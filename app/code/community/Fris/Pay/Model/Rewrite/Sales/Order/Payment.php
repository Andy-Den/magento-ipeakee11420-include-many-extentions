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
class Fris_Pay_Model_Rewrite_Sales_Order_Payment extends Mage_Sales_Model_Order_Payment
{
    const REGISTRY_KEY_CANCEL = 'braintreevzero_cancel';

    /**
     * Look up an authorization transaction using invoice trans. id, if set.
     *
     * This function is overriden because Magento will consider a transaction
     * for voiding only if it is of type TYPE_AUTH
     * Braintree allows voiding captures too.
     * An order invoice must exist or this function will return false.
     *
     * Called from /app/code/core/Mage/Sales/Model/Order/Payment::canCapture()
     *
     * @return Mage_Sales_Model_Order_Payment_Transaction|false
     */
    public function getAuthorizationTransaction()
    {
        if ($this->getMethodInstance()->getCode() != 'braintreevzero') {
            return parent::getAuthorizationTransaction();
        }

        $invoice = Mage::registry('current_invoice');
        if ($invoice && $invoice->getId()) {
            $transId = Mage::helper('fris_pay')->getTransactionIdBase($invoice->getTransactionId());
            $collection = Mage::getModel('sales/order_payment_transaction')->getCollection()
                ->addFieldToFilter('txn_id', array('eq' => $transId));
            if ($collection->getSize() < 1) {
                $collection = null;
            }
        }
        else if (($order = Mage::registry('current_order')) && $order->getId() && $order->hasInvoices()) {
            if (!Mage::registry(self::REGISTRY_KEY_CANCEL)) {
                return false;
            }
            $collection = Mage::getModel('sales/order_payment_transaction')->getCollection()
                ->addFieldToFilter('payment_id', array('eq' => $this->getId()))
                ->addFieldToFilter('txn_type', array('eq' => Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE));
            if ($collection->getSize() >= 1) {
                return false;
            }
        }
        if (empty($collection)) {
            $collection = Mage::getModel('sales/order_payment_transaction')->getCollection()
                ->setOrderFilter($this->getOrder())
                ->addPaymentIdFilter($this->getId())
                ->setOrder('created_at', Varien_Data_Collection::SORT_ORDER_DESC)
                ->setOrder('transaction_id', Varien_Data_Collection::SORT_ORDER_DESC);
        }
        foreach ($collection as $txn) {
            $txn->setOrderPaymentObject($this);
            $this->_transactionsLookup[$txn->getTxnId()] = $txn;
            $txn->setParentId($txn->getId());
            return $txn;
        }
        return false;
    }

    /**
     * Order cancellation hook for payment method instance.
     * Adds void transaction if needed.
     *
     * @return Mage_Sales_Model_Order_Payment
     */
    public function cancel()
    {
        if ($this->getMethodInstance()->getCode() == 'braintreevzero') {
            Mage::register(self::REGISTRY_KEY_CANCEL, true);
        }
        return parent::cancel();
    }
}
