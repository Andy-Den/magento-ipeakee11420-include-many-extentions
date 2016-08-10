<?php
set_time_limit(0);
ini_set("memory_limit","2000M");

$base_path = dirname(dirname(__FILE__));
require_once $base_path . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Mage.php';
Mage::app('admin');

$mediaFolderPath = $base_path . DIRECTORY_SEPARATOR .'media';

$status_options = getStockStatusOptions('custom_stock_status');

$excludeCategoryIds = array(165, 534, 498, 458, 117);

// Level of Brands / Homewares / Alessi => 2 / 3 / 4, minimal level is 4 means only Alessi and it's subcategories been included.
$minCategoryLevel = 4;

$debug = true;

$header = array(
		"id",
		"mpn",
		"product_type",
		"condition",
		"shipping",
		"google_product_category",
		"brand",
		"availability",
		"title",
		"link",
		"image link",
		"price",
		"description",
		"weight",
		"custom_label_0",
		"custom_label_1",
		"custom_label_2",
		"custom_label_3",
		"custom_label_4",
		);

$stores = array('australia', 'united_kingdom');

foreach($stores as $storeCode) {
	$store = Mage::app()->getStore($storeCode);
	echo $store->getId().','.$store->getCode().','.$store->getBaseUrl()."\n";

	$baseUrlHttpPath = $store->getBaseUrl();

	$websiteId = $store->getWebsiteId();

	$next3day = Mage::app()->getLocale()->date()->addDay(3);

	$date = date("j-n-Y");
	$file_to_write = dirname(__FILE__) . '/google_products_'.$storeCode.'.txt';
	//$WEB_PATH = "http://milandirect/";
	@unlink($file_to_write);

	$products = loadAllProducts($storeCode, $websiteId);

	$total = $products->getSize();

	if ($total <= 0 ) {
		echo "No Product Found\n";
		continue;
	}

	$categories = loadAllCategoryByStoreId($store->getId());

	importProducts($header, $file_to_write);

	$products->load();
	$products->addCategoryIds();

	$counter = 0;
	foreach($products as $product) {
		$image = getImage($product->getSku(), array($mediaFolderPath, $baseUrlHttpPath, $product->getImage()), $storeId);

		/**
		 * The way it checks the image is very tricky. Remove the check for now.
		 */
//		//ignore products without image
//		if (false === $image) {
//			echo "No image for ".$product->getId()."\n";
//			continue;
//		}

		$cateIds = $product->getCategoryIds();

		$levelCheckOK = false;
		$categoryLabels = array();
		for($i=0; $i< count($cateIds); $i++) {
			if (in_array($cateIds[$i], $excludeCategoryIds)) {
				//prouduct belongs to a excluded category
				echo "Product " . $product->getId(). " belongs to a excluded category ".$cateIds[$i] ."\n";
				continue 2;//next product
			}

			if (!isset( $categories[$cateIds[$i]] ) ) continue;//skip inactive category
			$category = $categories[$cateIds[$i]];

			if ($category['level'] >= $minCategoryLevel) $levelCheckOK = true;

			$categoryLabelArr = array();
			for($j=0; $j < count($category['path']); $j++) {
				if (in_array($category['path'][$j], $excludeCategoryIds)) {
					//proudct belongs to a sub category of excluded category
					echo "Product " . $product->getId(). " belongs to ". $cateIds[$i] ." which is a sub category of excluded category ". $category['path'][$j]."\n";
					continue 3;//next product
				}
				if (!isset($categories[$category['path'][$j]])) {
					//this category belongs to an inactive category, skip to next category
					continue 2;
				}
				$categoryLabelArr[] = $categories[$category['path'][$j]]['name'];
			}

			if (!empty($categoryLabelArr)) {
				$categoryLabels[] = implode(' / ', $categoryLabelArr);
			}
		}

		//prouct not belongs to any category has higher level
		if (! $levelCheckOK) {
			echo "All categories for ". $product->getId(). " are too high\n";
			continue;
		}

		$data = array();

		//order of array value here is very important.
		$data['id'] = $product->getId();
		$data['mpn'] = $product->getSku();
		$data['type_id'] = $product->getTypeId();
		$data['condition'] = "new";
		$data['shipping'] = getWeight($product->getWeight(), "Standard", $store->getId());
		$data['google_product_category'] = "Furniture";
		$data['brand'] = "Milan Direct";

		$data['availability'] = getStockStatus($product->getId(), $product->getCustomStockStatus(), $next3day, $status_options);

		$data['title'] = $product->getName();

		$data['link'] = getUrl($product, $baseUrlHttpPath, $product->getVisibility());

		$data['image'] = $image;

		$data['price'] = Mage_Catalog_Model_Product_Type_Price::calculateSpecialPrice(
				$product->getPrice(),
				$product->getSpecialPrice(),
				$product->getSpecialFromDate(),
				$product->getSpecialToDate(),
				$store
		);

		$data['description'] = filterDescription($product->getDescription());

		$data['weight'] = $product->getWeight();

		for($i=0; $i<5; $i++) {
			$data['custom_label_' . $i] = isset($categoryLabels[$i]) ? $categoryLabels[$i] : "";
		}

		importProducts($data, $file_to_write);
		$counter++;
	}

	echo $counter. " products found.\n";
}

exit;

function getStockStatus($entity_id, $status_id, $next3day, $status_options){

	$status = array(
			'in stock',
			'available for order',
			'preorder',
			'out of stock'
	);

	if (!isset($status_options[$status_id])) {
		return $status[3];
	}

	$txt = $status_options[$status_id];
	if (stripos($txt, 'expected shipment date') !==false) {
		return $status[2];
	}
	elseif (stripos($txt, 'in stock') !== false) {
		if (stripos($txt, '24 hours') > 0) {
			return $status[0];
		}
		else{
			return $status[1];
		}
	}
	elseif (stripos($txt, 'sold out') !== false || stripos($txt, 'out of stock') !== false) {
		return $status[3];
	}
	elseif (stripos($txt, 'days') !== false ) {
		$_num_of_business_days = intval(preg_replace('/\D+(\d+)\D+/', "$1", $txt));

		if ($_num_of_business_days > 10) return $status[2];
	}

	return $status[1];
}

function getStockStatusOptions($attributeCode, $withEmpty=false) {
	$attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attributeCode);

	$options = $attribute->getSource()->getAllOptions($withEmpty);
    foreach ($options as $k=>$v)
    {
        $result[$v['value']] = $v['label'];
    }
    return $result;
}

function loadAllCategoryByStoreId($storeId, $conn=null) {
	$storeId = intval($storeId);

	$tableName = 'catalog_category_flat_store_' . $storeId;

	$sql = "SELECT entity_id, path, level, name FROM $tableName WHERE is_active=1";

	$categories = array();

	if (null == $conn) {
		$resource = Mage::getSingleton('core/resource');
		$conn = $resource->getConnection('core_read');
	}

	$results = $conn->fetchAll($sql);
	for($i=0; $i<count($results); $i++) {
		$path = $results[$i]['path'];
		$path = str_replace('1/2/', '', $path);

		$categories[$results[$i]['entity_id']] = array('path' => explode('/', $path), 'level' => $results[$i]['level'], 'name' => $results[$i]['name']);
	}

	return $categories;
}

function importProducts($data, $filename)
{
	$fh	= fopen($filename,"a");
	chmod($filename,0777);

	//code to create txt file
	$bad=array('"',"\r\n","\n","\r","\t");
	$good=array(""," "," "," ","");
	$data = isset($data) ? str_replace($bad,$good,$data) : '';
	$feed_line = implode("\t", $data)."\r\n";
	fwrite($fh, $feed_line);

	//fputcsv($fh, $data);
	//fclose($fh);
}

function loadAllAttributes(){
	return array(
			'name',
			'url_path',
			'image',
			'price',
			'description',
			'weight',
			'special_price',
			'special_from_date',
			'special_to_date',
			'custom_stock_status',
			'preorder_calender',
			);
}

function loadAllProducts($storeCode, $websiteId)
{
	$attributes = loadAllAttributes();
	$products = Mage::getModel('catalog/product')->getResourceCollection()
	->addAttributeToFilter('visibility', 4)
	//->setVisibility(4)
	->addStoreFilter($storeCode)

	;

	foreach($attributes as $attr) {
		$products->addAttributeToSelect($attr, 'left');
	}

	//echo $products->getSelect()->__toString() . "\n";
	return $products;
}

function getImage($sku, $data, $storeId, $format="jpg"){

	$mediaFolderPath = $data[0];

	$baseUrlHttpPath = $data[1];

	//all store use same folder
	$prefix = "cleanimage";

	if ('UK-' == strtoupper(substr($sku, 0, 3))) {
		$sku = substr($sku, 3);
	}

	$imageName =  $prefix . '/' . $sku . "." . $format;

	if (file_exists($mediaFolderPath . '/' . $imageName)) {
		return $baseUrlHttpPath . 'media/' . $imageName;
	}

	//return $baseUrlHttpPath.'media/catalog/product/placeholder/default/no-image-262-262.jpg';
	return false;
}

function getWeight($weight, $shipping, $storeId) {
	$result = $shipping;

	if (3 == $storeId && !empty($weight) ) {
		$result = round(($weight * 0.4 + 13.2) * 1.5, 2);

		$result = 'GB:::' . sprintf("%.2f", $result) . ' GBP';
	}
	return $result;
}

function filterDescription($description) {
	$description = strip_tags($description, '<p>');
	$description = str_replace("</p><p>", "</p>_<p>", $description);
	$description = str_replace("<p>", "_<p>", $description);
	$description = str_replace("_", ' ', $description);
	$description = strip_tags($description);
	$description = str_replace("        ", " ", $description);
	$description = str_replace("    ", " ", $description);
	$description = str_replace("  ", " ", $description);
	$description = str_replace("   ", " ", $description);
	$description = str_replace("       ", " ", $description);
	$description = str_replace("&reg;", "�", $description);
	$description = str_replace("&trade;", "�", $description);
	$description = str_replace("&#150;", "�", $description);
	$description = str_replace("&#8482;", "�", $description);
	$description = str_replace("&amp;", "&", $description);
	$description = str_replace("&#160;", " ", $description);
	$description = str_replace("‚", " ,", $description);
	$description = str_replace("•", "�", $description);
	$description = str_replace("™", "�", $description);
	$description = str_replace("��", "�", $description);
	$description = str_replace("�", " ", $description);
	$description = preg_replace('/\n/i', ' ', $description);
	return $description;
}

function getUrl($product, $baseUrlHttpPath, $visibility) {
	$url = '';
	if( $visibility != '4' && 'simple' !== $product->getTypeId()) {
		//Fetch Parent ID
		$parentIds = $product->getTypeInstance()->getParentIdsByChild($product->getId());
		$parent_id = empty($parentIds) ? null : $parentIds[0];
		if($parent_id) {
			$url = $baseUrlHttpPath . 'catalog/product/view/id/'. $parent_id;
		}
	}

	$url = empty($url) ? $baseUrlHttpPath . 'catalog/product/view/id/'. $product->getId() : $url;

	return $url;
}
