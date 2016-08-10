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

class Balance_ConfigurableSimplePriceOverride_Block_Checkout_Cart_Item_Renderer extends Exceedz_Checkout_Block_Cart_Item_Renderer {

    protected function getConfigurableProductParentId() {
        if ($this->getItem()->getOptionByCode('cpid')) {
            return $this->getItem()->getOptionByCode('cpid')->getValue();
        }
        try {
            $buyRequest = unserialize($this->getItem()->getOptionByCode('info_buyRequest')->getValue());
            if (!empty($buyRequest['cpid'])) {
                return $buyRequest['cpid'];
            }
        } catch (Exception $e) {
            
        }
        return null;
    }

    protected function getConfigurableProductParent() {
        return Mage::getModel('catalog/product')
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->load($this->getConfigurableProductParentId());
    }

    public function getProduct() {
        return Mage::getModel('catalog/product')
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->load($this->getItem()->getProductId());
    }

    public function getProductName() {
        
            return parent::getProductName();
       
    }

    public function hasProductUrl() {
        if ($this->getConfigurableProductParentId()) {
            return true;
        } else {
            return parent::hasProductUrl();
        }
    }

    public function getProductUrl() {
        if ($this->getConfigurableProductParentId()) {
            return $this->getConfigurableProductParent()->getProductUrl();
        } else {
            return parent::getProductUrl();
        }
    }

    public function getOptionList() {
        $options = true;
       
            $options = parent::getOptionList();
     
            if ($this->getConfigurableProductParentId()) {
                $attributes = $this->getConfigurableProductParent()
                        ->getTypeInstance()
                        ->getUsedProductAttributes();
                foreach ($attributes as $attribute) {
                    $options[] = array(
                        'label' => $attribute->getFrontendLabel(),
                        'value' => $this->getProduct()->getAttributeText($attribute->getAttributeCode()),
                        'option_id' => $attribute->getId(),
                    );
                }
            }
        
        return $options;
    }

    public function getProductThumbnail() {

        if (!$this->getConfigurableProductParentId()) {
            return parent::getProductThumbnail();
        }


            $product = $this->getProduct();

            if ($product->getData('thumbnail') && ($product->getData('thumbnail') != 'no_selection')) {
                return $this->helper('catalog/image')->init($product, 'thumbnail');
            }


        $product = $this->getConfigurableProductParent();
        return $this->helper('catalog/image')->init($product, 'thumbnail');
    }

}
