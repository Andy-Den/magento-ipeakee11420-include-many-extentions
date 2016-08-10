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

class Balance_ConfigurableSimplePriceOverride_Helper_Data extends Mage_Core_Helper_Abstract {

  /**
    * Check Module Status Globally and Product Wise
    */
    public function checkModuelStatus($product) {

        if (Mage::getStoreConfig('SCP_options/general_page/enable_option') == 1) {
            if($product->isConfigurable()){
            $store_id = Mage::app()->getStore()->getId();
            $product_status = Mage::getResourceModel('catalog/product')->getAttributeRawValue($product->getId(), 'scpproductspecific', $store_id);
            if ($product_status != 1) {
                return true;
            } else {
                return false;
            }
            }
        } else {

            return false;
        }
    }

  /**
    * Check Module Status Globally.
    */
    public function checkModuelGlobalStatus() {

        if (Mage::getStoreConfig('SCP_options/general_page/enable_option') == 1) {
            return true;
        } else {
            return false;
        }
    }

}
