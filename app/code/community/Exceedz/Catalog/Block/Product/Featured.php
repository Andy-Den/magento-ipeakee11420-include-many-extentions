<?php
/**
 * Featured Product Block
 *
 * @category   Exceedz
 * @package    Exceedz_Catalog
 */
 class Exceedz_Catalog_Block_Product_Featured extends Mage_Catalog_Block_Product_Abstract
 {
    /**
     * Get the fetured product collection
     * @return collection or false if no product.
     */
	public function getFeaturedProduct()
    {
		$featuredCategoryId = $this->_getFeaturedCategoryId();
		if( $featuredCategoryId ) {
			$productCollection = Mage::getModel('catalog/category')->load($featuredCategoryId)
								->getProductCollection()
								->addAttributeToSelect('*')
								->addUrlRewrite()
								->setPageSize(2)
								->setCurPage(1);
			return $productCollection;
		}
		return false;
	}

	/**
     * Used to get Featured Product category id
     *
     * @return id or false if not found.
     */
	private function _getFeaturedCategoryId()
    {
    	$category = Mage::registry('current_category');
		$categoryId = Mage::getModel('catalog/category')->getCollection()
								->addFieldToFilter('parent_id', $category->getId())
								->addFieldToFilter('name', 'Featured Products')
								->getFirstItem()
								->getId();
		if($categoryId > 0) {
			return $categoryId;
		}
		$parentCateogryIds = $category->getParentIds();
		if(isset($parentCateogryIds[2])) return $parentCateogryIds[2];
		return false;
	}
}