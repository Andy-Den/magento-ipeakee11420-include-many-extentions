<?php
/**
 * @category   AW
 * @package    AW_Mobile
 */
require_once 'Exceedz/Checkout/controllers/OnepageController.php';
class AW_Mobile_OnepageController extends Exceedz_Checkout_OnepageController
{
    /**
     * Get the cart details
     *
     * @return cart html
     */
    public function cartAction()
    {
    	
    	Mage::getSingleton('checkout/session')->getQuote()->collectTotals();
        try {
            $layout = Mage::app()->getLayout();
            $update = $layout->getUpdate()->addHandle('checkout_onepage_cart')->addHandle('default')->load();
            $layout->generateXml()->generateBlocks($layout->getNode('cart'));
            $body = $layout->getBlock('cart')->toHtml();
        } catch (Exception $e) {
            $body = $layout->getBlock('cart')->toHtml();
        }
        $this->getResponse()->setBody($body);
    }

}