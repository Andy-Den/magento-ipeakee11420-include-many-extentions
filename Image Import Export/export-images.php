<?php
/**
 * Short description for file
 * 		This files is used to export Product Image details 
 *
 * PHP versions All
 *
 * @category   PHP Coding File
 * @package    
 * @author     Manish Pawar / Nitesh
 * @copyright  Zeon Solutions Pvt Ltd.
 * @license    As described below
 * @version    1.1.0
 * @link       
 * @see        -NA-
 * @since      10-Feb-2010
 * @modified   Manish Pawar [08-Dec-2011] 
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
	ini_set('max_execution_time', 18000);
		
	set_time_limit(0);
	
	include('config.php');
	
	error_reporting(0);

/*******************************************
* Global Configuration : Do Not Change *****
*******************************************/
	
	// BOF :: Database Connection
	$db = new mysqli ( $dbHostName, $dbUserName, $dbPassword, $dbName );
		
	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
	// EOF :: Database Connection
	
	// Status
	$attributeDetailStatus	= getAttributeDetails($db, 'status');
	$attributeIdStatus		= $attributeDetailStatus['attribute_id'];
	
	// Visibility
	$attributeDetailVisibility	= getAttributeDetails($db, 'visibility');
	$attributeIdVisibility		= $attributeDetailVisibility['attribute_id'];

	// Base Image
	$attributeDetailImage	= getAttributeDetails($db, 'image');
	$attributeIdImage		= $attributeDetailImage['attribute_id'];

	// Small Image
	$attributeDetailSmallImage	= getAttributeDetails($db, 'small_image');
	$attributeIdSmallImage		= $attributeDetailSmallImage['attribute_id'];

	// Thumb Image
	$attributeDetailThumbnail	= getAttributeDetails($db, 'thumbnail');
	$attributeIdThumbnail		= $attributeDetailThumbnail['attribute_id'];

	// Gallery Image
	$attributeDetailMediaGallery	= getAttributeDetails($db, 'media_gallery');
	$attributeIdMediaGallery		= $attributeDetailMediaGallery['attribute_id'];


	$resultsku = $db->query("SELECT count(*) FROM catalog_product_entity");
	$rowRec = $resultsku->fetch_row();
		
	$attribute_string = getAttributeSet($db);
	
?>
<html>
<head>
<title> Image Validation / Export Script</title>
</head>
<body>
<center>
	<form name="imgValid" action="" method="post">
	<table style="font-family: Verdana; font-size: 11px" border="1">
		<tr><td colspan="5"><h1>Image Validation / Export Script</h1></td></tr>
		<tr><td colspan="5"><h3>Database connected and total product count..<font color="red"><?php echo $rowRec[0];?></font></h3></td></tr>
		<tr>
			<td width="20%"><h3>Start Range : <input type="text" name="start" value="<?php echo $_POST['start'];?>"/></h3></td>
			<td width="20%"><h3>End Range : <input type="text" name="end" value="<?php echo $_POST['end'];?>"/></h3></td>
			<td width="15%"><h3>Parent Report : <input type="checkbox" name="parent" value="parent" /><?php echo $_POST['parent'];?></h3></td>
			<td width="25%"><h3>Attribute Set : <select name='attribute_set'>
												<option value=''>Select Attribute Set</option>
												<option value='All'>All Attributes</option>
												<?php echo $attribute_string; ?>
											</select>
							</h3>
			</td>
			<td width="20%"><input type="Submit" name="Submit" value="Submit"/></td>
		</tr>
	</table>
	</form>
</center>
</body>
</html>	
<?php

if ( isset($_POST['start']) && is_numeric($_POST['start']) && isset($_POST['end']) && is_numeric($_POST['end']) )
{
	$start = $_POST['start'];
	$end = $_POST['end'];
	$_POST['start']=NULL;	
	$_POST['end']=NULL;
		
	$imageFolder = $_SERVER["DOCUMENT_ROOT"]."/media/catalog/product/";
	
	
	// Draw up table and headers
	echo '<table style="font-family: Verdana; font-size: 11px" border="1" width="100%">';
	echo '<tr><td width="10px"><strong>Row No.</strong></td><td width="25px"><strong>SKU</strong></td><td width="25px"><strong>TYPE</strong></td>
	<td width="50px"><strong>Base Image</strong></td><td width="50px"><strong>Small Image</td>
	<td width="50px"><strong>Thumbnail Image</strong></td>
	<td width="50px"><strong>Gallery1 Image</strong></td><td width="50px"><strong>Gallery2 Image</strong></td>
	<td width="50px"><strong>Gallery3 Image</strong></td><td width="50px"><strong>Gallery4 Image</strong></td></tr>';
	
	$rowno=0;
	$arr_image = array();
	$temp_image = '';
	$sqlCondition = '';
	
	if ( $_POST['attribute_set'] == '' || $_POST['attribute_set'] == 'All' ) {
			
	}
	else {
		$sqlCondition .= " AND CPE.attribute_set_id='".$_POST['attribute_set']."'";
	}
		
	if ( $_POST['parent'] == 'parent' ) {
		$sql = 'SELECT CPE.sku, CPE.entity_id, CPE.type_id '
				. ' FROM '
				. ' catalog_product_entity AS CPE'
				. ' ,catalog_product_entity_int AS CPEI '
				. ' WHERE '
				. ' CPE.entity_id >='.$start
				. ' AND CPE.entity_id <='.$end
				. ' AND CPE.entity_id = CPEI.entity_id '
				. ' AND CPEI.attribute_id="'.$attributeIdVisibility.'"'
				. ' AND CPEI.value=4'
				;
		
	}
	else {
		$sql = 'SELECT CPE.sku, CPE.entity_id, CPE.type_id '
				. ' FROM '
				. ' catalog_product_entity AS CPE'
				. ' WHERE '
				. ' CPE.entity_id >='.$start
				. ' AND CPE.entity_id <='.$end
				;	
		$_POST['parent'] = 'All';			
	}
	$sql .= $sqlCondition;
	
	$resultsku = $db->query($sql);
	
	$arr_image[] = "Master Number,Product Name,Base Image,Small Image,Thumb Image,Large Image,Gallery Image 2,Gallery Image 3,Gallery Image 4,Gallery Image 5";
	
	while ($rowRec = $resultsku->fetch_row()) 
	{
		$imageArray = array();
		$rowno++;
		
		echo "<tr><td>$rowno</td>"."<td>".$rowRec[0]."</td>"."<td>".$rowRec[2]."</td>";
		$temp_image .= $rowRec[0].",";
		
		// base image
		$baseImage='';
		$sqlBI = 'SELECT value '
				. ' FROM '
				. ' catalog_product_entity_varchar '
				. ' WHERE '
				. ' attribute_id="'.$attributeIdImage.'"' 
				. ' AND entity_id = "'.$rowRec[1].'"';
		
		$resultvalue = $db->query($sqlBI);
		while ($row = $resultvalue->fetch_row()) 
		{
			$baseImage = $row[0];
		}
		$imageArray[] = $baseImage;
		$imagePath = $imageFolder.$baseImage;
		if (file_exists($imagePath) && $baseImage!='') 
		{
			echo "<td>$baseImage - Exist</td>";
			$baseImage = str_replace(",", "", $baseImage);
			$temp_image .= ",".$baseImage;
		} 
		else 
		{
			echo "<td><font color='red'>$baseImage - Not Exist</font></td>";
			$baseImage = str_replace(",", "", $baseImage);
			$temp_image .= ",".$baseImage;
		}
			
		
		// small image
		$smallImage='';
		$sqlSI = 'SELECT value '
				. ' FROM '
				. ' catalog_product_entity_varchar '
				. ' WHERE '
				. ' attribute_id="'.$attributeIdSmallImage.'"' 
				. ' AND entity_id = "'.$rowRec[1].'"';
		$resultvalue = $db->query($sqlSI);
		while ($row = $resultvalue->fetch_row()) 
		{
			$smallImage = $row[0];
		}
		$imageArray[] = $smallImage;
		$imagePath = $imageFolder.$smallImage;
		if (file_exists($imagePath) && $smallImage!='') 
		{
			echo "<td>$smallImage - Exist</td>";
			$smallImage = str_replace(",", "", $smallImage);
			$temp_image .= ",".$smallImage;
		} 
		else 
		{
			echo "<td><font color='red'>$smallImage - Not Exist</font></td>";
			$smallImage = str_replace(",", "", $smallImage);
			$temp_image .= ",".$smallImage;
		}
				
		// thumbnail image
		$thumbnailImage='';
		$sqlTI = 'SELECT value '
				. ' FROM '
				. ' catalog_product_entity_varchar '
				. ' WHERE '
				. ' attribute_id="'.$attributeIdThumbnail.'"' 
				. ' AND entity_id = "'.$rowRec[1].'"';
		$resultvalue = $db->query($sqlTI);
		while ($row = $resultvalue->fetch_row()) 
		{
			$thumbnailImage = $row[0];
		}
		$imageArray[] = $thumbnailImage;
		$imagePath = $imageFolder.$thumbnailImage;
		if (file_exists($imagePath) && $thumbnailImage!='') 
		{
			echo "<td>$thumbnailImage - Exist</td>";
			$thumbnailImage = str_replace(",", "", $thumbnailImage);
			$temp_image .= ",".$thumbnailImage;
		} 
		else 
		{
			echo "<td><font color='red'>$thumbnailImage - Not Exist</font></td>";
			$thumbnailImage = str_replace(",", "", $thumbnailImage);
			$temp_image .= ",".$thumbnailImage;
		}
				
		// gallery image
		$galleryImage='';
		$sqlGI = 'SELECT value '
				. ' FROM '
				. ' catalog_product_entity_media_gallery '
				. ' WHERE '
				. ' attribute_id="'.$attributeIdMediaGallery.'"' 
				. ' AND entity_id = "'.$rowRec[1].'"';
				
		$resultvalue = $db->query($sqlGI);		
		while ($row = $resultvalue->fetch_row()) 
		{
			$galleryImage='';
			$galleryImage = $row[0];
			if (!in_array ($galleryImage, $imageArray ,true))
			{
				$imageArray[] = $galleryImage;
				$imagePath = $imageFolder.$galleryImage;
				if (file_exists($imagePath) && $galleryImage!='')  
				{
					echo "<td>$galleryImage - Exist</td>";
					$galleryImage = str_replace(",", "", $galleryImage);
					$temp_image .= ",".$galleryImage;
				} 
				else 
				{
					echo "<td><font color='red'>$galleryImage - Not Exist</font></td>";
					$galleryImage = str_replace(",", "", $galleryImage);
					$temp_image .= ",".$galleryImage;
				}
				//$temp_image .= ",".$galleryImage;
			}
		}
		echo "</tr>";
		$arr_image[] = "$temp_image";
		$temp_image = '';
	}
	
	$attribute_set_name = getAttributeSetName($db, $_POST['attribute_set']);

	$filename = "sheets/E-".$attribute_set_name.'-'.$_POST['attribute_set']."-".$_POST['parent']."-Images-".date('Y-m-d').".csv";
	
	$fh	= fopen($filename,"w+");
	chmod($filename,0777);
	if(is_array($arr_image) & count($arr_image)>0){
		foreach ($arr_image as $images) {
		   fputcsv($fh, split(',', $images));
		}
	}
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
 * Used to Fetch the Attribute ID, Type
 *
 * @param  string	$attributeKey	Attribute Key
 */
function getAttributeSet($db)
{
	$sql = ' SELECT attribute_set_id, attribute_set_name FROM eav_attribute_set '
			. ' WHERE entity_type_id=\'4\' ORDER BY attribute_set_name '
			;
	
	$string = NULL;
	if ( $result = $db->query($sql) ){
		if ( $db->affected_rows>0 ){ 
			while ( $row = $result->fetch_row() ) {
				$string .= "<option value='".$row[0]."' >".$row[1]. "</option>";
			}
		}
	}
	return $string;
}

/**
 * Used to Fetch the Attribute Set Name
 *
 * @param  string	$db	
 * @param  int Attribute Set ID
 */
function getAttributeSetName($db, $attributeSetId ) {
	
	$sql = ' SELECT attribute_set_name '
		 . ' FROM eav_attribute_set '
		 . ' WHERE '
		 . ' attribute_set_id=\''.$attributeSetId.'\'';
		 
	if ( $result = $db->query($sql) ){
		$row	= $result->fetch_row();
		if(count($row)>0){
			$attributeSetId = $row[0];
		}
		$result->close();
	}
	return $attributeSetId;
}
?>