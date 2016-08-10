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

class Balance_ConfigurableSimplePriceOverride_Block_Catalog_Product_View extends
Mage_Catalog_Block_Product_View {

    /**
     * Default MAP renderer type
     *
     * @var string
     */
    protected $_mapRenderer = 'msrp_item';

    /**
     * Add meta information from product to head block
     *
     * @return Mage_Catalog_Block_Product_View
     */
    protected function _prepareLayout() {
        $this->getLayout()->createBlock('catalog/breadcrumbs');
        $moduleStatus = Mage::helper('configurablesimplepriceoverride')->checkModuelStatus($this->getProduct());
        if ($moduleStatus) {
            $headBlock = $this->getLayout()->getBlock('head');
            if (is_object($headBlock)) {
                $headBlock->addJs('balance/configurable/js/scp_product_extension.js');
            }
        } else {
            $headBlock = $this->getLayout()->getBlock('head');
        }

        if ($headBlock) {
            $product = $this->getProduct();
            $title = $product->getMetaTitle();
            if ($title) {
                $headBlock->setTitle($title);
            }
            $keyword = $product->getMetaKeyword();
            $currentCategory = Mage::registry('current_category');
            if ($keyword) {
                $headBlock->setKeywords($keyword);
            } elseif ($currentCategory) {
                $headBlock->setKeywords($product->getName());
            }
            $description = $product->getMetaDescription();
            if ($description) {
                $headBlock->setDescription(($description));
            } else {
                $headBlock->setDescription(Mage::helper('core/string')->substr($product->getDescription(), 0, 255));
            }
            if ($this->helper('catalog/product')->canUseCanonicalTag()) {
                $params = array('_ignore_category' => true);
                $headBlock->addLinkRel('canonical', $product->getUrlModel()->getUrl($product, $params));
            }
        }

        return parent::_prepareLayout();
    }

}
