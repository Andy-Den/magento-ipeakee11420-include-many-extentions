<?php

/**
* Web In Color
*
* NOTICE OF LICENSE
*
* This source file is subject to the EULA
* that is bundled with this package in the file WIC-LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://store.webincolor.fr/WIC-LICENSE.txt
* 
* @package		WIC_Criteotags
* @copyright   Copyright (c) 2010-2014 Web In Color (http://www.webincolor.fr)
* @author		Web In Color <contact@webincolor.fr>
**/



class WIC_Criteotags_Model_Export_Xml
{

    private $_logfilename = 'wic_criteo.log';
    private $_exportfilename = 'export-criteo.xml';
    const XML_CONFIG_DESCRIPTION = 'criteotags/export_criteo/description';    
    const XML_CONFIG_NAME_TEMPLATE = 'criteotags/export_criteo/name_template';    

	function runExport()
	{		
	try {
		
		$_data = array();
		
			
$j=0;
		foreach($this->getProducts() as $productId) {
$j++;			            
			$prices = array();
			$url = '';
			$product = Mage::getModel('catalog/product')->load($productId);
			
			if ($product->isGrouped() || $product->isConfigurable()) {

				if($product->isGrouped()) {
					$grouped_prods = $product->getTypeInstance()->getAssociatedProductIds();
				} else {
					$grouped_prods = $product->getTypeInstance()->getUsedProductIds();
				}

				foreach($grouped_prods as $id)
				{
					$subProds = Mage::getModel('catalog/product')->load($id);
					
					$subprices[] = array((float)$subProds->getFinalPrice(),(float)$subProds->getPrice());
					if ($subProds->getVisibility() <= 1)
					{
						$url = str_replace('index.php/', '', Mage::getBaseUrl('web').$product->getUrlPath());
					}
										
					$_data[$id] = $this->getProductDetails($subProds,$url);
					
				}
				
				array_multisort($subprices);
				$price_array = current($subprices);
				$prices['price'] = round(current($price_array),2);
				$prices['retailprice'] = round(next($price_array),2);
			}

			
			$_data[$productId]  = $this->getProductDetails($product,'',$prices);
			
			unset($prices);
			unset($subprices);
			unset($grouped_prods);
			unset($product);	
			
		//	if ($j > 500) break;

		}					
		//array_unique($_data);
		//ksort($_data);
		$this->export2XML($_data);
		unset($_data);
			

		
		
		} catch (Exception $e) {
            $this->log('Error in getting Data: '.$e->getMessage());
        }
		
	}
	
	private function getProducts()
	{
		ini_set('memory_limit', '1024M');
		
		$products = Mage::getModel('catalog/product')->getCollection();
		$products->addAttributeToFilter('status', 1);//enabled
		$products->addAttributeToFilter('visibility', 4);//catalog, search
//		$products->addAttributeToSelect('*');
		$prodIds=$products->getAllIds();
		
		return $prodIds;
	}
	
	
	protected function getProductDetails($product, $url = null, $prices = null)
	{
		$_data = array();
		$i=0;
		
		$_data['name'] 			= $this->getName($product);
		if (!empty($url))
		{
			$_data['producturl']	= $url;
		} else {
			$_data['producturl']	= str_replace('index.php/', '', Mage::getBaseUrl('web').$product->getUrlPath());
		}
		
		$_image = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product'.$product->getImage();
		if (!fopen($_image, "r")) {
			$_data['smallimage']	= "";
		} else {
			$_data['smallimage']	= $_image;
		}
		
		$_data['description']	= $this->getDescriptionReplace($product);
		if (!empty($prices))
		{
			foreach ($prices as $key => $price)
			{
				$_data[$key] = $price;
			}
		}
		else
		{
			$_data['price'] 		= round($product->getFinalPrice(), 2);
			$_data['retailprice'] 	= round($product->getPrice(), 2);
		}
		$_data['instock']		= $product->getStockItem()->getIsInStock() ? '1' : '0';
		foreach ($product->getCategoryIds() as $CategoryId)
		{
			$i++;
			$_data['categoryid'.$i] = $CategoryId;
			if ($i >=3) break;
		}
		$_data['discount'] = "";
		
		if($_data['retailprice'] > 0)
		{
			$_discount = round(100*(1-($_data['price']/$_data['retailprice'])),0);
			if($_discount > 0) $_data['discount'] = $_discount;
		}			
		
		return $_data;
	}
	

   protected function export2XML($data)
   {
		$xml = new DOMDocument('1.0','utf-8');
		$xml->formatOutput = true;
		
		$root = $xml->createElement('products');
		$root = $xml->appendChild($root);
   		
   		foreach($data as $id => $_data){
									
			$xproduct = $xml->createElement('product');
			$xproductattribute = $xml->createAttribute('id');
			$xproductattribute->value = $id;		
			$xproduct->appendChild($xproductattribute);
			$xproduct = $root->appendChild($xproduct);
			
			foreach($_data as $attribute => $value)
			{
				$node = $xml->createElement($attribute, htmlspecialchars(strip_tags($value),ENT_COMPAT,'UTF-8'));
	    		$xproduct->appendChild($node);
			}
						
		}
		
		// Write the feed
		$xml->save($this->getExportFilePath());		

   }
   
	public function getDescriptionReplace($product)
	{
		
		$attr =  Mage::getStoreConfig(self::XML_CONFIG_DESCRIPTION, Mage::app()->getStore()->getStoreId());
		
		return $this->getAttributeValue($product,$attr);
				
	}
	
	public function getAttributeValue($product,$attributecode)
	{
		$attr = $product->getResource()->getAttribute($attributecode);
		if($attr) {
			$inputType = $attr->getFrontend()->getInputType();
	
			switch ($inputType) {
				case 'multiselect':
				case 'select':
				case 'dropdown':
					$value = $product->getAttributeText($attributecode);
					if (is_array($value)) {
						$value = implode(', ', $value);
					}
					break;
				default:
					$value = $product->getData($attributecode);
					break;
			}
			
			return $value;
		}
		
			return "";
		
	}
	
	public function getName($product)
	{
		$name_template =  Mage::getStoreConfig(self::XML_CONFIG_NAME_TEMPLATE, Mage::app()->getStore()->getStoreId());
		if (!empty($name_template))
		{
			return $this->_parseAttributes($product,$name_template);
		} 
		
		return $product->getName();
	}
	
    /**
     * Parses template and insert attribute values
     *
     * @param string $tpl template
     * @param  Mage_Catalog_Model_Product $p product
     * @return string
     */
    protected function _parseAttributes($p, $tpl)
    {
        $vars = array();
        preg_match_all('/{([a-z\_\|0-9]+)}/', $tpl, $vars);
        if (!$vars[1]){
            return $tpl;    
        }
        $vars = $vars[1];
        
        foreach ($vars as $codes){
            $value = '';
            foreach (explode('|', $codes) as $code){
                $value = $this->getAttributeValue($p, $code); 
                if ($value){
                     break; 
                }        
            }
            if ($value)
                $tpl = str_replace('{' . $codes . '}', $value, $tpl);
        }
        
        return $tpl;
    }   	
    
    
	
	public function getExportFilePath()
	{
		return Mage::getBaseDir('media').DS.$this->_exportfilename;
	}
	
	public function getExportFileUrl()
	{
		return Mage::getBaseUrl('media').$this->_exportfilename;
	}	
	
	public function log($message)
	{
		Mage::log($message, null, $this->_logfilename);
	}	

}