<?php
/**
 * Short description for file
 * 		This files is insert Images.
 *
 * PHP versions All
 *
 * @category   PHP Coding File
 * @package
 * @author     Manish Pawar
 * @copyright  Zeon Solutions Pvt Ltd.
 * @license    As described below
 * @version    1.1.0
 * @link
 * @see        -NA-
 * @since
 * @modified   Manish Pawar [ 08-Dec-2011]
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
* Copyright 2009 Owner
*******************************************************/

/*******************************************
* FOLLOWING PARAMETER NEED TO BE CHANGED *
*******************************************/

	ini_set("memory_limit","2000M");
	ini_set('max_execution_time', 18000);

	set_time_limit(0);

	include('config.php');

	$fileToRead = 'sheets/images-delete-import.csv';

/*******************************************
* Global Configuration : Do Not Change *****
*******************************************/

	// Checking if csv file present in folder or not
	if (!file_exists($fileToRead)) {
		echo $fileToRead." was not found";
		exit;
	}

	// BOF :: Database Connection
	//$db = new mysqli ( $dbHostName, $dbUserName, $dbPassword, $dbName );
	$db = new mysqli ( '208.69.127.5', 'milandirect', 'noez@2012', 'milandirect' );
	$dbnew = new mysqli ( '208.69.127.5', 'milandirect', 'noez@2012', 'milandirect' );
	//$dbnew = new mysqli ( $dbHostName, $dbUserName, $dbPassword, $dbName );

	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
	// EOF :: Database Connection

	// Read All File Data
	$handle			= fopen($fileToRead, "r");

/*******************************************
* FUNCTIONALITY CODE STARTS BELOW *
*******************************************/

	$images_data = array();
	$rowCount =0;

	// Base Image
	$baseImageDetails = getAttributeDetails($db, 'image');
	$baseImageAttributeID = $baseImageDetails['attribute_id'];
	$baseImageAttributeTable = $baseImageDetails['backend_type'];

	// Base Image Label
	$baseImageLabelDetails = getAttributeDetails($db, 'image_label');
	$baseImageLabelAttributeID = $baseImageLabelDetails['attribute_id'];
	$baseImageLabelAttributeTable = $baseImageLabelDetails['backend_type'];

	// Small Image
	$smallImageDetails = getAttributeDetails($db, 'small_image');
	$smallImageAttributeID = $smallImageDetails['attribute_id'];
	$smallImageAttributeTable = $smallImageDetails['backend_type'];

	// Small Image Label
	$smallImageLabelDetails = getAttributeDetails($db, 'small_image_label');
	$smallImageLabelAttributeID = $smallImageLabelDetails['attribute_id'];
	$smallImageLabelAttributeTable = $smallImageLabelDetails['backend_type'];

	// thumb Image
	$thumbImageDetails = getAttributeDetails($db, 'thumbnail');
	$thumbImageAttributeID = $thumbImageDetails['attribute_id'];
	$thumbImageAttributeTable = $thumbImageDetails['backend_type'];

	// Thumb Image Label
	$thumbImageLabelDetails = getAttributeDetails($db, 'thumbnail_label');
	$thumbImageLabelAttributeID = $thumbImageLabelDetails['attribute_id'];
	$thumbImageLabelAttributeTable = $thumbImageLabelDetails['backend_type'];

	// Gallery Image
	$galleryImageDetails = getAttributeDetails($db, 'media_gallery');
	$galleryImageAttributeID = $galleryImageDetails['attribute_id'];
	$galleryImageAttributeTable = $galleryImageDetails['backend_type'];

	// Product Name
	$productNameDetails = getAttributeDetails($db, 'name');
	$productNameAttributeID = $productNameDetails['attribute_id'];
	$productNameAttributeTable = $productNameDetails['backend_type'];

	echo '<table style="font-family: Verdana; font-size: 11px" border="1">';
	echo '<tr><H1>Import Product Image</H2></tr>';
	echo '<tr>';
	echo "	<td><strong>Row No.</strong></td>
			<td><strong>SKU</strong></td>
			<td><strong>Label</strong></td>
			<td><strong>Base Image</strong></td>
			<td><strong>Small Image</strong></td>
			<td><strong>Thumb Image</strong></td>
			<td><strong>Large Image</strong></td>
		 </tr>";

	while (($data = fgetcsv($handle, 4086, ",")) !== FALSE) {

		if($rowCount != 0) {
			$sku			= trim($data[0]);
			$info 			= array();
			$info 			= _getProductInfo($dbnew, $sku, $productNameAttributeID);
			$entityID 		= $info[0];

			if ( $data[1] == '' ) {
				$label			= $db->real_escape_string($info[1]);
			}
			else {
				$label			= trim($db->real_escape_string($data[1]));
			}

			$baseImage   	= trim($db->real_escape_string($data[2]));
			$smallImage  	= trim($db->real_escape_string($data[3]));
			$thumbImage  	= trim($db->real_escape_string($data[4]));
			$arrGallery		= array();

			for($i=5;$i<=55;$i++) {
				$arrGallery[]	= @trim($data[$i]);
			}

			$rowCount+=1;
			echo "<tr><td>".$rowCount."</td>";
			echo "<td><strong>".$sku."</strong></td>";
			echo "<td>".$label."</td>";

			if($entityID) {
				$sql = 'DELETE FROM catalog_product_entity_varchar '
						. ' WHERE '
						. ' attribute_id IN (\''.$baseImageAttributeID.'\', \''.$smallImageAttributeID.'\', \''.$thumbImageAttributeID.'\', \''.$baseImageLabelAttributeID.'\', \''.$smallImageLabelAttributeID.'\', \''.$thumbImageLabelAttributeID.'\')'
						. ' AND '
						. ' entity_id =\''.$entityID.'\'';

				if($db->query($sql)){
					if($db->affected_rows>0){
						echo "<td>DELETED=>CPEV_IMG</td>";
					}
					else {
						echo "<td>NOT DELETED=>CPEV_IMG</td>";
					}
				}

				$sql = 'DELETE FROM catalog_product_entity_media_gallery '
						. ' WHERE '
						. ' attribute_id IN (\''.$baseImageAttributeID.'\',\''.$smallImageAttributeID.'\',\''.$thumbImageAttributeID.'\',\''.$galleryImageAttributeID.'\')'
						. ' AND '
						. ' entity_id =\''.$entityID.'\'';

				if($db->query($sql)){
					if($db->affected_rows>0){
						echo "<td>DELETED=>CPE_MG</td>";
					}
					else {
						echo "<td>Not DELETED=>CPE_MG</td>";
					}
				}

				//Base Image
				if($baseImage !=''){
					// Image
					$sql = 'INSERT INTO catalog_product_entity_varchar '
							. ' SET '
							. ' `entity_type_id` = 4 '
							. ','. ' `attribute_id`=\''.$baseImageAttributeID.'\''
							. ','. ' `store_id` = 0 '
							. ','. ' `entity_id`=\''.$entityID.'\''
							. ','. ' `value`=\''.$baseImage.'\'';
					$db->query($sql);
					// Label
					$sql = 'INSERT INTO catalog_product_entity_varchar '
							. ' SET '
							. ' `entity_type_id` = 4 '
							. ','. ' `attribute_id`=\''.$baseImageLabelAttributeID.'\''
							. ','. ' `store_id` = 0 '
							. ','. ' `entity_id`=\''.$entityID.'\''
							. ','. ' `value`=\''.$label.'\'';
					$db->query($sql);

					// Galery Entry
					$sql = 'INSERT INTO catalog_product_entity_media_gallery '
							. ' SET '
							. ' `attribute_id`=\''.$galleryImageAttributeID.'\''
							. ','. ' `entity_id`=\''.$entityID.'\''
							. ','. ' `value`=\''.$baseImage.'\'';
					$db->query($sql);
					$value_id = $db->insert_id;
					$sql = 'INSERT INTO catalog_product_entity_media_gallery_value '
							. ' SET '
							. ' `value_id`=\''.$value_id.'\''
							. ','. ' `store_id` = 0 '
							. ','. ' `label`=\''.$label.'\''
							. ','. ' `position` = 1 '
							. ','. ' `disabled` = 1 ';
					$db->query($sql);

					echo "<td>Base=>".$baseImage."</td>";
				}

				//Small Image
				if($smallImage !=''){
					// Image
					$sql = 'INSERT INTO catalog_product_entity_varchar '
							. ' SET '
							. ' `entity_type_id` = 4 '
							. ','. ' `attribute_id`=\''.$smallImageAttributeID.'\''
							. ','. ' `store_id` = 0 '
							. ','. ' `entity_id`=\''.$entityID.'\''
							. ','. ' `value`=\''.$smallImage.'\'';
					$db->query($sql);

					// Label
					$sql = 'INSERT INTO catalog_product_entity_varchar '
							. ' SET '
							. ' `entity_type_id` = 4 '
							. ','. ' `attribute_id`=\''.$smallImageLabelAttributeID.'\''
							. ','. ' `store_id` = 0 '
							. ','. ' `entity_id`=\''.$entityID.'\''
							. ','. ' `value`=\''.$label.'\'';
					$db->query($sql);

					// Gallary Image
					$sql = 'INSERT INTO catalog_product_entity_media_gallery '
							. ' SET '
							. ' `attribute_id`=\''.$galleryImageAttributeID.'\''
							. ','. ' `entity_id`=\''.$entityID.'\''
							. ','. ' `value`=\''.$smallImage.'\'';
					$db->query($sql);
					$value_id = $db->insert_id;
					$sql = 'INSERT INTO catalog_product_entity_media_gallery_value '
							. ' SET '
							. ' `value_id`=\''.$value_id.'\''
							. ','. ' `store_id` = 0 '
							. ','. ' `label`=\''.$label.'\''
							. ','. ' `position` = 2 '
							. ','. ' `disabled` = 1 ';
					$db->query($sql);

					echo "<td>Small=>".$smallImage."</td>";
				}

				//Thumb Image
				if($thumbImage !=''){
					// Image
					$sql = 'INSERT INTO catalog_product_entity_varchar '
							. ' SET '
							. ' `entity_type_id` = 4 '
							. ','. ' `attribute_id`=\''.$thumbImageAttributeID.'\''
							. ','. ' `store_id` = 0 '
							. ','. ' `entity_id`=\''.$entityID.'\''
							. ','. ' `value`=\''.$thumbImage.'\'';
					$db->query($sql);
					// Label
					$sql = 'INSERT INTO catalog_product_entity_varchar '
							. ' SET '
							. ' `entity_type_id` = 4 '
							. ','. ' `attribute_id`=\''.$thumbImageLabelAttributeID.'\''
							. ','. ' `store_id` = 0 '
							. ','. ' `entity_id`=\''.$entityID.'\''
							. ','. ' `value`=\''.$label.'\'';
					$db->query($sql);
					// Gallary
					$sql = 'INSERT INTO catalog_product_entity_media_gallery '
							. ' SET '
							. ' `attribute_id`=\''.$galleryImageAttributeID.'\''
							. ','. ' `entity_id`=\''.$entityID.'\''
							. ','. ' `value`=\''.$thumbImage.'\'';
					$db->query($sql);
					$value_id = $db->insert_id;
					$sql = 'INSERT INTO catalog_product_entity_media_gallery_value '
							. ' SET '
							. ' `value_id`=\''.$value_id.'\''
							. ','. ' `store_id` = 0 '
							. ','. ' `label`=\''.$label.'\''
							. ','. ' `position` = 3 '
							. ','. ' `disabled` = 1 ';
					$db->query($sql);

					echo "<td>Thumb=>".$thumbImage."</td>";
				}

				//Gallery Image
				$position = 4;
				if(is_array($arrGallery) && count($arrGallery) > 0 ){
					foreach($arrGallery as $key=>$value){

						if($value==''){
							continue;
						}

						//$label	= str_replace('.jpg','', $value);

						$disabled = 0;

						$sql = 'INSERT INTO catalog_product_entity_media_gallery '
								. ' SET '
								. ' `attribute_id`=\''.$galleryImageAttributeID.'\''
								. ','. ' `entity_id`=\''.$entityID.'\''
								. ','. ' `value`=\''.$value.'\'';
						$db->query($sql);
						$value_id = $db->insert_id;
						$sql = 'INSERT INTO catalog_product_entity_media_gallery_value '
								. ' SET '
								. ' `value_id`=\''.$value_id.'\''
								. ','. ' `store_id` = 0 '
								. ','. ' `label`=\''.$label.'\''
								. ','. ' `position` = \''.$position.'\''
								. ','. ' `disabled` = \''.$disabled.'\'';
						$db->query($sql);
						$position++;
						echo "<td>Gallery=>".$value."</td>";
					}
				}

			}
			else {
				echo "<td>SKU Not Found</td>";
			}
			echo "</tr>";
		}
		else {
			$rowCount+=1;
		}
	}
	//EOF :: While Looop
	echo '</table>';

/*******************************************
* COMMAN FUNCTION STARTS BELOW *
*******************************************/

	/**
     * Return loaded product instance
     *
     * @param  int|string $productId (SKU or ID)
     * @param  int|string $store
     * @return Mage_Catalog_Model_Product
     */
    function _getProductInfo (&$dbnew, $sku, $attribute_id)
    {
		$sku	= $dbnew->real_escape_string($sku);
		$result = false;
	    $sql = 'SELECT CPE.entity_id, CPEV.value '
				. ' FROM catalog_product_entity AS CPE, catalog_product_entity_varchar AS CPEV'
				. ' WHERE CPE.entity_id = CPEV.entity_id'
				. ' AND CPE.sku=\''.$sku.'\''
				. ' AND CPEV.attribute_id=\''.$attribute_id.'\'';

		if ($qresult = $dbnew->query($sql)){
			$row	= $qresult->fetch_row();
			if(count($row)>0){
				$result = $row;
			}
			$qresult->close();
		}
        return $result;
    }

	/**
     * Used to Fetch the Attribute ID, Type
	 *
	 * @param  string	$attributeKey
     */
	function getAttributeDetails($db, $attributeKey, $type_id='4')
	{
		$sql = ' SELECT attribute_id, backend_type FROM eav_attribute '
				. ' WHERE entity_type_id=\''.$type_id.'\' '
				. ' AND attribute_code=\''.$attributeKey.'\'';

		if ($result = $db->query($sql)){
			$row	= $result->fetch_array();
			if(count($row)>0){

				return $row;
			}
			$result->close();
		}

		return false;
	}
?>