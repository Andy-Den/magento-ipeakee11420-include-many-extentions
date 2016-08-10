<?php
/**
 * Short description for file
 * 		This files is used to export Product details
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

ini_set("memory_limit","2000M");

set_time_limit(0);

include(dirname(__FILE__) . DIRECTORY_SEPARATOR .'config.php');
//echo $mageFilePath;exit();

include($mageFilePath);

$db = new mysqli ( $dbHostName, $dbUserName, $dbPassword, $dbName );
$dbNew = new mysqli ( $dbHostName, $dbUserName, $dbPassword, $dbName );

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

$websites = Mage::app()->getWebsites();
$baseUrlHttpPath = '';
$storeCode = '';
$storeId= 0;
$categories = array();
$status_options = getStockStatusOptions($db, 'custom_stock_status');
$excludeCategoryIds = array(165, 534, 498, 458, 117);
$minCategoryLevel = 4;
$rateRules = array();

foreach ($websites as $_website) {
    $websiteId = $_website->getId();
    foreach ($_website->getStores() as $_store) {
        echo "\nWeb ID:".$websiteId.' Store Code:'.$_store->getCode().' Store URL:'.$_store->getBaseUrl();
        $baseUrlHttpPath = $_store->getBaseUrl();
        $storeCode = $_store->getCode();
        $storeId = $_store->getID();
        break;
    }

    $next3day = Mage::app()->getLocale()->date()->addDay(3);

    $date = date("j-n-Y");
    $file_to_write = dirname(__FILE__) . '/google_products_'.$storeCode.'.txt';
    //$WEB_PATH = "http://milandirect/";
    @unlink($file_to_write);

    //check tablerates exist
    $existTablerate = false;
    if (separateTablerateCsv($storeCode)){
        $existTablerate = true;
    }

    /*******************************************
     * FUNCTIONALITY CODE STARTS BELOW *
     *******************************************/

    if($storeCode == 'united_kingdom'){
        $data = array("id","mpn","product_type","condition","shipping_label","google_product_category","brand","availability","title", "link", "image link", "price","sale_price","description","shipping_weight");
    } elseif ($storeCode == "australia") {
        $data = array("id","mpn","product_type","condition","shipping","shipping_label","google_product_category","brand","availability","title", "link", "image link", "price","sale_price","description","shipping_weight","custom_label_0","custom_label_1","custom_label_2","custom_label_3","custom_label_4");
        $arrAttribute[] = 'aw_os_category_display';
    }

    $arrAttribute[] = 'name';
    $arrAttribute[] = 'url_path';
    $arrAttribute[] = 'image';
    $arrAttribute[] = 'price';
    $arrAttribute[] = 'special_price';
    $arrAttribute[] = 'description';
    $arrAttribute[] = 'weight';
    $arrAttribute[] = 'special_from_date';
    $arrAttribute[] = 'special_to_date';
    $arrAttribute[] = 'manufacturer';

    $visibilityAttributeDetail 		= getAttributeDetails($db, 'visibility');
    $statusAttributeDetail 	   		= getAttributeDetails($db, 'status');
    $attributeDetailPrice 	   		= getAttributeDetails($db, 'price');
    $attributeDetailSpecialPrice 	= getAttributeDetails($db, 'special_price');
    $customStockStatus				= getAttributeDetails($db, 'custom_stock_status');
    $preorderCalender				= getAttributeDetails($db, 'preorder_calender');
    $manufacturer					= getAttributeDetails($db, 'manufacturer');

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
        . ' AND CPEI_STOCKSTATUS.attribute_id=\''.$customStockStatus['attribute_id'].'\' '
        . ' LEFT JOIN catalog_product_entity_int AS CPEI_BRAND ON CPE_SKU.entity_id=CPEI_BRAND.entity_id'
        . ' AND CPEI_BRAND.attribute_id=\''.$manufacturer['attribute_id'].'\' '
        . ' LEFT JOIN catalog_product_entity_datetime AS CPEI_PREORDER ON CPE_SKU.entity_id=CPEI_PREORDER.entity_id'
        . ' AND CPEI_PREORDER.attribute_id=\''.$preorderCalender['attribute_id'].'\' '
        . ' LEFT JOIN catalog_product_website AS CPEI_WEBSITE ON CPE_SKU.entity_id=CPEI_WEBSITE.product_id'
        . ' WHERE CPEI_VISIBILITY.attribute_id=\''.$visibilityAttributeDetail['attribute_id'].'\' '
        . ' AND CPEI_STATUS.attribute_id=\''.$statusAttributeDetail['attribute_id'].'\' '
        . ' AND CPEI_STATUS.value=\'1\''
//        . ' AND CPEI_VISIBILITY.value=\'4\''
        . ' AND CPEI_DESCRIPTION.value !=\'\''
        . ' AND CPEI_WEBSITE.website_id='.$websiteId
        . ' AND CPE_SKU.sku !=\'\''
        . ' AND CPE_SKU.type_id = "simple"'
        . ' GROUP BY entity_id '
        . ' ORDER BY type_id '
    ;

//echo PHP_EOL.$sql.PHP_EOL;die;
    $mageDb = Mage::getSingleton('core/resource')->getConnection('core_write');
    $superLinks = $mageDb->fetchAll('select product_id, parent_id from catalog_product_super_link');
    $simpleConfigurables = array();
    foreach ($superLinks as $s) {
        $simpleConfigurables[$s['product_id']] = $s['parent_id'];
    }
    $result = $dbNew->query($sql);
    if($result){
        if($dbNew->affected_rows <=0){
            echo "No Product Found";
            continue;
        }

//        $testSkus = array('GH000431', 'GH000435', '	GH000436', 'GH000438');
        $testSkus = array('GH000431');

        $counter = 0;
        importProducts($data, $file_to_write);

        $arrParent = NULL;
        $arrParentStatus = NULL;

        $categories = loadAllCategoryByStoreId($storeId);
        while ($row = $result->fetch_array()) {

            $entity_id	= $row['entity_id'];
            $type_id	= $row['type_id'];
            $visibility = $row['visibility'];
            $sku		= $row['sku'];
            $categoryId   = $row['website_id'];
            $attribute_set_id = $row['attribute_set_id'];
            $condition = "new";
            $shipping = "Standard";
            $location = "";

            if (!in_array($sku, $testSkus)) {
                continue;
            }
print_r($row);
            //$manufacturer = "Milan Direct";
            $availability = getStockStatus($entity_id, $row['custom_stock_status'], $next3day, $status_options);
            $value = NULL;
            unset($data);
            $data = array();
            $url = false;

            $data['entity_id'] = $entity_id;
            $data['mpn'] = $sku;
            //$data[] = $visibility;
            $data['type_id'] = $type_id;

            $categoruUrl = getCategoryProduct($db, $categoryId);

            //$google_product_category = getGoogleCategory($db, $categoryId);

            $google_product_category = "Furniture";

            //$data[] = $categoruUrl;
            $data['condition'] = $condition;

            if($storeCode == 'australia'){
                $data['shipping'] = $shipping;
            }

            $data['shipping_label'] = "";

            $data['google_product_category'] = $google_product_category;
            $data['manufacturer'] = 'Milan Direct';
            $data['availability'] = $availability;

            $resultAtt = '';
            $rowAtt = '';

            // Skip simple that is not visible and does not have a parent configurable product.
            if ($row['visibility'] != 4 && !array_key_exists($row['entity_id'], $simpleConfigurables)) {
                continue;
            }

            foreach($arrAttribute AS $akey=>$avalue) {
                $attributeDetail = getAttributeDetails($db, $avalue);

                $attributeValue = NULL;
                $parent_id = NULL;

                if ( is_array($attributeDetail) && count($attributeDetail) > 0 ) {

                    $tableName		= "catalog_product_entity_".$attributeDetail['backend_type'];
                    $attributeId    = $attributeDetail['attribute_id'];

                    if ($avalue == 'url_path') {
                        if ($storeId == 0) {
                            $sqlAtt = 'select value from catalog_product_entity_url_key where store_id = 0 and entity_id = '.$entity_id.' order by store_id desc limit 1;';
                        } else {
                            $sqlAtt = 'select value from catalog_product_entity_url_key where store_id IN (0,'.$storeId.') and entity_id = '.$entity_id.' order by store_id desc limit 1;';
                        }
                        echo $sqlAtt.PHP_EOL;
                    }
                    else if( $attributeDetail['backend_type'] == 'int' ) {
                        $sqlAtt = ' SELECT EAOV.value '
                            . ' FROM eav_attribute_option_value AS EAOV '
                            . ' LEFT JOIN '.$tableName . ' AS CPINT '
                            . ' ON EAOV.option_id = CPINT.value'
                            . ' WHERE CPINT.attribute_id=\''.$attributeId.'\''
                            . ' AND EAOV.store_id=\'0\''
                            . ' AND CPINT.entity_id=\''.$entity_id.'\'';
                    }
                    else {
                        $sqlAtt = 'SELECT value FROM '.$tableName
                            . ' WHERE '
                            . ' attribute_id=\''.$attributeId.'\''
                            . ' AND store_id=\'0\''
                            . ' AND entity_id=\''.$entity_id.'\'';
                    }

                    $resultAtt = $db->query($sqlAtt);
                    if($resultAtt){
                        if($db->affected_rows>0) {
                            $rowAtt = $resultAtt->fetch_array();

                            if( $avalue == 'description' ) {
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
                                $attributeValue = str_replace("&reg;", "�", $attributeValue);
                                $attributeValue = str_replace("&trade;", "�", $attributeValue);
                                $attributeValue = str_replace("&#150;", "�", $attributeValue);
                                $attributeValue = str_replace("&#8482;", "�", $attributeValue);
                                $attributeValue = str_replace("&amp;", "&", $attributeValue);
                                $attributeValue = str_replace("&#160;", " ", $attributeValue);
                                $attributeValue = str_replace("‚", " ,", $attributeValue);
                                $attributeValue = str_replace("•", "�", $attributeValue);
                                $attributeValue = str_replace("™", "�", $attributeValue);
                                $attributeValue = str_replace("��", "�", $attributeValue);
                                $attributeValue = str_replace("�", " ", $attributeValue);
                                $attributeValue = preg_replace('/\n/i', ' ', $attributeValue);
                            }elseif($avalue == 'weight'){
                                if($storeCode == 'united_kingdom'){
                                    $attributeValue = $rowAtt['value'];
                                }else{
                                    $attributeValue = $rowAtt['value']." kg";
                                    $data['shipping'] = getWeight($rowAtt['value'], $shipping, $storeId);
                                }
                            }
                            elseif($avalue == 'price') {
                                if($rowAtt['value'] != null && $visibility !='3'){
                                    $productprice = '';
                                    $productprice = getBasePrice($db,$entity_id,$attributeDetailPrice, $storeId);
                                }
                                else{
                                    $productprice = '';
                                    $productprice = getBasePrice($db,$entity_id,$attributeDetailPrice, $storeId);
                                }
                                $attributeValue  = $productprice;
                            }
                            elseif($avalue == 'special_price') {
                                if($rowAtt['value'] != null && $visibility !='3'){
                                    $productprice = '';
                                    $productprice = getBasePrice($db,$entity_id,$attributeDetailSpecialPrice, $storeId);
                                }
                                else{
                                    $productprice = '';
                                    $productprice = getBasePrice($db,$entity_id,$attributeDetailSpecialPrice, $storeId);
                                }
                                $attributeValue  = $productprice;
                            }
                            elseif( $avalue == 'image' ) {
                                $attributeValue = getImage($sku, array($mediaFolderPath, $baseUrlHttpPath, $rowAtt['value']), $storeId);
                            }
                            elseif($avalue == 'aw_os_category_display') {
                                if ($rowAtt['value'] == 1) {
                                    $attributeValue ="free_shipping";
                                    $data['shipping_label'] = $attributeValue;
                                }
                            }
                            elseif( $avalue == 'url_path' ) {
                                $attributeValue = getUrl($entity_id, $baseUrlHttpPath, $visibility, $db, $rowAtt['value'], $storeId);
                            }
                            else {
                                $attributeValue = $rowAtt['value'];
                            }
                        }
                        else {
                            if($avalue == 'price') {
                                $productprice = '';
                                $productprice = getBasePrice($db,$entity_id,$attributeDetailPrice, $storeId);
                                $attributeValue  = $productprice;
                            } elseif($avalue == 'manufacturer') {
                                $attributeValue = 'Milan Direct';
                                if($rowAtt['value'] != '')
                                    $attributeValue = $rowAtt['value'];
                            }
                        }
                    }
                }

                $data[$avalue] = $attributeValue;
            }

            if (is_null($data['url_path'])) {
                $data['url_path'] = $baseUrlHttpPath . 'catalog/product/view/id/'. $entity_id;
            }

            $counter+=1;
            $qty = getProductQty($db, $entity_id);
            if( $qty <= 0 ) {
                $qty = '99999999';
                $stockstatus= 'in stock';
            }

            unset($data['aw_os_category_display']);
            $categoryLabels = getProductLabels($data['entity_id'],$storeId);

            for($i=0;$i< count($categoryLabels); $i++) {
                $categoryLabels[$i]=iconv('UTF-8', 'ASCII//TRANSLIT', $categoryLabels[$i]);
            }
            if($_website->getCode() != 'uk'){
                for($i=0; $i<5; $i++) {
                    $data['custom_label_' . $i] = isset($categoryLabels[$i]) ? $categoryLabels[$i] : "";
                }
            }

            $data = applySpecialPrice($data);
print_r($data);
//            if($data['image']){
                importProducts($data, $file_to_write);
//            }
        }

        echo $counter." Product Found";

        // While End
    }
}


/**
 * BOF :: Function to Log Queries
 * @param String query
 */
function importProducts($data, $filename)
{

    if (!file_exists($filename)) {
        $fh	= fopen($filename,"w");
        chmod($filename,0777);
    }
    else{
        $fh	= fopen($filename,"a+");
        chmod($filename,0777);
    }

    //code to create txt file
    $bad=array('"',"\r\n","\n","\r","\t");
    $good=array(""," "," "," ","");
    $data = isset($data) ? str_replace($bad,$good,$data) : '';

    $feed_line = implode("\t", $data)."\r\n";
    fwrite($fh, $feed_line);

    //fputcsv($fh, $data);
    fclose($fh);
}
/**
 * Used to Fetch the Attribute ID, Type
 *
 * @param  string	$attributeKey
 */
function getAttributeDetails($db, $attributeKey)
{
    $sql = ' SELECT attribute_id, backend_type FROM eav_attribute '
        . ' WHERE entity_type_id=\'4\' AND '
        . ' attribute_code=\''.$attributeKey.'\'';

    if ($result = $db->query($sql)){
        $row	= $result->fetch_array();
        if(count($row)>0){
            return $row;
        }
        $result->close();
    }

    return false;
}

/**
 * Used to Fetch the Parent Product
 *
 * @param	int		$entity_id
 */
function getParentId($db, $entity_id)
{
    $groupedLinkTypeId = '3';

    //Query database for Config Product
    $select = " SELECT CPSL.parent_id FROM catalog_product_super_link AS CPSL LEFT JOIN catalog_product_entity_int AS CPEI_STATUS "
        . " ON CPSL.parent_id=CPEI_STATUS.entity_id "
        . " WHERE CPEI_STATUS.value = '1'"
        . " AND CPSL.product_id = '".$entity_id."'"
        . " LIMIT 0,1 ";

    if ($result = $db->query($select)){
        $row	= $result->fetch_array();
        if(count($row)>0){
            $parentId = $row[0];
            return $parentId;
        }
        $result->close();
    }

    //Query database for Group Product
    $select = " SELECT CPL.product_id "
        . " FROM catalog_product_link AS CPL "
        . " LEFT JOIN catalog_product_entity_int AS CPEI_STATUS ON CPL.product_id=CPEI_STATUS.entity_id "
        . " WHERE CPL.linked_product_id = '".$entity_id."' "
        . " AND CPL.link_type_id = '".$groupedLinkTypeId."'"
        . " AND CPEI_STATUS.value = '1'"
        . " LIMIT 0,1 ";

    if ($result = $db->query($select)){
        $row	= $result->fetch_array();
        if(count($row)>0){
            $parentId = $row[0];
            return $parentId;
        }
        $result->close();
    }
    return false;
}


/**
 * Used to Fetch the Attribute ID, Type
 *
 * @param  string	$attributeKey
 */
function getProductQty($db, $entity_id)
{
    $sql = ' SELECT qty FROM cataloginventory_stock_item '
        . ' WHERE product_id=\''.$entity_id.'\'';

    if ($result = $db->query($sql)){
        $row	= $result->fetch_array();
        if(count($row)>0){
            return $row[0];
        }
        $result->close();
    }

    return false;
}


function getCategoryProduct($db, $categoryId) {
    $categoriesData = array();
    $sqlAll = 'SELECT entity_id, name, path, url_path FROM catalog_category_flat_store_1 WHERE entity_id NOT IN (\'1\',\'2\') AND name!=\'\'';
    if($resultData = $db->query($sqlAll)){
        while($rowAll = $resultData->fetch_object()){
            $categoriesData[$rowAll->entity_id] = $rowAll->name;
        }
    }

    $sql = 'SELECT entity_id,name, path, url_path'
        .' FROM catalog_category_flat_store_1'
        . ' WHERE entity_id IN ('.$categoryId.')';

    $rowno=1;

    if ($result = $db->query($sql)){
        $tempPath = '';
        while($row = $result->fetch_object()){
            if($rowno > 1){
                $tempPath .= ',';
            }

            $path = str_replace('1/2/','',$row->path);
            $pathIds = explode('/',$path);

            //print_r($pathIds); exit;

            foreach($pathIds as $key=>$value){
                if($key!=0){
                    $tempPath .=' > ';
                }

                $tempPath .= $categoriesData[$value];
            }

            $lastval = split("/",$row->path);
            $lastval = $lastval[count($lastval)-1];
            $rowno++;
        }
        //echo $tempPath;  exit;
        return $tempPath;

    }
}


function getGoogleCategory($db, $categoryId) {
    $categoriesData = array();

    $sqlAll = 'SELECT entity_id, name, path, url_path FROM catalog_category_flat_store_1 WHERE entity_id NOT IN (\'1\',\'2\') AND name!=\'\'';
    if($resultData = $db->query($sqlAll)){
        while($rowAll = $resultData->fetch_object()){
            $categoriesData[$rowAll->entity_id] = $rowAll->name;
        }
    }

    $sql = 'SELECT entity_id,name, path, url_path'
        .' FROM catalog_category_flat_store_1'
        . ' WHERE entity_id IN ('.$categoryId.')'
        . ' LIMIT 0,1 ';

    $rowno=1;

    if ($result = $db->query($sql)){
        $tempPath = '';
        while($row = $result->fetch_object()){

            $path = str_replace('1/2/','',$row->path);
            $pathIds = explode('/',$path);

            //print_r($pathIds); exit;

            foreach($pathIds as $key=>$value){
                if($key!=0){
                    $tempPath .=' > ';
                }

                $tempPath .= $categoriesData[$value];
            }

            $lastval = split("/",$row->path);
            $lastval = $lastval[count($lastval)-1];
            $rowno++;
        }
        //echo $tempPath;  exit;
        return $tempPath;

    }
}


function getBasePrice($db,$entity_id,$attributeDetailPrice, $store_id) {
    $basePrice		= NULL;
    $tableName		= "catalog_product_entity_".$attributeDetailPrice['backend_type'];
    $attributeId    = $attributeDetailPrice['attribute_id'];

    $sqlAtt = ' SELECT CPINT.value, CPINT.store_id '
        . ' FROM '
        . ' '.$tableName . ' AS CPINT '
        . ' WHERE CPINT.attribute_id=\''.$attributeId.'\''
        . ' AND CPINT.entity_id=\''.$entity_id.'\'';

    $resultAtt = $db->query($sqlAtt);

    $storePrices = array();
    if($db->affected_rows>0) {
        while($rowAtt = $resultAtt->fetch_row()) {
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
function applySpecialPrice($data){
    $specialPrice 	 = $data['special_price'];
    $specialFromDate = strtotime($data['special_from_date']);
    $specialToDate 	 = strtotime($data['special_to_date']);
    $today			 = strtotime('today');

    // If Today in between from & To date ( sometime to date not specified so ignored if not)
    // echo "$today >= $specialFromDate && $today <= $specialToDate";
    if ($today >= $specialFromDate && (!$specialToDate || $today <= $specialToDate) && $data['special_price'] > 0) {
        // Overwite price
        $data['special_price'] = $data['special_price'];
    } else {
        $data['special_price'] = '';
    }

    // unset related data as its not being used in feed.
    //unset($data['special_price']);
    unset($data['special_from_date']);
    unset($data['special_to_date']);
    return $data;
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

    if ( 3 == $storeId && !empty($weight) ) {
        $result = round(($weight * 0.4 + 13.2) * 1.5, 2);

        $result = 'GB:::' . sprintf("%.2f", $result) . ' GBP';
    }
    return $result;
}

function getUrl($entity_id, $baseUrlHttpPath, $visibility, $db, $rowAttvalue, $storeId) {
    return $url = $baseUrlHttpPath.$rowAttvalue;
}

function getUrlKey($db, $entity_id, $store_id) {
    $sql = ' SELECT value FROM catalog_product_entity_url_key ' . ' WHERE entity_id=\''.$entity_id.'\' AND store_id=\''.$store_id.'\'';

    if ($result = $db->query($sql)){
        $row	= $result->fetch_array();
        if(count($row) > 0){
            return $row[0];
        } else {
            getUrlKey($db, $entity_id, 0);
        }
        $result->close();
    }

    return false;
}

function getStockStatusOptions($db, $attribute_code) {

    $sql = "SELECT `option_id`, `value` FROM `eav_attribute_option_value` WHERE `store_id`=0 AND `option_id` IN "
        ."(SELECT `option_id` FROM `eav_attribute_option` WHERE `attribute_id` IN "
        ."(SELECT `attribute_id` FROM `eav_attribute` WHERE `attribute_code`='{$attribute_code}')) ORDER BY `option_id`";
echo $sql.PHP_EOL;
    $options = array();
    if ($result = $db->query($sql)){
        while($row	= $result->fetch_array(MYSQLI_NUM)) {
            if(count($row)>0){
                $options[$row[0]] = $row[1];
            }
        }
        $result->close();
    }

    return empty($options) ? false : $options;
}

function getStockStatus($entity_id, $status_id, $next3day, $status_options){

    $status = array(
        'in stock',
        //'available for order',   // update Google feed
        'in stock',
        'preorder',
        'out of stock'
    );

    if (!empty($status_id) && !isset($status_options[$status_id])) {
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

function getIsNew($from_date, $to_date, $today) {
    if (!empty($from_date) && $today->compareDate($from_date) < 0 ) {
        return false;
    }
    else{
        if (!empty($to_date) && $today->compareDate($to_date) <= 0 ) {
            return true;
        }
        elseif (!empty($from_date) && empty($to_date)) {
            return true;
        }
        else{
            return false;
        }
    }
}

//Add custom fields
function getProductLabels($productId,$storeId) {
    global $categories,$excludeCategoryIds,$minCategoryLevel;

    $levelCheckOK = false;
    $categoryLabels = array();
    $tableName = 'catalog_category_product';
    $sql = "SELECT category_id FROM $tableName WHERE product_id=".$productId;
    $resource = Mage::getSingleton('core/resource');
    $conn = $resource->getConnection('core_read');
    $results = $conn->fetchAll($sql);
    $cateIds = array();
    if(count($results)){
        for($i=0; $i<count($results); $i++) {
            $cateIds[] = $results[$i]['category_id'];
        }
    }


    for($i=0; $i< count($cateIds); $i++) {
        if (in_array($cateIds[$i], $excludeCategoryIds)) {
            //prouduct belongs to a excluded category
            //echo "Product " . $product->getId(). " belongs to a excluded category ".$cateIds[$i] ."\n";
            return $categoryLabels;
            //continue 2;//next product
        }

        if (!isset( $categories[$cateIds[$i]] )) {
            continue;//skip inactive category
        }
        $category = $categories[$cateIds[$i]];

        if ($category['level'] >= $minCategoryLevel) $levelCheckOK = true;

        $categoryLabelArr = array();
        for($j=0; $j < count($category['path']); $j++) {
            if (in_array($category['path'][$j], $excludeCategoryIds)) {
                //proudct belongs to a sub category of excluded category
                //echo "Product " . $product->getId(). " belongs to ". $cateIds[$i] ." which is a sub category of excluded category ". $category['path'][$j]."\n";
                return $categoryLabels;
                //continue 2;//next product
            }
            if (!isset($categories[$category['path'][$j]])) {
                //this category belongs to an inactive category, skip to next category
                continue;
            }
            $categoryLabelArr[] = $categories[$category['path'][$j]]['name'];
        }

        if (!empty($categoryLabelArr)) {
            $categoryLabels[] = implode(' / ', $categoryLabelArr);
        }
    }
    return $categoryLabels;
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

function separateTablerateCsv($storeCode)
{
    $filePath = dirname(__FILE__).DIRECTORY_SEPARATOR.$storeCode."_tablerates.csv";
    global $rateRules;
    $existFile = false;
    if (file_exists($filePath)) {
        $existFile = true;
        $file = fopen($filePath,"r");
        $i = 0;
        while(! feof($file))
        {
            $rateRule = fgetcsv($file);
            $i++;
            if ($i>1 && $rateRule[0]!="") {
                $rateRules[]=$rateRule;
            }
        }
        fclose($file);
    }
    return $existFile;
}

function getProductShippingRate($oldWeight)
{
    global $rateRules;
    $strShipping = "";
    if (count($rateRules)) {
        foreach ($rateRules as $rateRule) {
            $shippingPrice = ($oldWeight * $rateRule[4] + $rateRule[5]) * $rateRule[6];
            $moneyCode = "AUD";
            if(substr($rateRule[0],0,2) == "UK") {
                $moneyCode = "USD";
            }
            $strShipping .= substr($rateRule[0],0,2).":".$rateRule[3].":".round($shippingPrice,2)." ".$moneyCode.", ";
        }
    }
    return rtrim($strShipping,", ");
}