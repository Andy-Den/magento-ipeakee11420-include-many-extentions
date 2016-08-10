<?php
/**
 * @category    Exceedz
 * @package     Exceedz_Checkout
 */
require_once 'Mage/Checkout/controllers/OnepageController.php';
class Exceedz_Checkout_OnepageController extends Mage_Checkout_OnepageController
{
    /**
     * Get the cart details
     *
     * @return cart html
     */
    public function cartAction()
    {
        Mage::getSingleton('checkout/session')->getQuote()->collectTotals();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * save checkout billing address
     */
    public function saveBillingAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
//            $postData = $this->getRequest()->getPost('billing', array());
//            $data = $this->_filterPostData($postData);
            $data = $this->getRequest()->getPost('billing', array());
            $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

            if (isset($data['email'])) {
                $data['email'] = trim($data['email']);
            }
            $result = $this->getOnepage()->saveBilling($data, $customerAddressId);

            if (!isset($result['error'])) {
                /* check quote for virtual */
                if ($this->getOnepage()->getQuote()->isVirtual()) {
                    $result['goto_section'] = 'payment';
                    $result['update_section'] = array(
                        'name' => 'payment-method',
                        'html' => $this->_getPaymentMethodsHtml()
                    );
                } elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {

                    $shippingMethodData = $this->getRequest()->getPost('shipping_method', '');
                    $resultShippingMethod = $this->getOnepage()->saveShippingMethod($shippingMethodData);

                    $result['goto_section'] = 'payment';
                    $result['update_section'] = array(
                        'name' => 'payment-method',
                        'html' => $this->_getPaymentMethodsHtml()
                    );

                    if(isset($resultShippingMethod['error']) || !$this->checkShippingMethodCost()) {
                       $result['error'] = true;
                       $result['message'] = $this->__('Invalid postcode.');
                    }
                    else {
                        $this->getOnepage()->getQuote()->collectTotals()->save();
                    }

                    $result['allow_sections'] = array('shipping');
                    $result['duplicateBillingInfo'] = 'true';


                } else {
                    $result['goto_section'] = 'shipping';
                }
            }

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * check checkout shipping cost
     * @return true for no error
     */
    public function checkShippingMethodCost()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $shippingAmount = $quote->getShippingAddress()->collectTotals()->getShippingAmount();
        if ($shippingAmount > 0 || Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getShippingDescription() == 'Free Shipping') {
            return true;
        } else {
            return false;
        }
    }
}
