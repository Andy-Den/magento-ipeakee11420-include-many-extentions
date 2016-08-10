<?php
/**
 * Short description for file
 * 		This files is Import Custom Option. 
 *
 */
/*******************************************
* FOLLOWING PARAMETER NEED TO BE CHANGED *
*******************************************/
	$string = ''; $importStringResult = '';
	$fileToReadData = $filePath;
    $seperator = ',';

	// Open File / Put file into one string / Seperate each line into an array
	$fp = fopen($fileToReadData, 'r');
	// Read All File Data
	$handle = fopen($fileToReadData, "r");
	
	// Page Header
	$headers = array_flip(fgetcsv($handle, 4086, ","));
	$i = 1;
	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		
		// Set needed variables
		$catalogNumber			= $data[$headers['SKU']]; // Example: 304-ISLC
		$title					= $data[$headers['Custom_Title']]; // Example: Black Top
		$sku					= $data[$headers['Value_Sku']]; // Example: BLK
		$is_require				= $data[$headers['Is_Require']]; //  1
		$type					= $data[$headers['Type']]; // Example: field, area, file, date, date_time, time, drop_down, radio, checkbox, multiple
		$option_price			= $data[$headers['Option_Price']] ? $data[$headers['Option_Price']] : '0.00';
		$option_price_type		= !empty($data[$headers['Option_Price_Type']]) ? $data[$headers['Option_Price_Type']] : 'fixed';
		
		$value_title			= $data[$headers['Value_Title']];
        $value_title			= explode($seperator, $data[$headers['Value_Title']]);
		$value_sku				= $data[$headers['Value_Sku']] ? $data[$headers['Value_Sku']] : '';
		$value_price			= $data[$headers['Value_Price']] ? $data[$headers['Value_Price']] : '0.00';
        $value_price			= explode($seperator, $value_price);
		$value_price_type		= !empty($data[$headers['Value_Price_Type']]) ? $data[$headers['Value_Price_Type']] : 'fixed';
		$value_setup_price		= $data[$headers['Value_Setup_Price']] ? $data[$headers['Value_Setup_Price']] : '0.00';
		$value_setup_price_type	= !empty($data[$headers['Value_Setup_Price_Type']]) ? $data[$headers['Value_Setup_Price_Type']] : 'fixed';
		$max_characters			= $data[$headers['Max_Characters']] ? $data[$headers['Max_Characters']] : '';
		$sort_order				= !empty($data[$headers['Sort_Order']]) ? $data[$headers['Sort_Order']] : '0';
		$option_sort_order		= !empty($data[$headers['Sort_Order']]) ? $data[$headers['Option_Sort_Order']] : '0';
		$file_extension			= $data[$headers['File_Extension']] ? $data[$headers['File_Extension']] : '';
		$image_size_x			= $data[$headers['Image_Size_X']] ? $data[$headers['Image_Size_X']] : '';
		$image_size_y			= $data[$headers['Image_Size_Y']] ? $data[$headers['Image_Size_Y']] : '';

		if ($is_require == '' || $is_require == 'No') {
			$is_require	= '0';
		} else {
			$is_require	= '1';
		}		

		$store_id				= '0';
		
		/**
		 * Check Product is exit or not
		 */		
		$entityId = "";

		$sqlEntityId = 'SELECT entity_id '
					. '	FROM '
					. '   catalog_product_entity '
					. ' WHERE '
					. ' sku=\''.$catalogNumber.'\''; 
		
		$resultEntityId = $db->query($sqlEntityId);
		$rowEntityId	= $resultEntityId->fetch_row();
		$entityId 		= $rowEntityId[0];
		
		if ($entityId) {
			$string ="<tr width='50%'><td>".$catalogNumber."</td><td>".$entityId."</td><td>".$title."</td><td>".$type."</td><td>".$value_title."</td>";

			$flag = true;
			
			// Check Duplicate Record
			$sqlCheck = 'SELECT CPO.option_id '
						. '	FROM '
						. ' catalog_product_option AS CPO '
						. ','.' catalog_product_option_title AS CPOT '
						. ' WHERE '
						. '     CPO.option_id=CPOT.option_id '
						. ' AND '.' CPO.product_id=\''.$entityId.'\''
						. ' AND '.' CPOT.title=\''.$title.'\''
						;
			
			if ($resultCheck = $db->query($sqlCheck)){
				$rowCheck	= $resultCheck->fetch_row();
				
				if($rowCheck[0] > 0){
					$option_id = $rowCheck[0];
					$flag = false;
                    continue;
				}
				$resultCheck->close();
			}
			
			if($flag) {
				$string .="<td><font color=\'blue\'>New Record</font></td>";
				$option_id = '';
				
				// Insert Option
				$sqlCPO = ' INSERT INTO `catalog_product_option` '
							. ' SET '
							. ''.' `product_id` =\''.$entityId.'\''
							. ','.' `type` =\''.$type.'\''
							. ','.' `is_require` =\''.$is_require.'\''
							. ','.' `sku` =\''.$sku.'\''
							. ','.' `max_characters` =\''.$max_characters.'\''
							. ','.' `sort_order` =\''.$sort_order.'\''
							. ','.' `file_extension` =\''.$file_extension.'\''
							. ','.' `image_size_x` =\''.$image_size_x.'\''
							. ','.' `image_size_y` =\''.$image_size_y.'\''
							;											
				
				if($db->query ( $sqlCPO )){
					if($db->affected_rows>0){
						$option_id = $db->insert_id;
						$string .='<td>catalog_product_option Record Saved</td>';
						
						// Insert Option Title
						$sqlCPOT = ' INSERT INTO `catalog_product_option_title` '
								. ' SET '
								. ''.' `option_id` =\''.$option_id.'\''
								. ','.' `store_id` =\''.$store_id.'\''
								. ','.' `title` =\''.$title.'\''
								;
						if($db->query ( $sqlCPOT )){
							if($db->affected_rows>0){
								$string .='<td>`catalog_product_option_title` Record Saved</td>';
							}
							else {
								$string .='<td><font color=\'red\'>`catalog_product_option_title` Record Not Saved</font></td>';
							}
						}
						else {
							//$string .='<td><font color=\'red\'>`catalog_product_option_title` Record Not Saved</font></td>';
						}
					}
					else {
						//$string .='<td><font color=\'red\'>catalog_product_option Record Not Saved</font></td>';
					}

					//Insert Into catalog_product_option_price table only if type is area / field
					if($type=='area' || $type=='field' || $type=='date' || $type=='date_time' || $type=='time' || $type=='file')
					{
						// Insert Option Title
						 $sqlCPOP = ' INSERT INTO `catalog_product_option_price` '
									. ' SET '
									. ''.' `option_id` =\''.$option_id.'\''
									. ','.' `store_id` =\''.$store_id.'\''
									. ','.' `price` =\''.$option_price.'\''
									. ','.' `price_type` =\''.$option_price_type.'\'';
									
						if($db->query ( $sqlCPOP )){
							if($db->affected_rows>0){
								$string .='<td>`catalog_product_option_price` Record Saved</td>';
							}
							else {
								$string .='<td><font color=\'red\'>`catalog_product_option_price` Record Not Saved</font></td>';
							}
						}
						else {
							$string .='<td><font color=\'red\'>`catalog_product_option_price` Record Not Saved</font></td>';
						}
					}
				}
			}
			else {
				$string .="<td><font color=\'blue\'>Record Already Exits</font></td>";
			}	

			// Insert Option Value
			if($type=='drop_down' || $type=='radio' || $type=='checkbox' || $type=='multiple') {
                foreach($value_title as $key=>$color)
				{
					
					// Insert Option
                    $sqlCPOTV = ' INSERT INTO `catalog_product_option_type_value` '
                                . ' SET '
                                . ''.' `option_id` =\''.$option_id.'\''
                                . ','.' `sku` =\''.$value_sku.'\''
                                . ','.' `sort_order` =\''.($key+1).'\'';							
                                
                    if($db->query ( $sqlCPOTV )){
                        if($db->affected_rows>0){
                            $option_type_id = $db->insert_id;
                            //print_r($option_type_id); exit;  
                            
                            $sqlCPOTT = ' INSERT INTO `catalog_product_option_type_title` '
                                        . ' SET '
                                        . ''.' `option_type_id` =\''.$option_type_id.'\''
                                        . ','.' `store_id` =\''.$store_id.'\''
                                        . ','.' `title` =\''.$color.'\'';							
                            //print($sqlCPOTT);
                                if($db->query ($sqlCPOTT)){
                                    //$string .='<td>`catalog_product_option_type_title` Record Saved</td>';
                                    $string .='<td>Record Saved</td>';
                                }
                                else {
                                    //$string .='<td><font color=\'red\'>`catalog_product_option_type_title` Record Not Saved</font></td>';
                                    $string .='<td><font color=\'red\'>Record Not Saved</font></td>';
                                }
                            

                            $sqlCPOTP = ' INSERT INTO `catalog_product_option_type_price` '
                                        . ' SET '
                                        . ''.' `option_type_id` =\''.$option_type_id.'\''
                                        . ','.' `store_id` =\''.$store_id.'\''
                                        . ','.' `price` =\''.(isset($value_price[$key]) ? $value_price[$key] : 0.00).'\''
                                        . ','.' `price_type` =\''.$value_price_type.'\'';							
                                
                            if($db->query ( $sqlCPOTP )){
                                //$string .='<td>`catalog_product_option_type_price` Record Saved</td>';
                                $string .='<td>Record Saved</td>';
                            }
                            else {
                                //$string .='<td><font color=\'red\'>`catalog_product_option_type_price` Record Not Saved</font></td>';
                                $string .='<td><font color=\'red\'>Record Not Saved</font></td>';
                            }
                        }
                    }
                    else {
                        //$string .='<td><font color=\'red\'>`catalog_product_option_type_value` Record Not Saved</font></td>';
                        $string .='<td><font color=\'red\'>Record Not Saved</font></td>';
                    }						
                                        
                    //IF Setup Price is there
                    //Check if catalog_product_option_type_setup_price Table exits
                    $table_results = $db->query("SHOW tables LIKE `catalog_product_option_type_setup_price`");
                    if( $table_results->num_rows > 0 ) {
                        
                        $sqlCPOTP = ' INSERT INTO `catalog_product_option_type_setup_price` '
                                    . ' SET '
                                    . ''.' `option_type_id` =\''.$option_type_id.'\''
                                    . ','.' `store_id` =\''.$store_id.'\''
                                    . ','.' `setup_price` =\''.$value_setup_price.'\''
                                    . ','.' `setup_price_type` =\''.$value_setup_price_type.'\'';							
                            
                        if($db->query ( $sqlCPOTP )){
                            $string .='<td>`catalog_product_option_type_setup_price` Record Saved</td>';
                        }
                        else {
                            //$string .='<td><font color=\'red\'>`catalog_product_option_type_setup_price` Record Not Saved</font></td>';
                            $string .='<td><font color=\'red\'>Record Not Saved</font></td>';
                        }
                    }
                    else {
                        //$string .='<td><font color=\'red\'>`catalog_product_option_type_setup_price` Record Not Saved</font></td>';
                        $string .='<td><font color=\'red\'>Record Not Saved</font></td>';
                    }
			
				}
			}	
			// Update the Custom Option Status
			$sqlCPOTP = ' UPDATE `catalog_product_entity` '
						. ' SET '
						. ' `has_options` =\'1\''
						. ' WHERE '
						. ' `entity_id` =\''.$entityId.'\'';
														
			if($db->query ( $sqlCPOTP )){
				//$string .='<td></td><td></td><td></td><td>`catalog_product_entity` Record Saved</td>';
			}
			else {
				//$string .='<td><font color=\'red\'>`catalog_product_entity` Record Not Saved</font></td>';
			}
		}
		else {
			$string .='<td align=\'center\'><font color=\'red\'>SKU Not Found</font></td>';
		}		
		$string .="</tr>";
		$importStringResult[] = $string;	
} // End While loop

/*******************************************
* 					FUNCTIONS			   *
*******************************************/
/**
 * Used to Fetch the Product Name
 *
 * @param  int	$productId
 */
function checkOptionTitle($db, $option_id)
{
	$sql = ' SELECT title FROM catalog_product_option_type_title '
		 . ' WHERE option_type_id IN ( '
		 . ' SELECT option_type_id FROM catalog_product_option_type_value '
		 . ' WHERE option_id = '.$option_id. ')'  ;

	if ($result = $db->query($sql)){
		if($db->affected_rows>0){
			while($row	= $result->fetch_array()) {
				if(count($row)>0){
					$title[] =$row[0];
				}
			}
			return $title;
			$result->close();
		}
		$title[] = 'ok12';
		return $title;
	}
	return false;
}		
?>