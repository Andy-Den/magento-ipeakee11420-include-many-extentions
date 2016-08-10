<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Balance
 * @package    ConfigurableSimplePriceOverride
 * @copyright  Copyright (c) 2011 Balance
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

class Balance_ConfigurableSimplePriceOverride_Block_Checkout_Cart_Item_Renderer_Configurable extends Exceedz_Checkout_Block_Cart_Item_Renderer_Configurable {

  /**
    * Get The Thumbnail of the Associated Simple product when adding to the Shopping cart
    * 
    * @see Mage_Checkout_Block_Cart_Item_Renderer_Configurable::getProductThumbnail()
    */
    public function getProductThumbnail() {
        $moduleStatus = Mage::helper('configurablesimplepriceoverride')->checkModuelStatus($this->getProduct());
        if ($moduleStatus) {
            $product = $this->getChildProduct();
            if (!$product || !$product->getData('thumbnail')
                    || ($product->getData('thumbnail') == 'no_selection')) {
                $product = $this->getProduct();
            }
            return $this->helper('catalog/image')->init($product, 'thumbnail');
        } else {

            return parent::getProductThumbnail();
        }
    }
  
  /**
    * Get The Name of the Associated Simple product when adding to the Shopping cart
    * 
    * @see Mage_Checkout_Block_Cart_Item_Renderer_Configurable::getProductName()
    */
     public function getProductName()
    {
         $moduleStatus = Mage::helper('configurablesimplepriceoverride')->checkModuelStatus($this->getProduct());
        if ($moduleStatus) {
            $product = $this->getChildProduct();
            return $product->getName();
        }else{
        return $this->getProduct()->getName();
        }
    }
    

}
