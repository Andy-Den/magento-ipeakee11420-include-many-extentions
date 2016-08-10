<?php
/**
 * Short description for file
 *        This files is used to export Product details
 *
 * PHP versions All
 *
 * @category   PHP Coding File
 * @package
 * @license    As described below
 * @version    1.1.0
 * @link
 * @see        -NA-
 * @since      10-Feb-2010
 * @deprecated -NA-
 */

/*********************************************************
 * Licence:
 * This file is sole property of the installer.
 * Any type of copy or reproduction without the consent
 * of owner is prohibited.
 * If in any case used leave this part intact without
 * any modification.
 * All Rights Reserved
 * Copyright 2010 Owner
 *******************************************************/

/*******************************************
 * FOLLOWING PARAMETER NEED TO BE CHANGED *
 *******************************************/

ini_set("memory_limit", "2000M");

set_time_limit(0);

include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php');
//echo $mageFilePath;exit();

include($mageFilePath);

$db = new mysqli ($dbHostName, $dbUserName, $dbPassword, $dbName);
$dbNew = new mysqli ($dbHostName, $dbUserName, $dbPassword, $dbName);

if (mysqli_connect_errno()) {
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
}

$websites = Mage::app()->getWebsites();
$baseUrlHttpPath = '';
$storeCode = '';
$storeId = 0;
$status_options = getStockStatusOptions($db, 'custom_stock_status');

foreach ($websites as $_website) {
	$websiteId = $_website->getId();
	foreach ($_website->getStores() as $_store) {
		echo "Web ID:" . $websiteId . ' Store Code:' . $_store->getCode() . ' Store URL:' . $_store->getBaseUrl() . ' ';
		$baseUrlHttpPath = $_store->getBaseUrl();
		$storeCode = $_store->getCode();
		$storeId = $_store->getID();
		break;
	}

	$next3day = Mage::app()->getLocale()->date()->addDay(3);

	$date = date("j-n-Y");
	$dataFeedTmpFile = dirname(__FILE__) . '/google_products_' . $storeCode . '.tmp.txt';
	$file_to_write = dirname(__FILE__) . '/google_products_' . $storeCode . '.txt';
	//$WEB_PATH = "http://milandirect/";
	@unlink($dataFeedTmpFile);

	/*******************************************
	 * FUNCTIONALITY CODE STARTS BELOW *
	 *******************************************/

	$data = array(
		'id', 'mpn', 'product_type',
		'condition', 'shipping', 'shipping_label',
		'shipping_weight', 'google_product_category', 'brand',
		'availability', 'title', 'link',
		'image_link', 'price', 'sale_price',
		'description'
	);

	$arrAttribute = array(
		'name',
		'url_path',
		'image',
		'price',
		'special_price',
		'description',
		'weight',
		'special_from_date',
		'special_to_date',
		'manufacturer',
		'free_shipping',
	);
	$visibilityAttributeDetail = getAttributeDetails($db, 'visibility');
	$statusAttributeDetail = getAttributeDetails($db, 'status');
	$attributeDetailPrice = getAttributeDetails($db, 'price');
	$attributeDetailSpecialPrice = getAttributeDetails($db, 'special_price');
	$customStockStatus = getAttributeDetails($db, 'custom_stock_status');
	$preorderCalender = getAttributeDetails($db, 'preorder_calender');
	$manufacturer = getAttributeDetails($db, 'manufacturer');

	$simpleConfigurableFetch = $dbNew->query(
		'SELECT
			product_id, pk.value
		FROM
			catalog_product_super_link AS pl
				LEFT JOIN
			catalog_product_entity_url_key AS pk ON pl.parent_id = pk.entity_id;'
	);
    $simpleConfigurableUrls = array();
    while ($row = $simpleConfigurableFetch->fetch_array()) {
        $simpleConfigurableUrls[$row[0]] = $row[1];
    }

	$sql = 'SELECT '
		. ' CPE_SKU.sku '
		. ', CPE_SKU.entity_id'
		. ', CPE_SKU.attribute_set_id '
		. ', CPE_SKU.type_id '
		. ', CPEI_VISIBILITY.value AS visibility'
		. ', CPEI_STATUS.value AS status'
		. ', CPEI_STOCKSTATUS.value AS custom_stock_status'
		. ', CPEI_BRAND.value AS manufacturer'
		. ', CPEI_URL.value AS url_path'
		. ', CPEI_PREORDER.value AS preorder_calender'
		. ', CPEI_WEBSITE.website_id AS website_id'
		. ' FROM '
		. ' catalog_product_entity AS CPE_SKU'
		. ' LEFT JOIN catalog_product_entity_int AS CPEI_STATUS ON CPE_SKU.entity_id=CPEI_STATUS.entity_id'
		. ' LEFT JOIN catalog_product_entity_int AS CPEI_VISIBILITY ON CPE_SKU.entity_id=CPEI_VISIBILITY.entity_id'
		. ' LEFT JOIN catalog_product_entity_text AS CPEI_DESCRIPTION ON CPE_SKU.entity_id=CPEI_DESCRIPTION.entity_id'
		. ' LEFT JOIN catalog_product_entity_url_key AS CPEI_URL ON CPE_SKU.entity_id=CPEI_URL.entity_id'
		. ' LEFT JOIN catalog_product_entity_int AS CPEI_STOCKSTATUS ON CPE_SKU.entity_id=CPEI_STOCKSTATUS.entity_id'
		. ' AND CPEI_STOCKSTATUS.attribute_id=\'' . $customStockStatus['attribute_id'] . '\' '
		. ' LEFT JOIN catalog_product_entity_int AS CPEI_BRAND ON CPE_SKU.entity_id=CPEI_BRAND.entity_id'
		. ' AND CPEI_BRAND.attribute_id=\'' . $manufacturer['attribute_id'] . '\' '
		. ' LEFT JOIN catalog_product_entity_datetime AS CPEI_PREORDER ON CPE_SKU.entity_id=CPEI_PREORDER.entity_id'
		. ' AND CPEI_PREORDER.attribute_id=\'' . $preorderCalender['attribute_id'] . '\' '
		. ' LEFT JOIN catalog_product_website AS CPEI_WEBSITE ON CPE_SKU.entity_id=CPEI_WEBSITE.product_id'
		. ' WHERE CPEI_VISIBILITY.attribute_id=\'' . $visibilityAttributeDetail['attribute_id'] . '\' '
		. ' AND CPEI_STATUS.attribute_id=\'' . $statusAttributeDetail['attribute_id'] . '\' '
		. ' AND CPEI_STATUS.value=\'1\''
		. ' AND CPEI_DESCRIPTION.value !=\'\''
		. ' AND CPEI_WEBSITE.website_id=' . $websiteId
		. ' AND CPE_SKU.sku !=\'\''
		. ' AND CPE_SKU.type_id = "simple"'
		. ' GROUP BY entity_id '
		. ' ORDER BY type_id ';


	$result = $dbNew->query($sql);
	if ($result) {
		if ($dbNew->affected_rows <= 0) {
			echo "No Product Found";
			continue;
		}

		// Write the header row
		$counter = 0;
		importProducts($data, $dataFeedTmpFile);

		while ($row = $result->fetch_array()) {

			$entity_id = $row['entity_id'];
			$type_id = $row['type_id'];
			$visibility = $row['visibility'];
			$sku = $row['sku'];
			$attribute_set_id = $row['attribute_set_id'];
			$availability = getStockStatus($entity_id, $row['custom_stock_status'], $next3day, $status_options);

			unset($data);
			$data = array(
				'id' => $entity_id,
				'mpn' => $sku,
				'product_type' => $type_id,
				'condition' => 'new',
				'shipping' => 'Standard',
				'shipping_label' => '',
				'shipping_weight' => '',
				'google_product_category' => 'Furniture',
				'brand' => 'Milan Direct',
				'availability' => $availability,
				'title' => '',
				'link' => '',
				'image_link' => '',
				'price' => '',
				'sale_price' => '',
				'description' => '',
			);

			$resultAtt = '';
			$rowAtt = '';

			foreach ($arrAttribute as $akey => $avalue) {
				$attributeDetail = getAttributeDetails($db, $avalue);

				$attributeValue = NULL;
				$parent_id = NULL;

				if (is_array($attributeDetail) && count($attributeDetail) > 0) {

					$tableName = "catalog_product_entity_" . $attributeDetail['backend_type'];
					$attributeId = $attributeDetail['attribute_id'];

					if ($avalue == 'free_shipping') {
						$sqlAtt = ' SELECT CPINT.value '
							. ' FROM ' . $tableName . ' AS CPINT '
							. ' WHERE CPINT.attribute_id=\'' . $attributeId . '\''
							. ' AND CPINT.store_id=\'0\''
							. ' AND CPINT.entity_id=\'' . $entity_id . '\'';
					}
					elseif ($avalue == 'url_path') {
						$sqlAtt = "SELECT value"
							. " FROM catalog_product_entity_url_key WHERE"
							. " store_id = 0"
							. " AND entity_id = ". $entity_id;
					}
					elseif ($attributeDetail['backend_type'] == 'int') {
						$sqlAtt = ' SELECT EAOV.value '
							. ' FROM eav_attribute_option_value AS EAOV '
							. ' LEFT JOIN ' . $tableName . ' AS CPINT '
							. ' ON EAOV.option_id = CPINT.value'
							. ' WHERE CPINT.attribute_id=\'' . $attributeId . '\''
							. ' AND EAOV.store_id=\'0\''
							. ' AND CPINT.entity_id=\'' . $entity_id . '\'';
					}
					else {
						$sqlAtt = 'SELECT value FROM ' . $tableName
							. ' WHERE '
							. ' attribute_id=\'' . $attributeId . '\''
							. ' AND store_id=\'0\''
							. ' AND entity_id=\'' . $entity_id . '\'';
					}

					$resultAtt = $db->query($sqlAtt);
					if (!$resultAtt || $db->affected_rows <= 0) {
						continue;
					}

					$rowAtt = $resultAtt->fetch_array();
					if ($avalue == 'description') {
						$attributeValue = strip_tags($rowAtt['value'], '<p>');
						$attributeValue = str_replace("</p><p>", "</p>_<p>", $attributeValue);
						$attributeValue = str_replace("<p>", "_<p>", $attributeValue);
						$attributeValue = str_replace("_", ' ', $attributeValue);
						$attributeValue = strip_tags($attributeValue);
						$attributeValue = str_replace("        ", " ", $attributeValue);
						$attributeValue = str_replace("    ", " ", $attributeValue);
						$attributeValue = str_replace("  ", " ", $attributeValue);
						$attributeValue = str_replace("   ", " ", $attributeValue);
						$attributeValue = str_replace("       ", " ", $attributeValue);
						$attributeValue = str_replace("&reg;", "ï¿½", $attributeValue);
						$attributeValue = str_replace("&trade;", "ï¿½", $attributeValue);
						$attributeValue = str_replace("&#150;", "ï¿½", $attributeValue);
						$attributeValue = str_replace("&#8482;", "ï¿½", $attributeValue);
						$attributeValue = str_replace("&amp;", "&", $attributeValue);
						$attributeValue = str_replace("&#160;", " ", $attributeValue);
						$attributeValue = str_replace("â€š", " ,", $attributeValue);
						$attributeValue = str_replace("â€¢", "ï¿½", $attributeValue);
						$attributeValue = str_replace("â„¢", "ï¿½", $attributeValue);
						$attributeValue = str_replace("ï¿½ï¿½", "ï¿½", $attributeValue);
						$attributeValue = str_replace("ï¿½", " ", $attributeValue);
						$attributeValue = preg_replace('/\n/i', ' ', $attributeValue);

						$data['description'] = $attributeValue;
					} elseif ($avalue == 'name') {
						$data['title'] = $rowAtt['value'];
					} elseif ($avalue == 'weight') {
						$data['shipping_weight'] = $rowAtt['value'] . ' kg';
					} elseif ($avalue == 'manufacturer') {
						$data['brand'] = $rowAtt['value'];
					} elseif ($avalue == 'price') {
						$data['price'] = getBasePrice($db, $entity_id, $attributeDetailPrice, $storeId);
					} elseif ($avalue == 'special_price') {
						$data['sale_price'] = getBasePrice($db, $entity_id, $attributeDetailSpecialPrice, $storeId);
					} elseif ($avalue == 'image') {
						$data['image_link'] = getImage($sku, $baseUrlHttpPath);
					} elseif ($avalue == 'free_shipping') {
						if ($rowAtt['value'] == 1) {
							$data['shipping_label'] = 'free_shipping';
						}
					} elseif ($avalue == 'url_path') {
						// Use the parent configurable url if it's a simple product and its parent has a SEO like url.
						if (array_key_exists($entity_id, $simpleConfigurableUrls)) {
							$data['link'] = $baseUrlHttpPath . $simpleConfigurableUrls[$entity_id];
						} else {
							$data['link'] = $baseUrlHttpPath . $rowAtt['value'];
						}
					} else {
						continue;
					}
				}
			}

			if (is_null($data['link'])) {
				$data['link'] = $baseUrlHttpPath . 'catalog/product/view/id/' . $entity_id;
			}

			$counter += 1;
			$qty = getProductQty($db, $entity_id);
			if ($qty <= 0) {
				$qty = '99999999';
				$stockstatus = 'in stock';
			}

			$data = applySpecialPrice($data);

			if ($data['image_link']) {
				importProducts($data, $dataFeedTmpFile);
			}
		}

		updateDataFeeds($dataFeedTmpFile, $file_to_write);

		echo $counter . " Product Found" . PHP_EOL;

		// While End
	}
}

/**
 * Only update data feed file when the new feed is larger than 500000 bytes
 * which it should be as a valid data feed for all stores.
 *
 * @param string $tmpFile The tmp file for the data feed (newly generated this time).
 * @param string $file The data feed which will be picked up by Google.
 */
function updateDataFeeds($tmpFile, $file)
{
	if (filesize($tmpFile) > 500000) {
		@rename($tmpFile, $file);
	}
}


/**
 * BOF :: Function to Log Queries
 * @param String query
 */
function importProducts($data, $filename)
{

	if (!file_exists($filename)) {
		$fh = fopen($filename, "w");
		chmod($filename, 0777);
	} else {
		$fh = fopen($filename, "a+");
		chmod($filename, 0777);
	}

	//code to create txt file
	$bad = array('"', "\r\n", "\n", "\r", "\t");
	$good = array("", " ", " ", " ", "");
	$data = isset($data) ? str_replace($bad, $good, $data) : '';

	$feed_line = implode("\t", $data) . "\r\n";
	fwrite($fh, $feed_line);

	//fputcsv($fh, $data);
	fclose($fh);
}

/**
 * Used to Fetch the Attribute ID, Type
 *
 * @param  string $attributeKey
 */
function getAttributeDetails($db, $attributeKey)
{
	$sql = ' SELECT attribute_id, backend_type FROM eav_attribute '
		. ' WHERE entity_type_id=\'4\' AND '
		. ' attribute_code=\'' . $attributeKey . '\'';

	if ($result = $db->query($sql)) {
		$row = $result->fetch_array();
		if (count($row) > 0) {
			return $row;
		}
		$result->close();
	}

	return false;
}

/**
 * Used to Fetch the Attribute ID, Type
 *
 * @param  string $attributeKey
 */
function getProductQty($db, $entity_id)
{
	$sql = ' SELECT qty FROM cataloginventory_stock_item '
		. ' WHERE product_id=\'' . $entity_id . '\'';

	if ($result = $db->query($sql)) {
		$row = $result->fetch_array();
		if (count($row) > 0) {
			return $row[0];
		}
		$result->close();
	}

	return false;
}

function getBasePrice($db, $entity_id, $attributeDetailPrice, $store_id)
{
	$basePrice = NULL;
	$tableName = "catalog_product_entity_" . $attributeDetailPrice['backend_type'];
	$attributeId = $attributeDetailPrice['attribute_id'];

	$sqlAtt = ' SELECT CPINT.value, CPINT.store_id '
		. ' FROM '
		. ' ' . $tableName . ' AS CPINT '
		. ' WHERE CPINT.attribute_id=\'' . $attributeId . '\''
		. ' AND CPINT.entity_id=\'' . $entity_id . '\'';

	$resultAtt = $db->query($sqlAtt);

	$storePrices = array();
	if ($db->affected_rows > 0) {
		while ($rowAtt = $resultAtt->fetch_row()) {
			$storePrices[$rowAtt[1]] = $rowAtt[0];
		}

		// check store specific price set
		if (isset($storePrices[$store_id]) && $storePrices[$store_id] > 0) {
			$basePrice = $storePrices[$store_id];
		} else {
			// Set Admin store price
			$basePrice = $storePrices[0];
		}
	}

	return $basePrice;
}

/**
 * Check special price conditions. If matched then replace the price by special_price
 *
 * @params $data product data array
 * @return filter product data array
 */
function applySpecialPrice($data)
{
	$specialFromDate = strtotime($data['special_from_date']);
	$specialToDate = strtotime($data['special_to_date']);
	$today = strtotime('today');

	// If Today in between from & To date ( sometime to date not specified so ignored if not)
	if ($today >= $specialFromDate && (!$specialToDate || $today <= $specialToDate) && $data['sale_price'] > 0) {
		// do nothing
	} else {
		$data['sale_price'] = '';
	}

	return $data;
}

function getImage($sku, $baseUrlHttpPath, $format = 'jpg')
{
	if ('UK-' == strtoupper(substr($sku, 0, 3))) {
		$sku = substr($sku, 3);
	}

	$imageName = 'media/cleanimage/' . $sku . '.' . $format;

	if (file_exists('../' . $imageName)) {
		return $baseUrlHttpPath . $imageName;
	}

	return false;
}

function getUrl($entity_id, $baseUrlHttpPath, $visibility, $db, $rowAttvalue, $storeId)
{
	return $url = $baseUrlHttpPath . $rowAttvalue;
}

function getStockStatusOptions($db, $attribute_code)
{

	$sql = "SELECT `option_id`, `value` FROM `eav_attribute_option_value` WHERE `store_id`=0 AND `option_id` IN "
		. "(SELECT `option_id` FROM `eav_attribute_option` WHERE `attribute_id` IN "
		. "(SELECT `attribute_id` FROM `eav_attribute` WHERE `attribute_code`='{$attribute_code}')) ORDER BY `option_id`";

	$options = array();
	if ($result = $db->query($sql)) {
		while ($row = $result->fetch_array(MYSQLI_NUM)) {
			if (count($row) > 0) {
				$options[$row[0]] = $row[1];
			}
		}
		$result->close();
	}

	return empty($options) ? false : $options;
}

function getStockStatus($entity_id, $status_id, $next3day, $status_options)
{

	$status = array(
		'in stock',
		//'available for order',   // update Google feed
		'in stock',
		'preorder',
		'out of stock'
	);

	if (!isset($status_options[$status_id])) {
		return $status[3];
	}

	$txt = $status_options[$status_id];
	if (stripos($txt, 'expected shipment date') !== false) {
		return $status[2];
	} elseif (stripos($txt, 'in stock') !== false) {
		if (stripos($txt, '24 hours') > 0) {
			return $status[0];
		} else {
			return $status[1];
		}
	} elseif (stripos($txt, 'sold out') !== false || stripos($txt, 'out of stock') !== false) {
		return $status[3];
	} elseif (stripos($txt, 'days') !== false) {
		$_num_of_business_days = intval(preg_replace('/\D+(\d+)\D+/', "$1", $txt));

		if ($_num_of_business_days > 10) return $status[2];
	}

	return $status[1];
}
