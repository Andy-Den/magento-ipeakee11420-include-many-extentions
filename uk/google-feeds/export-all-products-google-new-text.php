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
	
	$db = new mysqli ( $dbHostName, $dbUserName, $dbPassword, $dbName );
	$dbNew = new mysqli ( $dbHostName, $dbUserName, $dbPassword, $dbName );
		
	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
	$date = date("j-n-Y");
	$file_to_write = 'sheets/milan'.$date.'.txt';
	$WEB_PATH = "http://208.69.127.5/milandirect/";
	//$WEB_PATH = "http://setonuk.zeondemo.com";
	
	@unlink($file_to_write);

	/*******************************************
	* FUNCTIONALITY CODE STARTS BELOW *
	*******************************************/
	//$data = array("S.No.", "Sku", "Attribute Set", "Product Type", "Visibility", "Status", "Name", "Price", "Link", "Image Link", "Description", "Qty");
	$data = array("id","mpn","product_type","condition","shipping","location","make","google_product_category","brand","availability","title", "link", "image link", "price","description","weight");
	//$data = array("Sku","Name", "Description","Product Type","Link", "Image Link");
	
	$arrAttribute[] = 'name';
	$arrAttribute[] = 'url_path';
	$arrAttribute[] = 'image';
	$arrAttribute[] = 'display_price';
	$arrAttribute[] = 'description';
	$arrAttribute[] = 'weight';

	$visibilityAttributeDetail = getAttributeDetails($db, 'visibility');
	$statusAttributeDetail = getAttributeDetails($db, 'status');
	$attributeDetailPrice = getAttributeDetails($db, 'price');
			
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
		. ' AND CPEI_WEBSITE.website_id=\'2\''
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
				$make = "";
				
				$brand = "Seton";
				$availability = "in stock";
				$value = NULL;
				$data = "";
				$url = false;

				$data[] = $entity_id;
				$data[] = $sku;
				//$data[] = $visibility;
				//$data[] = $type_id;

				$categoruUrl = getCategoryProduct($db, $categoryId);
				
				//$google_product_category = getGoogleCategory($db, $categoryId);
				
				$google_product_category = "Business & Industrial";

				$data[] = $categoruUrl;
				$data[] = $condition;
				$data[] = $shipping;
				$data[] = $location;
				$data[] = $make;
				$data[] = $google_product_category;
				$data[] = $brand;
				$data[] = $availability;
	
				//$data[] = $attribute_set_id;
				//$data[] = $type_id;
				//$data[] = $row['visibility'];
				//$data[] = $row['status'];
						
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
									$attributeValue = strip_tags($rowAtt['value']);
									$attributeValue = str_replace("    ", " ", $attributeValue);
									$attributeValue = str_replace("  ", " ", $attributeValue);
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
								elseif($avalue == 'display_price') {
									if($rowAtt['value'] != null && $visibility !='3'){
										$productprice = '';
										$productprice = $rowAtt['value'];
									}
									else{
										$productprice = '';
										$productprice = getBasePrice($db,$entity_id,$attributeDetailPrice);
									}
									$attributeValue  = $productprice;
									
								}
								elseif( $avalue == 'image' ) {

									$attributeValue = "media/catalog/product/".$rowAtt['value'];
									$attributeValue = str_replace('//', '/', $attributeValue);
									$attributeValue = $WEB_PATH.$attributeValue;
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
										$attributeValue = $WEB_PATH.$rowAtt['value'];
										$arrParent[$entity_id] = $attributeValue;
										$url = true;
									}
									
									if ( !$url ) {
										$attributeValue = $WEB_PATH.$rowAtt['value'];
									}
								}
								else {
									$attributeValue = $rowAtt['value'];
								}
							}
							else{
								if($avalue == 'display_price') {
									$productprice = '';
									$productprice = getBasePrice($db,$entity_id,$attributeDetailPrice);
									$attributeValue  = $productprice;
								}
							}
						}
					}
					$data[] = $attributeValue;
				}
									
					$counter+=1;
					$qty = getProductQty($db, $entity_id);
					if( $qty <= 0 ) {
						$qty = '99999999';
						$stockstatus= 'in stock';
					}
					//$data[] = $stockstatus;
					//$data[] = '~~';
					importProducts($data, $file_to_write);
				
			}
		
			echo $counter." Product Found";
			
			// While End
		}
		else {
			echo "No Product Found";
		}
	}

	/**
	* BOF :: Function to Log Queries
	* @param String query
	*/
	function importProducts($data, $filename)
	{
	    
		if (!file_exists($filename)) {
			$fh	= fopen($filename,"a+");
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

	
	function getBasePrice($db,$entity_id,$attributeDetailPrice) {
	
	$basePrice		= NULL;
	$tableName		= "catalog_product_entity_".$attributeDetailPrice['backend_type']; 
	$attributeId    = $attributeDetailPrice['attribute_id'];

	$sqlAtt = ' SELECT CPINT.value '
			. ' FROM '
			. ' '.$tableName . ' AS CPINT '
			. ' WHERE CPINT.attribute_id=\''.$attributeId.'\''
			. ' AND CPINT.entity_id=\''.$entity_id.'\''; 

	$resultAtt = $db->query($sqlAtt);
	
	  if($db->affected_rows>0) {
			$rowAtt = $resultAtt->fetch_row();
			if($rowAtt[0] != '') {
				$basePrice		= $rowAtt[0];
			}
	  }
	  
	  return $basePrice;
}

?>