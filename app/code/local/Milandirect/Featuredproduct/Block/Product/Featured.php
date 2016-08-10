<?php

/**
 * Milandirect_Featured showing on cart empty
 *
 * @category  Milandirect
 * @package   Milandirect_Featured
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */

class Milandirect_Featuredproduct_Block_Product_Featured extends Mage_Catalog_Block_Product_Abstract
{
    public function getFeaturedProducts()
    {
        $store_id = Mage::app()->getStore()->getId();
        $_products = Mage::getModel('catalog/product')->getCollection()
            ->addStoreFilter($store_id)
            ->addAttributeToFilter('is_featured', array(1))
            ->addAttributeToFilter('status', array(1));
        return $_products;
    }
}
