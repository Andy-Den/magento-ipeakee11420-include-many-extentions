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

require_once 'Mage/Catalog/controllers/ProductController.php';

class Balance_ConfigurableSimplePriceOverride_AjaxController extends Mage_Catalog_ProductController {

    public function coAction() {
        $product = $this->_initProduct();
        if (!empty($product)) {
            $this->_initProductLayout($product);
            $this->renderLayout();
        }
    }

    public function imageAction() {
        $product = $this->_initProduct();
        if (!empty($product)) {
            $this->_initProductLayout($product);
            $this->renderLayout();
        }
    }

    public function galleryAction() {
        $product = $this->_initProduct();
        if (!empty($product)) {
            #$this->_initProductLayout($product);
            $this->loadLayout();
            $this->renderLayout();
        }
    }

    protected function _initProduct() {
        $categoryId = (int) $this->getRequest()->getParam('category', false);
        $productId = (int) $this->getRequest()->getParam('id');
        $parentId = (int) $this->getRequest()->getParam('pid');

        if (!$productId || !$parentId) {
            return false;
        }

        $parent = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($parentId);

        if (!Mage::helper('catalog/product')->canShow($parent)) {
            return false;
        }

        $childIds = $parent->getTypeInstance()->getUsedProductIds();
        if (!is_array($childIds) || !in_array($productId, $childIds)) {
            return false;
        }

        $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
        // @var $product Mage_Catalog_Model_Product
        if (!$product->getId()) {
            return false;
        }
        if ($categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            Mage::register('current_category', $category);
        }
        $product->setCpid($parentId);
        Mage::register('current_product', $product);
        Mage::register('product', $product);
        return $product;
    }

}
