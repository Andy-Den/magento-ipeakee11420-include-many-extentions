<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */

class Amasty_SeoRichData_Helper_Product extends Mage_Core_Helper_Abstract
{
    const ITEM_TYPE_OFFER_URL        = 'https://schema.org/Offer';

    protected $_priceApplied = false;

    public function applyPrice($html)
    {
        if ($this->_priceApplied)
            return $html;

        $product = Mage::registry('current_product');

        $priceSelector = Mage::getStoreConfig('amseorichdata/product/container_price_selector');

        $dom = new Amasty_SeoRichData_Model_Dom($html);

        if ($priceBox = $dom->query($priceSelector))
        {
            $store = $product->getStore();

            $finalPrice = $product->getFinalPrice();

            if (Mage::getStoreConfig('amseorichdata/product/price_incl_tax'))
                $finalPrice = Mage::helper('tax')->getPrice($product, $finalPrice, true);

            $price = $store->roundPrice($store->convertPrice($finalPrice));

            $priceBox->setAttribute('itemscope', '');
            $priceBox->setAttribute('itemtype', self::ITEM_TYPE_OFFER_URL);
            $priceBox->setAttribute('itemprop', 'offers');

            $dom->appendElement($priceBox, 'link', array(
                'itemprop' => 'availability',
                'href' => $product->isAvailable() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'
            ));

            $dom->appendElement($priceBox, 'meta', array(
                'itemprop' => 'priceCurrency',
                'content' => Mage::app()->getStore()->getCurrentCurrencyCode()
            ));

            $dom->appendElement($priceBox, 'meta', array(
                'itemprop' => 'price',
                'content' => $price
            ));

            if ($product->getSpecialToDate() && $product->getPrice() != $product->getFinalPrice())
            {
                $dom->appendElement($priceBox, 'time', array(
                    'itemprop' => 'priceValidUntil',
                    'datetime' => date('Y-m-d', strtotime($product->getSpecialToDate()))
                ));
            }

            $html = $dom->save();
            $html = html_entity_decode($html, ENT_QUOTES, "UTF-8");

            $this->_priceApplied = true;
        }

        return $html;
    }

    public function getGroupedPrice($product)
    {
        $resource = Mage::getResourceSingleton('core/resource');
        $connection = $resource->getReadConnection();

        $select = $connection
            ->select()
            ->from($resource->getTable('catalog/product_index_price'), 'min_price')
            ->where('entity_id = ?', $product->getId())
            ->where('website_id = ?', Mage::app()->getWebsite()->getId())
            ->where('customer_group_id = ?', +Mage::getSingleton('customer/session')->getCustomerGroupId())
        ;

        $groupPrice = $connection->fetchOne($select);

        $store = $product->getStore();
        $groupPrice = $store->roundPrice($store->convertPrice($groupPrice));

        return $groupPrice;
    }

    public function applyImage($html)
    {
        $pattern = '|(\<img)([^>]+src=[^>]+/image/[^>]+\>)|i';
        $replace = '${1} itemprop="image" ${2}';

        return preg_replace($pattern, $replace, $html, 1);
    }
}
