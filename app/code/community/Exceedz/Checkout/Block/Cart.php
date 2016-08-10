<?php
/**
 * Shopping cart block
 *
 * @category    Exceedz
 * @package     Exceedz_Checkout
 */
class Exceedz_Checkout_Block_Cart extends Mage_Checkout_Block_Cart
{
	/**
     * prepare breadcrumb
     */
    protected function _prepareLayout(){
         if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')){
            $breadcrumbs->addCrumb('home', array('label'=>__('Home'), 'title'=>__('Go to Home Page'), 'link'=>Mage::getBaseUrl()));
            $breadcrumbs->addCrumb('cart', array('label'=>'Your Cart', 'title'=>'Your Cart'));
         }
    }
}
