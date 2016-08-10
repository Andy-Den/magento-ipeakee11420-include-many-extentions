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

class Balance_ConfigurableSimplePriceOverride_Model_Catalog_Product_Type_Configurable_Price extends Mage_Catalog_Model_Product_Type_Configurable_Price {

    

    public function getMinimalPrice($product) {
        return $this->getPrice($product);
    }

    public function getMaxPossibleFinalPrice($product) {

        $moduleStatus = Mage::helper('configurablesimplepriceoverride')->checkModuelStatus($product);
        if ($moduleStatus) {

            $childProduct = $this->getChildProductWithHighestPrice($product, "finalPrice");

            if (!$childProduct) {
                $childProduct = $this->getChildProductWithHighestPrice($product, "finalPrice", false);
            }

            if ($childProduct) {
                $fp = $childProduct->getFinalPrice();
            } else {
                return false;
            }
            return $fp;

        } else {
            return parent::getFinalPrice($qty = null,$product);
        }
    }

    
    public function getFinalPrice($qty = null, $product) {



        $moduleStatus = Mage::helper('configurablesimplepriceoverride')->checkModuelStatus($product);
        if ($moduleStatus) {
            $basePrice = $this->getBasePrice($product, $qty);
            if (($sProduct = $this->getConfigurableItemProduct($product)) !== false) {
                $finalPrice = $sProduct->getFinalPrice($qty);
                $finalPrice += $this->getTotalConfigurableItemsPrice($product, $finalPrice);
                $finalPrice += $this->_applyOptionsPrice($product, $qty, $basePrice) - $basePrice;
                $finalPrice = max(0, $finalPrice);
                $product->setFinalPrice($finalPrice);
                return $finalPrice;
            }

            $childProduct = $this->getChildProductWithLowestPrice($product, "finalPrice");
            if (!$childProduct) {
                $childProduct = $this->getChildProductWithLowestPrice($product, "finalPrice", false);
            }

            if ($childProduct) {
                $fp = $childProduct->getFinalPrice();
                $fp += $this->_applyOptionsPrice($product, $qty, $basePrice) - $basePrice;
            } else {
                return false;
            }

            $product->setFinalPrice($fp);
            return $fp;
        } else {
            return parent::getFinalPrice($qty = null, $product);
        }
    }

    
     public function getConfigurableItemProduct($product) {

        $product->getTypeInstance(true)
                ->setStoreFilter($product->getStore(), $product);
        $attributes = $product->getTypeInstance(true)
                ->getConfigurableAttributes($product);
        $selectedAttributes = array();
        if ($product->getCustomOption('attributes')) {

            $selectedAttributes = unserialize($product->getCustomOption('attributes')->getValue());
        }
        $attributesInfo = array();
        foreach ($attributes as $attribute) {
            $attributeId = $attribute->getProductAttribute()->getId();
            $value = $this->_getValueByIndex(
                    $attribute->getPrices() ? $attribute->getPrices() : array(), isset($selectedAttributes[$attributeId]) ? $selectedAttributes[$attributeId] : null
            );
            $product->setParentId(true);
            if ($value) {

                $attributesInfo[$attributeId] = $value['value_index'];
            }
        }
        if (!empty($attributesInfo)) {
            $simpleProduct = $product->getTypeInstance(true)->getProductByAttributes($attributesInfo, $product);
            $simpleProduct->load(false);
            if ($simpleProduct) {
                     return $simpleProduct;
            }
        }
        return false;
    }

    public function getPrice($product) {

        $moduleStatus = Mage::helper('configurablesimplepriceoverride')->checkModuelStatus($product);
        if ($moduleStatus) {
            
             if (($sProduct = $this->getConfigurableItemProduct($product)) !== false) {
                $product->setPrice($sProduct->getPrice());
                return $sProduct->getPrice();
            }
            
            
            $price = $product->getIndexedPrice();
            if ($price !== null) {
                return $price;
            }
            
           
            $childProduct = $this->getChildProductWithLowestPrice($product, "finalPrice");
            if (!$childProduct) {
                $childProduct = $this->getChildProductWithLowestPrice($product, "finalPrice", false);
            }
            if ($childProduct) {
                return $childProduct->getPrice();
            }
            return false;
        } else {
            return parent::getPrice($product);
        }
    }

    public function getChildProducts($product, $checkSalable = true) {
        $moduleStatus = Mage::helper('configurablesimplepriceoverride')->checkModuelStatus($product);
        if ($moduleStatus) {

            static $childrenCache = array();
            $cacheKey = $product->getId() . ':' . $checkSalable;

            if (isset($childrenCache[$cacheKey])) {
                return $childrenCache[$cacheKey];
            }

            $childProducts = $product->getTypeInstance(true)->getUsedProductCollection($product);
            $childProducts->addAttributeToSelect(array('price', 'special_price', 'status', 'special_from_date', 'special_to_date'));

            if ($checkSalable) {
                $salableChildProducts = array();
                foreach ($childProducts as $childProduct) {
                    if ($childProduct->isSalable()) {
                        $salableChildProducts[] = $childProduct;
                    }
                }
                $childProducts = $salableChildProducts;
            }

            $childrenCache[$cacheKey] = $childProducts;
            return $childProducts;
        } else {

            return parent::getChildProducts($product, $checkSalable = true);
        }
    }

   
    

    public function getChildProductWithHighestPrice($product, $priceType, $checkSalable = true) {
        $moduleStatus = Mage::helper('configurablesimplepriceoverride')->checkModuelStatus($product);
        if ($moduleStatus) {

            $childProducts = $this->getChildProducts($product, $checkSalable);
            if (count($childProducts) == 0) { #If config product has no children
                return false;
            }
            $maxPrice = 0;
            $maxProd = false;
            foreach ($childProducts as $childProduct) {
                if ($priceType == "finalPrice") {
                    $thisPrice = $childProduct->getFinalPrice();
                } else {
                    $thisPrice = $childProduct->getPrice();
                }
                if ($thisPrice > $maxPrice) {
                    $maxPrice = $thisPrice;
                    $maxProd = $childProduct;
                }
            }
            return $maxProd;
        } else {
            return parent::getChildProductWithHighestPrice($product, $priceType, $checkSalable = true);
        }
    }

    public function getChildProductWithLowestPrice($product, $priceType, $checkSalable = true) {
        $moduleStatus = Mage::helper('configurablesimplepriceoverride')->checkModuelStatus($product);
        if ($moduleStatus) {


            $childProducts = $this->getChildProducts($product, $checkSalable);
            if (count($childProducts) == 0) { #If config product has no children
                return false;
            }
            $minPrice = PHP_INT_MAX;
            $minProd = false;
            foreach ($childProducts as $childProduct) {
                if ($priceType == "finalPrice") {
                    $thisPrice = $childProduct->getFinalPrice();
                } else {
                    $thisPrice = $childProduct->getPrice();
                }
                if ($thisPrice < $minPrice) {
                    $minPrice = $thisPrice;
                    $minProd = $childProduct;
                }
            }
            return $minProd;
        } else {
            return parent::getChildProductWithLowestPrice($product, $priceType, $checkSalable = true);
        }
    }

  
    
    public function getTierPrice($qty = null, $product) {
        $moduleStatus = Mage::helper('configurablesimplepriceoverride')->checkModuelStatus($product);
        if ($moduleStatus) {
            
            if (($sproduct = $this->getConfigurableItemProduct($product)) !== false) {
                    return parent::getTierPrice($qty = null, $sproduct);
            }else{
                    return array();
            }
        } else {
            return parent::getTierPrice($qty = null, $product);
        }
    }

}
