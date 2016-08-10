<?php
/**
 * fris - smart commerce extensions for Magento
 *
 * @category  Fris
 * @package   Fris_Pay
 * @copyright Copyright (c) 2015 fris IT (http://fris.technology)
 * @license   http://fris.technology/license
 * @author    fris IT <support@fris.technology>
 */

// This relies on DOCUMENT_ROOT/lib being among the include paths as well as the
// braintree-php-3.x.y folder being renamed to braintree-php.
require_once 'braintree-php/lib/Braintree.php';


class Fris_Pay_Model_Method_Braintreevzero extends Mage_Payment_Model_Method_Abstract
{
    // Overriding: Mage_Payment_Model_Method_Abstract
    protected $_code = 'braintreevzero';
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;

    // Braintree-specific parameters taken from config form.
    protected $_merchantAccountId = '';
    protected $_useVault = true;

    /**
     * Block path.
     *
     * @var string
     */
    protected $_formBlockType = 'fris_pay/form_braintreevzero';


    public function __construct()
    {
        parent::__construct();
        if ($this->getConfigData('active') == 1) {
            $this->_initEnvironment();
        }
    }

    public function _construct() {
        return;
    }

    /**
     * Retrieve information from payment configuration.
     *
     * @param string $field
     * @param int|string|null|Mage_Core_Model_Store $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        $path = 'payment/' . $this->getCode() . '/' . $field;
        if (empty($storeId)) {
            if (Mage::app()->getStore()->getCode() == Mage_Core_Model_Store::ADMIN_CODE) {
                $storeId = Mage::getSingleton('adminhtml/session_quote')->getStoreId();
            }
            else {
                $storeId = $this->getStore();
            }
        }
        return Mage::getStoreConfig($path, $storeId);
    }

    /**
     * Initializes environment.
     *
     * @param int $storeId
     */
    protected function _initEnvironment($storeId = null)
    {
        $env = $this->getConfigData('environment', $storeId);
        if ($env != 'production') {
          $env = 'sandbox';
        }
        Braintree_Configuration::environment($env);
        Braintree_Configuration::merchantId($this->getConfigData('merchant_id', $storeId));
        Braintree_Configuration::publicKey( $this->getConfigData('public_key',  $storeId));
        Braintree_Configuration::privateKey($this->getConfigData('private_key', $storeId));

        $this->_merchantAccountId = $this->getConfigData('merchant_account_id', $storeId);
        $this->_useVault = $this->getConfigData('use_vault', $storeId);
    }

    /**
     * Validate data
     *
     * @return Fris_Pay_Model_Method_Braintreevzero
     */
    public function validate()
    {
        $request = Mage::app()->getRequest();
        if ($request->getPost('payment_method_nonce')) {
            // nonce looks like: "77cf198e-2998-413c-a88f-727d635c3373"
            return $this;
        }
        // When Vault is used, we don't necessarily have a nonce. This is ok,
        // as long as we have an existing customerId.
        if ($this->useVault()) {
            $session = Mage::getSingleton('customer/session');
            if ($id = $this->isExistingCustomerWithCard($session)) {
                return $this;
            }
        }

        $path = $request->getOriginalPathInfo();
        $module = $request->getControllerModule();
        // Some checkouts like Mage core when delaying pament verification till
        // "Place order" is pressed, as well as IWD and Idev validate at
        // inappropriate times. Give these the benefit of the doubt for now.
        // This function will be called again on a different $path and even if
        // it isn't the ultitmate auhtorisation is not here, but in the
        // Braintree_Transaction::sale() call.
        if (($path == '/checkout/onepage/savePayment/') || /* VerficationTime == 'order_submit' */
            (strpos($path, '/checkout/onepage/saveOrder/form_key/') === 0) ||
            (strpos($path, '/firecheckout/index/saveOrder/form_key/') === 0) ||
            ($path == '/onestepcheckout/ajax/placeOrder/' /* && module == 'AW_Onestepcheckout'*/) ||
            ($path == '/onepage/json/savePayment' /* && $module == 'IWD_Opc'*/) ||
            ($path == '/onestepcheckout/ajax/set_methods_separate/' /* && $module == 'Idev_OneStepCheckout'*/) ||
            ($path == '/checkout/onepage/' && $module == 'GoMage_Checkout')) {
            return $this;
        }
        Mage::throwException(Mage::helper('fris_pay')->__('No payment method nonce received. Are all payment details correct?'));
    }

    /**
     * Authorizes the supplied amount with Braintree.
     *
     * @param Varien_Object $payment
     * @param decimal $amount
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        $request = Mage::app()->getRequest();
        $nonce = $request->getPost('payment_method_nonce');

        if (!$nonce && !$this->useVault()) {
            Mage::throwException(Mage::helper('fris_pay')->__('No payment method nonce received. Cannot authorize payment.  Please edit your Payment Information and try again.'));
        }
        $order = $payment->getOrder();

        $args = array(
            'amount' => $amount,
            'paymentMethodNonce' => $nonce,
            'orderId' => $order->getIncrementId(),
            'channel' => $this->getChannel(),
            'options' => array(
                'submitForSettlement' => true,
                'storeInVaultOnSuccess' => $this->useVault(),
                'storeShippingAddressInVault' => $this->useVault(),
                'addBillingAddressToPaymentMethod' => $this->useVault(),
            ),
        );
        $billing = $order->getBillingAddress();
        $shipping = $order->getShippingAddress();

        // See https://developers.braintreepayments.com/javascript+php/sdk/server/transaction-processing/create-from-vault
        if ($this->useVault() && $order->getCustomerId()) {
            $customerId = $this->_generateBraintreeCustomerId($order->getCustomerId(), $order->getCustomerEmail());
            if ($this->getBraintreeCustomer($customerId)) {
                // Customer already exists, so just pass their ID.
                $args['customerId'] = $customerId;
            }
            else {
                // Create a new customer on Braintree with the given ID.
                $args['customer'] = array(
                    'id' => $customerId,
                    'company'   => $billing->getCompany(),
                    'firstName' => $billing->getFirstname(),
                    'lastName'  => $billing->getLastname(),
                    'phone'     => $billing->getTelephone(),
                    'fax'       => $billing->getFax(),
                    'email'     => $order->getCustomerEmail(),
                  //'website'   => 'N.A.',
               );
            }
        }
        // If 'customerId' is supplied will 'billing' and 'shipping' be
        // updated automatically, if changed during Magento checkout?
        if ($billing) {
          $args['billing'] = $this->toBraintreeAddress($billing);
        }
        if ($shipping) {
          $args['shipping'] = $this->toBraintreeAddress($shipping);
        }
        if (!empty($this->_merchantAccountId)) {
            // This causes an exception in Braintree v.zero when the merchant
            // has not set up a merchantAccountId identical to the one below, as
            // specified on the Magento Payment Method configuration page.
            // Merchant accounts may be used to deal with different currencies.
            $args['merchantAccountId'] = $this->_merchantAccountId;
        }
        $deviceData = $request->getPost('device_data');
        if (/*$this->getConfigData('fraudprotection') &&*/ $deviceData) {
            $args['deviceData'] = $deviceData;
        }

        $this->_debug($args);

        $result = Braintree_Transaction::sale($args);

        if (empty($result->success)) {
            $this->_debug($result->errors->deepAll());
            
            $error = 'ERROR ';
            if (empty($result->transaction)) {
                $error .= empty($result->message) ? '?' : $result->message;
            } else {
                $error .= '(' . $result->transaction->processorResponseCode . '): '
                    . $result->transaction->processorResponseText;
            }
            $msg = Mage::helper('fris_pay')->__('Please try again later or contact merchant.');
            Mage::throwException($error . "\n\n" . $msg);
        }
        else {
            $this->setStore($order->getStoreId());
            $this->_processSale($payment, $result);
        }
        return $this;
    }

    /**
     * Captures the supplied amount.
     *
     * @param Varien_Object $payment
     * @param decimal $amount
     * @return Fris_Pay_Model_Method_Braintreevzero
     */
    public function capture(Varien_Object $payment, $amount)
    {
        return $this->authorize($payment, $amount);
    }

    /**
     * Processes successful sale.
     *
     * @param Varien_Object $payment
     * @param Braintree_Result_Successful $result
     * @param decimal $amount, taken from $result when omitted
     * 
     * @return Varien_Object
     */
    protected function _processSale($payment, $result, $amount = NULL)
    {
        $trans = $result->transaction;
        $payment->setStatus(self::STATUS_APPROVED)
            ->setLastTransId($trans->id)
            ->setTransactionId($trans->id)
            ->setIsTransactionClosed(0)
            ->setCcTransId($trans->id)
            ->setCcLast4($trans->creditCardDetails->last4)
            ->setAmount(isset($amount) ? $amount : $trans->amount)
            ->setShouldCloseParentTransaction(false)
            ->setAdditionalInformation($this->_getExtraTransactionInformation($trans));

        if (isset($trans->creditCard['token'])) {
            $payment->setTransactionAdditionalInfo('token', $trans->creditCard['token']);
        }
        return $payment;
    }

    /**
     * Returns extra transaction information.
     *
     * @param $trans
     * @return array of key,value pairs
     */
    protected function _getExtraTransactionInformation($trans) {
        $data = array();
        $fields = array(
            'avsErrorResponseCode',
            'avsPostalCodeResponseCode',
            'avsStreetAddressResponseCode',
            'cvvResponseCode',
            'gatewayRejectionReason',
            'processorAuthorizationCode',
            'processorResponseCode',
            'processorResponseText',
        );
        foreach ($fields as $field) {
            if (!empty($trans->{$field})) {
                $data[$field] = $trans->{$field};
            }
        }
        return $data;
    }

    /**
     * Refunds specified amount on Braintree.
     *
     * @param Varien_Object $payment
     * @param decimal $amount
     * @return Fris_Pay_Model_Method_Braintreevzero
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $transId = Mage::helper('fris_pay')->getTransactionIdBase($payment->getRefundTransactionId());
        try {
            $trans = Braintree_Transaction::find($transId);
            $this->_debug($payment->getCcTransId());
            $this->_debug($trans);
            if ($trans->status === Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT) {
                if ($amount < $trans->amount) {
                    Mage::throwException(
                        Mage::helper('fris_pay')->__(
                            'This refund is for a partial amount but the transaction has not settled, yet. ' .
                            'Please wait 24 hours before trying to issue a partial refund.'
                        ));
                }
                else {
                    Mage::throwException(
                        Mage::helper('fris_pay')->__(
                            'The transaction has not settled, yet. ' .
                            'Please wait 24 hours before trying to issue a refund or use the Void option.'
                        ));
                }
            }

            $result = $trans->status === Braintree_Transaction::SETTLED
                ? Braintree_Transaction::refund($transId, $amount)
                : Braintree_Transaction::void($transId);
            $this->_debug($result);
            if ($result->success) {
                $payment->setIsTransactionClosed(1);
            }
            else {
                Mage::throwException(Mage::helper('fris_pay/error')->parseBraintreeError($result));
            }
        }
        catch (Exception $e) {
            Mage::throwException(Mage::helper('fris_pay')
                ->__('There was an error refunding the transaction') . ': ' . $e->getMessage());
        }
        return $this;
    }

    /**
     * Voids transaction on Braintree.
     *
     * @param Varien_Object $payment
     */
    public function void(Varien_Object $payment)
    {
        $transIds = array();
        
        $invoice = Mage::registry('current_invoice');
        if ($invoice && $invoice->getId() && $invoice->getTransactionId()) {
            $transIds[] = Mage::helper('fris_pay')->getTransactionIdBase($invoice->getTransactionId());
        }
        else {
            $collection = Mage::getModel('sales/order_payment_transaction')
                ->getCollection()
                ->addFieldToSelect('txn_id')
                ->addOrderIdFilter($payment->getOrder()->getId())
                ->addTxnTypeFilter(array(
                    Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH,
                    Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE
                ));
            foreach ($collection->getColumnValues('txn_id') as $transId) {
                $baseId = Mage::helper('fris_pay')->getTransactionIdBase($transId);
                if (!in_array($baseId, $transIds)) {
                    $transIds[] = $baseId;
                }
            }
        }
        foreach ($transIds as $transId) {
            $trans = Braintree_Transaction::find($transId);
            if ($trans->status !== Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT &&
                $trans->status !== Braintree_Transaction::AUTHORIZED) {
                Mage::throwException(Mage::helper('fris_pay')
                    ->__('Transaction already settled or voided. Cannot void.'));
            }
            if ($trans->status === Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT) {
                $message = Mage::helper('fris_pay')->__('Voided capture.') ;
            }
        }
        $errors = '';
        foreach ($transIds as $transId) {
            $this->_debug('void-' . $transId);
            $result = Braintree_Transaction::void($transId);
            $this->_debug($result);
            if (!$result->success) {
                $errors .= ' ' . Mage::helper('fris_pay/error')->parseBraintreeError($result);
            }
            elseif (!empty($message)) {
                $payment->setMessage($message);
            }
        }
        if ($errors) {
            Mage::throwException(Mage::helper('fris_pay')->__('There was an error voiding the transaction') . ': ' . $errors);
        }
        else {
            $match = true;
            foreach ($transIds as $transId) {
                $collection = Mage::getModel('sales/order_payment_transaction')
                    ->getCollection()
                    ->addFieldToFilter('parent_txn_id', array('eq' => $transId))
                    ->addFieldToFilter('txn_type', Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID);
                if ($collection->getSize() < 1) {
                    $match = false;
                }
            }
            if ($match) {
                $payment->setIsTransactionClosed(1);
            }
        }
        return $this;
    }

    /**
     * Generates an md5 hash to be used as the customer ID on Braintree.
     *
     * For compatibility reasons, this is the same as the old Braintree module.
     * However this means that when a user changes their email, they will get a
     * new Braintree customer ID and will have to enter their full credit card
     * details again.
     *
     * @param string $customerId
     * @param string $email, optional
     * @return string, the hash as a 32-character hexadecimal numeric string
     */
    protected function _generateBraintreeCustomerId($customerId, $email = null)
    {
        // Does md5 guarantee uniqueness? Can two customerId-email pairs end
        // up with the same hash?
        $arg = $email ? ($customerId . '-' . $email) :  $customerId;
        return md5($arg);
    }

    /**
     * Return customer object via its unique ID.
     *
     * @param string $customerId
     * @return boolean
     */
    public function getBraintreeCustomer($customerId)
    {
        try {
            return Braintree_Customer::find($customerId);
        } 
        catch (Braintree_Exception_NotFound $e) { }
        return false;
    }

    /**
     * Returns whether the supplied session belongs to a Braintree customer.
     *
     * @param object $session
     * @return mixed, Braintree customer id or false
     */
    public function isExistingCustomerWithCard($session) {
        if (!$session || !$session->isLoggedIn()) {
            return false;
        }
        $customerId = $session->getCustomerId();
        $customerEmail = $session->getCustomer()->getEmail();
        $braintreeCustomerId = $this->_generateBraintreeCustomerId($customerId, $customerEmail);
        $braintreeCustomer = $this->getBraintreeCustomer($braintreeCustomerId);
        if ($braintreeCustomer && !empty($braintreeCustomer->creditCards)) {
            return $braintreeCustomerId;
        }
        return false;
    }

    /**
     * Sets trans. ID on invoice. Without it online refunds won't be possible.
     *
     * Called from Mage_Sales_Model_Order_Payment::capture().
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @param Mage_Sales_Model_Order_Payment $payment
     *
     * @return Fris_Pay_Model_Method_Braintreevzero
     */
    public function processInvoice($invoice, $payment)
    {
        $invoice->setTransactionId($payment->getLastTransId());
        return $this;
    }

    /**
     * Convert magento address to a Braintree-style array.
     *
     * @param Mage_Customer_Model_Address $address
     *
     * @return array
     */
    public function toBraintreeAddress($address)
    {
        $region = $address->getRegion();
        // PayPal: US & CA need to pass 2-letter region code, not region name.
        $regionId = $address->getData('region_id');
        $countryId = $address->getCountryId();
        if ($regionId && in_array($countryId, array('US', 'CA'))) {
            $regionModel = $address->getRegionModel($regionId);
            if ($regionModel && $regionModel->getCountryId() == $countryId) {
                $region = $regionModel->getCode();
            }
        }
        return array(
            'company'           => $address->getCompany(),
            'firstName'         => $address->getFirstname(),
            'lastName'          => $address->getLastname(),
            'streetAddress'     => $address->getStreet(1),
            'extendedAddress'   => $address->getStreet(2),
            'locality'          => $address->getCity(),
            'region'            => $region,
            'postalCode'        => $address->getPostcode(),
            'countryCodeAlpha2' => $address->getCountry(),
        );
    }

    /**
     * For use in Braintree_Transaction::sale() call.
     *
     * @return string
     */
    protected function getChannel()
    {
        return 'Magento ' . Mage::getEdition() . ' ' . Mage::getVersion() . ', Fris_Pay extension';
    }

    /**
     * If vault can be used
     *
     * @return boolean
     */
    public function useVault()
    {
        return (bool)$this->_useVault;
    }
}
