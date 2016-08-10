<?php
class Balance_ConnectFurniture_Helper_Data extends Mage_Core_Helper_Abstract
{

	public function exportFeed()
	{
        $store_id = Mage::getStoreConfig('connectfurniture/export/store');
		Mage::app()->setCurrentStore($store_id);
		$productCollection = Mage::getResourceModel('catalog/product_collection')
                        			->addAttributeToSelect(array('name','description','brand', 'manufacturer','price','special_price'))
                                    ->addStoreFilter($store_id)
                        			->joinField('qty','cataloginventory/stock_item','qty','product_id=entity_id','{{table}}.stock_id=1','left')
                        			->joinField('is_in_stock','cataloginventory/stock_item','is_in_stock','product_id=entity_id','{{table}}.stock_id=1','left')
                                    ->addAttributeToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
                                    ->addAttributeToFilter('status', array('eq' => 1));
        $xml_template = Mage::getStoreConfig('connectfurniture/export/format');

        $io = new Varien_Io_File();
        $path = Mage::getBaseDir() . DS . 'feeds';
        $name = 'connectfurniture.xml';
        $file = $path . DS . $name;
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $path));
        $io->streamOpen($file, 'w+');
        $io->streamLock(true);

        $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\r\n");
        $io->streamWrite('<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">' . "\r\n");
        $io->streamWrite('<channel>' . "\r\n");
        $io->streamWrite('<title>'.Mage::getStoreConfig('general/store_information/name', $store_id).'</title>' . "\r\n");
        $io->streamWrite('<link>'.Mage::app()->getStore($store_id)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK).'</link>' . "\r\n");
        $io->streamWrite('<description>Product feed for ConnectFurniture.com.au</description>' . "\r\n");
        foreach ($productCollection as $product) {
			if ($product->getIsInStock() == 1) {
				$stockStatus = 'in stock';
			} else {
				$stockStatus =  'out of stock';
			}
			$qty = $product->getQty();
			$price = $product->getPrice();
			$specialPrice = $product->getSpecialPrice();
			if ($product->getTypeId()=='simple') {
				$parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
				if($parentIds){
					continue;
				}
			}
			if ($product->getTypeId()=='configurable') {
				$childProducts = Mage::getModel('catalog/product_type_configurable')
					->getUsedProducts(null, $product);
				$specialPrices = array();
				$originalPrices = array();

				$allChildOutStock = true;
				foreach ($childProducts as $child){
					$_child = Mage::getModel('catalog/product')->load($child->getId());
					$specialPrices[] =  $_child->getFinalPrice();
					$originalPrices[] = $_child->getPrice();
					if ($child->getIsInStock() == 1) {
						$qty += $_child->getStockItem()->getQty();
						$allChildOutStock = false;
					}
				}
				if ($allChildOutStock) {
					$stockStatus =  'out of stock';
				}
				if (count($originalPrices)) {
					$price = min($originalPrices);
				}
				if (count($specialPrices)) {
					$minSpecialPrice = min($specialPrices);
					if ($price > $minSpecialPrice) {
						$specialPrice = $minSpecialPrice;
					} else {
						$specialPrice = '';
					}
				}

			}
			$brand = $product->getAttributeText('manufacturer');
			if (!$brand) {
				$brand = Mage::getStoreConfig('general/store_information/name', $store_id);
			}
			$brand = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $brand);
        	$temp = array(
        		'PRODUCT:NAME',
        		'PRODUCT:URL',
        		'PRODUCT:DESCRIPTION',
        		'PRODUCT:SKU',
        		'PRODUCT:BRAND',
        		'PRODUCT:PRICE',
        		'PRODUCT:SPECIAL_PRICE',
        		'PRODUCT:STOCK',
        		'PRODUCT:QTY'
        	);


        	$data = array(
        		$product->getName(),
        		$product->getProductUrl(true),
        		$product->getDescription(),
        		$product->getSku(),
				$brand,
				$price,
				$specialPrice,
        		$stockStatus,
				$qty
        	);

        	$xml_feed_data = str_replace($temp,$data,$xml_template);
        	$io->streamWrite($xml_feed_data);
        }
        $io->streamWrite("\r\n" . '</channel>' . "\r\n");
        $io->streamWrite('</rss>');
	}
}