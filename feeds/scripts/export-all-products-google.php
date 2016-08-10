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

	include('config.php');
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
	foreach ($websites as $_website) {
		$websiteId = $_website->getId();
		foreach ($_website->getStores() as $_store) {
			echo '<br>Web ID:'.$websiteId.' Store Code:'.$_store->getCode().' Store URL:'.$_store->getBaseUrl().'<br>';
			$baseUrlHttpPath = $_store->getBaseUrl();
			$storeCode = $_store->getCode();
			$storeId = $_store->getID();
			break;
		}


	$date = date("j-n-Y");
	$file_to_write = '/var/www/milandirect/feeds/google_products_'.$storeCode.'.txt';
	//$WEB_PATH = "http://milandirect/";
	@unlink($file_to_write);

	/*******************************************
	* FUNCTIONALITY CODE STARTS BELOW *
	*******************************************/
	//$data = array("S.No.", "Sku", "Attribute Set", "Product Type", "Visibility", "Status", "Name", "Price", "Link", "Image Link", "Description", "Qty");
	$data = array("id","mpn","product_type","condition","shipping","google_product_category","brand","availability","title", "link", "image link", "price","description","weight");
	//$data = array("Sku","Name", "Description","Product Type","Link", "Image Link");

	$arrAttribute[] = 'name';
	$arrAttribute[] = 'url_path';
	$arrAttribute[] = 'image';
	$arrAttribute[] = 'price';
	$arrAttribute[] = 'description';
	$arrAttribute[] = 'weight';
	$arrAttribute[] = 'special_price';
	$arrAttribute[] = 'special_from_date';
	$arrAttribute[] = 'special_to_date';
	
	$visibilityAttributeDetail 		= getAttributeDetails($db, 'visibility');
	$statusAttributeDetail 	   		= getAttributeDetails($db, 'status');
	$attributeDetailPrice 	   		= getAttributeDetails($db, 'price');
	$attributeDetailSpecialPrice 	= getAttributeDetails($db, 'special_price');
	
	$sql = 'SELECT '
. ' CPE_SKU.sku '
. ', CPE_SKU.entity_id'
. ', CPE_SKU.attribute_set_id '
. ', CPE_SKU.type_id '
. ', CPEI_VISIBILITY.value AS visibility'
. ', CPEI_STATUS.value AS status'
. ', CPEI_WEBSITE.website_id AS website_id'
. ' FROM '
. ' catalog_product_entity AS CPE_SKU'
. ' LEFT JOIN catalog_product_entity_int AS CPEI_STATUS ON CPE_SKU.entity_id=CPEI_STATUS.entity_id'
. ' LEFT JOIN catalog_product_entity_int AS CPEI_VISIBILITY ON CPE_SKU.entity_id=CPEI_VISIBILITY.entity_id'
. ' LEFT JOIN catalog_product_entity_text AS CPEI_DESCRIPTION ON CPE_SKU.entity_id=CPEI_DESCRIPTION.entity_id'
. ' LEFT JOIN catalog_product_website AS CPEI_WEBSITE ON CPE_SKU.entity_id=CPEI_WEBSITE.product_id'
. ' WHERE CPEI_VISIBILITY.attribute_id=\''.$visibilityAttributeDetail['attribute_id'].'\' '
. ' AND CPEI_STATUS.attribute_id=\''.$statusAttributeDetail['attribute_id'].'\' '
. ' AND CPEI_STATUS.value=\'1\''
. ' AND CPEI_VISIBILITY.value=\'4\''
. ' AND CPEI_DESCRIPTION.value !=\'\''
. ' AND CPEI_WEBSITE.website_id='.$websiteId
. ' AND CPE_SKU.sku !=\'\''
. ' GROUP BY entity_id '
. ' ORDER BY type_id '
;

	$result = $dbNew->query($sql);
	if($result){
		if($dbNew->affected_rows>0){
			$counter = 0;
			importProducts($data, $file_to_write);

			$arrParent = NULL;
			$arrParentStatus = NULL;
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
				$manufacturer = "";

				$brand = "Milan Direct";
				$availability = "in stock";
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
				$data['shipping'] = $shipping;
				//$data[] = $location;
				//$data[] = $manufacturer;
				$data['google_product_category'] = $google_product_category;
				$data['brand'] = $brand;
				$data['availability'] = $availability;

				$resultAtt = '';
				$rowAtt = '';

				foreach($arrAttribute AS $akey=>$avalue) {
					$attributeDetail = getAttributeDetails($db, $avalue);

					$attributeValue = NULL;
					$parent_id = NULL;

					if ( is_array($attributeDetail) && count($attributeDetail) > 0 ) {

						$tableName		= "catalog_product_entity_".$attributeDetail['backend_type'];
						$attributeId    = $attributeDetail['attribute_id'];

						if( $attributeDetail['backend_type'] == 'int' ) {
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
									$attributeValue = str_replace("&reg;", "®", $attributeValue);
									$attributeValue = str_replace("&trade;", "™", $attributeValue);
									$attributeValue = str_replace("&#150;", "–", $attributeValue);
									$attributeValue = str_replace("&#8482;", "™", $attributeValue);
									$attributeValue = str_replace("&amp;", "&", $attributeValue);
									$attributeValue = str_replace("&#160;", " ", $attributeValue);
									$attributeValue = str_replace("â€š", " ,", $attributeValue);
									$attributeValue = str_replace("â€¢", "•", $attributeValue);
									$attributeValue = str_replace("â„¢", "™", $attributeValue);
									$attributeValue = str_replace("–¢", "•", $attributeValue);
									$attributeValue = str_replace("Â", " ", $attributeValue);
									$attributeValue = preg_replace('/\n/i', ' ', $attributeValue);
								}elseif($avalue == 'weight'){
								   $attributeValue = $rowAtt['value'];
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
										$productprice = getBasePrice($db,$entity_id,$attributeDetailPrice, $storeId);
									}
									else{
										$productprice = '';
										$productprice = getBasePrice($db,$entity_id,$attributeDetailSpecialPrice, $storeId);
									}
									$attributeValue  = $productprice;

								}
								elseif( $avalue == 'image' ) {

									$attributeValue = "media/catalog/product/".$rowAtt['value'];
									$attributeValue = str_replace('//', '/', $attributeValue);
									//$attributeValue = $baseUrlHttpPath.$attributeValue;
									$imageName = $mediaFolderPath.'/catalog/product/'.$rowAtt['value'];
									if ( file_exists($imageName)) {
										 $attributeValue = $baseUrlHttpPath.$attributeValue;;
									} else {
										$attributeValue = $baseUrlHttpPath.'media/catalog/product/placeholder/default/no-image-262-262.jpg';
									}
								}
								elseif( $avalue == 'url_path' ) {

									// Retrive Child Parent URL
									if( $visibility != '4' ) {
										 //Fetch Parent ID
										$parent_id = getParentId($db, $entity_id);
										if($parent_id) {
											$attributeValue = $arrParent[$parent_id];
											if( $attributeValue ) {
												$attributeValue = $attributeValue.'?sku='.$sku;						$url = true;
											}
										}
									}
									else {
										$attributeValue = $baseUrlHttpPath.$rowAtt['value'];
										$arrParent[$entity_id] = $attributeValue;
										$url = true;
									}

									if ( !$url ) {
										$attributeValue = $baseUrlHttpPath.$rowAtt['value'];
									}
								}
								else {
									$attributeValue = $rowAtt['value'];
								}
							}
							else{
								if($avalue == 'price') {
									$productprice = '';
									$productprice = getBasePrice($db,$entity_id,$attributeDetailPrice, $storeId);
									$attributeValue  = $productprice;
								}
							}
						}
					}
					$data[$avalue] = $attributeValue;
				}

				
					$counter+=1;
					$qty = getProductQty($db, $entity_id);
					if( $qty <= 0 ) {
						$qty = '99999999';
						$stockstatus= 'in stock';
					}
					//$data[] = $stockstatus;
					//$data[] = '~~';
					
					$data = applySpecialPrice($data);
					importProducts($data, $file_to_write);
			}

			echo $counter." Product Found";

			// While End
		}
		else {
			echo "No Product Found";
		}
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
//print_r($categoryId);

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
			$data['price'] = $data['special_price'];
		}
		
		// unset related data as its not being used in feed.
		unset($data['special_price']);
		unset($data['special_from_date']);
		unset($data['special_to_date']);
		return $data;
	}

?>