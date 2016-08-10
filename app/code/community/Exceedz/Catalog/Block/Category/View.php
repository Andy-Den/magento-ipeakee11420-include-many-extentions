<?php
/**
 * Rewrite Mage_Catalog_Block_Category_View
 *
 * @category   Exceedz
 * @package    Exceedz_Catalog 
 */
class Exceedz_Catalog_Block_Category_View extends Mage_Catalog_Block_Category_View
{
	/**
     * Get Featured Product Block
     *
     * @return block html
     */
    public function getFeaturedProductHtml()
	{
		return $this->getBlockHtml('product_featured');
	}
}
