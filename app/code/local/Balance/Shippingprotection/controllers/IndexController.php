<?php

/**
 * Balance_Shippingprotection_IndexController
 *
 * @author Balance Internet
 */
class Balance_Shippingprotection_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function updatefreightprotectionAction()
    {
        $freight_protection = Mage::app()->getRequest()->getParam('freight_protection');
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $quote->setBiIsProtection($freight_protection)->save();
        $quote->collectTotals()->save();

        $array_values = array();
        $array_values['totals'] = $this->getLayout()->createBlock('checkout/cart_totals')->setTemplate(
            'checkout/cart/totals.phtml'
        )->toHtml();

        $this->getResponse()->setBody(json_encode($array_values));
        $this->getResponse()->setHeader('Content-Type', 'application/json', true);

    }

}
