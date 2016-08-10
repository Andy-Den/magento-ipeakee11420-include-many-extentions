<?php
/**
 * Product Reviews Page
 *
 * @category   Exceedz
 * @package    Exceedz_Review
 */
class Exceedz_Review_Block_Product_View extends Mage_Review_Block_Product_View
{
    /**
     * Replace review summary html with more detailed review summary
     * Reviews collection count will be jerked here
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $templateType
     * @param bool $displayIfNoReviews
     * @return string
     */
    public function getReviewsSummaryHtmlForEbay(Mage_Catalog_Model_Product $product, $templateType = false, $displayIfNoReviews = false)
    {
        return $this->getLayout()->createBlock('rating/entity_detailed')->setEntityId($product->getId())->toHtml();
    }

    public function getReviewsCollectionForEbay(Mage_Catalog_Model_Product $product)
    {
        if (null === $this->_reviewsCollection) {
            $this->_reviewsCollection = Mage::getModel('review/review')->getCollection()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->addEntityFilter('product',$product->getId())
                ->setDateOrder()
                ->addRateVotes();
        }
        return $this->_reviewsCollection;
    }    
}
