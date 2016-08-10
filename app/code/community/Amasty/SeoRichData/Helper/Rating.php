<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */

class Amasty_SeoRichData_Helper_Rating extends Mage_Core_Helper_Abstract
{
    const ITEM_TYPE_REVIEW           = 'https://schema.org/AggregateRating';

    public function addSnippets($html)
    {
        $product = Mage::registry('current_product');

        if (!$product)
            return $html;

        if (!$product->getRatingSummary()) {
            Mage::getModel('review/review')
                ->getEntitySummary($product, Mage::app()->getStore()->getId());
        }

        if (!$product->getRatingSummary() || !$product->getRatingSummary()->getRatingSummary())
            return $html;

        $attributesHtml = 'itemscope="" itemprop="aggregateRating" itemtype="'.self::ITEM_TYPE_REVIEW.'"';

        $showTotals = +Mage::getStoreConfig('amseorichdata/rating/totals');

        $metaHtml = $this->_meta(array(
            'itemprop' => 'ratingValue',
            'content' => round(($product->getRatingSummary()->getRatingSummary() * 5) / 100, 2)
        ));

        if ($showTotals & Amasty_SeoRichData_Model_Source_Reviews_Totals::TOTALS_REVIEWS)
        {
            $metaHtml .= $this->_meta(array(
                'itemprop' => 'reviewCount',
                'content' => $product->getRatingSummary()->getReviewsCount()
            ));
        }

        if ($showTotals & Amasty_SeoRichData_Model_Source_Reviews_Totals::TOTALS_VOTES)
        {
            $metaHtml .= $this->_meta(array(
                'itemprop' => 'ratingCount',
                'content' => $this->_getProductVotes($product)
            ));
        }

        $html = preg_replace('/(\<\w+\s*[^>]*)(\>)/', "\${1} $attributesHtml\${2}$metaHtml", $html, 1);

        return $html;
    }

    protected function _getProductVotes($product)
    {
        $adapter = $product->getResource()->getReadConnection();
        $select = $adapter->select()->from($product->getResource()->getTable('rating/rating_vote_aggregated'), 'vote_count')
            ->where('store_id=?', Mage::app()->getStore()->getId())
            ->where('entity_pk_value=?', $product->getId())
            ->limit(1)
        ;

        return $adapter->fetchOne($select);
    }

    protected function _meta($attributes)
    {
        $attributesHtml = '';

        foreach ($attributes as $name => $value)
            $attributesHtml .= "$name=\"$value\" ";

        return "\n<meta $attributesHtml />";
    }

    public function getSummaryRating()
    {
        $product = Mage::registry('current_product');

        if (Mage::getStoreConfigFlag('amseorichdata/yotpo/enabled')
            && Mage::helper('amseorichdata')->isYotpoReviewsEnabled()) {
            $rating = Mage::helper('yotpo/richSnippets')->getRichSnippet($product);
            $summaryRating = $rating['average_score'];
        } else {
            if (!$product->getRatingSummary()) {
                Mage::getModel('review/review')
                    ->getEntitySummary($product, Mage::app()->getStore()->getId())
                ;
            }

            $summaryRating = round(($product->getRatingSummary()->getRatingSummary() * 5) / 100, 2);
        }

        return $summaryRating;
    }
}
