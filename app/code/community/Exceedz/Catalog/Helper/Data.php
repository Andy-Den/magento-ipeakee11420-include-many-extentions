<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Catalog data helper
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Exceedz_Catalog_Helper_Data extends Mage_Catalog_Helper_Data
{
	public function getGroupedProductCartUrl($productId,$childId,$childType)
	{
		if($childType == 'simple')
		{
			$cartUrl = Mage::getBaseUrl().'checkout/cart/addlistproduct/product/'.$childId.'/?qty=1';
		}
		elseif($childType == 'configurable')
		{
			$product = Mage::getModel('catalog/product')->load($childId);
			if ($product->getTypeInstance(true)->getConfigurableAttributes($product)) {
    			$configurableAttribute = $product->getTypeInstance(true)->getConfigurableAttributes($product);
    			$configAttributeId = $configurableAttribute->getData();
    			foreach($configAttributeId as $configId)
    			{
    				$configurableId = $configId['attribute_id'];
    			}
    		}
			if($product->getTypeInstance(true)->getConfigurableAttributesAsArray($product)) {
    				$productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
    				$attributeOptions = array();
					foreach ($productAttributeOptions as $productAttribute) {
   				 		foreach ($productAttribute['values'] as $attribute) {
        					$attributeOptions[$productAttribute['label']][$attribute['value_index']] = $attribute['store_label'];
        					break;
   				 		}
					}	
    				foreach ($attributeOptions as $key=>$value)
    				{
    					foreach ($value as $attrValue=>$val)
    					{
    						$configAttrValue = $attrValue;
    					}
    				}
    		}
    		$cartUrl = Mage::getBaseUrl().'checkout/cart/addlistproduct/product/'.$childId.'/?super_attribute['.$configurableId.']='.$configAttrValue.'&cpid='.$childId.'&qty=1';
		}
		return $cartUrl; 	
	}
}
