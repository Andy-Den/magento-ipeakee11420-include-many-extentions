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

class Balance_ConfigurableSimplePriceOverride_Model_Catalog_Product extends Mage_Catalog_Model_Product {

    public function getMaxPossibleFinalPrice() {
        $moduleStatus = Mage::helper('configurablesimplepriceoverride')->checkModuelStatus($this);
        if ($moduleStatus) {
            if (is_callable(array($this->getPriceModel(), 'getMaxPossibleFinalPrice'))) {
                return $this->getPriceModel()->getMaxPossibleFinalPrice($this);
            } else {
                #return $this->_getData('minimal_price');
                return parent::getMaxPrice();
            }
        } else {
            return parent::getMaxPossibleFinalPrice();
        }
    }

    public function isVisibleInSiteVisibility() {
        $moduleStatus = Mage::helper('configurablesimplepriceoverride')->checkModuelStatus($this);
        if ($moduleStatus) {
            if (is_callable(array($this->getTypeInstance(), 'hasConfigurableProductParentId')) && $this->getTypeInstance()->hasConfigurableProductParentId()) {
                return true;
            } else {
                return parent::isVisibleInSiteVisibility();
            }
        } else {
            return parent::isVisibleInSiteVisibility();
        }
    }

}
