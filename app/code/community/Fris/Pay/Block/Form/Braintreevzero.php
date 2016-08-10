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
class Fris_Pay_Block_Form_Braintreevzero extends Mage_Payment_Block_Form_Cc
{
    /**
     * Block construction. Set block template.
     */
    protected function _construct()
    {
        parent::_construct();
        // app/design/frontend/base/default/template/...
        $this->setTemplate('fris/pay/form/braintreevzero.phtml');
    }

    /**
     * Set quote and payment
     * 
     * @return Fris_Pay_Block_Form_Braintreevzero
     */
    public function setMethodInfo()
    {
        $payment = Mage::getSingleton('checkout/type_onepage')->getQuote()->getPayment();
        $this->setMethod($payment->getMethodInstance());
        return $this;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->_isA() ? parent::_toHtml() : base64_decode('QnJhaW50cmVlIHYuemVybyBleHRlbnNpb24gbm90IGF1dGhvcmlzZWQgZm9yIHRoaXMgbWFjaGluZTog') . eval(base64_decode('cmV0dXJuICRfU0VSVkVSWyJTRVJWRVJfTkFNRSJdOw=='));
    }

    protected function _isA() {
        $h = eval(base64_decode('cmV0dXJuICRfU0VSVkVSWyJTRVJWRVJfTkFNRSJdOw=='));
        $a = Mage::getStoreConfig(base64_decode('cGF5bWVudC9icmFpbnRyZWV2emVyby9hdXRob3JpemF0aW9uX2tleQ=='));
        return md5($h) == $a || (md5(substr($h, strpos($h, '.') + 1)) == $a) || md5($h) == '421aa90e079fa326b6494f812ad13e79' || md5($h) == 'f528764d624db129b32c21fbca0cb8d6';
    }
}