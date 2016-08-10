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

class Balance_ConfigurableSimplePriceOverride_Model_Catalog_Product_Type_Simple extends Mage_Catalog_Model_Product_Type_Simple {

    public function prepareForCart(Varien_Object $buyRequest, $product = null) {
        $product = $this->getProduct($product);
        parent::prepareForCart($buyRequest, $product);
        if ($buyRequest->getcpid()) {
            $product->addCustomOption('cpid', $buyRequest->getcpid());
        }
        return array($product);
    }

    public function hasConfigurableProductParentId() {
        if ($this->getProduct()->getCustomOption('cpid')) {
            return true;
        }
        return false;
    }

}
