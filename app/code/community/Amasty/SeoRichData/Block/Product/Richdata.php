<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */

class Amasty_SeoRichData_Block_Product_Richdata extends Mage_Catalog_Block_Product_Abstract
{

	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('amasty/amseorichdata/catalog/product/richdata.phtml');
	}

	public function _toHtml()
	{
		if (!Mage::getStoreConfig('amseorichdata/product/enabled') ||
			(!Mage::registry('current_product') && ! Mage::registry('product'))
		) {
			return '';
		}

		$path = Mage::helper('catalog')->getBreadcrumbPath();
		array_pop($path);

		$categories = array();
		foreach ($path as $item) {
			$categories[] = $item['label'];
		}

		$this->setData('categoryPath', implode(' > ', $categories));

		return parent::_toHtml();
	}

}