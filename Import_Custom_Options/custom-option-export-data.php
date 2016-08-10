<?php
/**
 * Short description for file
 * 		This files is used to export Product details 
 *
 * PHP versions All
 */
/*******************************************
* FOLLOWING PARAMETER NEED TO BE CHANGED *
*******************************************/

/**************************** BOF: Files To Write ****************************/
//$fileToWrite	= 'custom-options-'.date('Y-m-d').'.csv';
//$file_to_write	= 'sheets/'.$fileToWrite;
/**************************** BOF: Files To Write ****************************/

$data     = '';
$totalRecords = '';

$fileName	= 'custom-options-'.$_REQUEST['attribute_set'].'-'.date('Y-m-d').'.csv';
$file_to_write	= 'sheets/'.$fileName;

@unlink($file_to_write);

// Heading of the excel sheet
$data = array( "SKU","Custom_Title","Type","Is_Require","Option_Price","Option_Price_Type","Value_Title",
			   "Value_Sku","Value_Price","Value_Price_Type","Max_Characters","Value_Setup_Price","Value_Setup_Price_Type",
			   "Sort_Order","Option_Sort_Order","File_Extension","Image_Size_X","Image_Size_Y"
			 );
			 
importProducts($data, $file_to_write);

if ( $_REQUEST['attribute_set'] == '' || $_REQUEST['attribute_set'] == 'All' ) {
	$sqlCondition = '';
}
else {
	$sqlCondition = " AND attribute_set_id='".$_REQUEST['attribute_set']."'";
}

// SELECT Products
$sqlEntityId = 'SELECT '
				. ' entity_id, sku, attribute_set_id '
				. ' FROM '
				. ' catalog_product_entity '
				. ' WHERE '
				. ' has_options=\'1\''
				. $sqlCondition
				//. ' LIMIT 0,1000'
				;

if ($resultEntityId = $db->query($sqlEntityId)) {
	while ($rowEntityId	= $resultEntityId->fetch_row()) {
		$entityId 		= $rowEntityId['0'];
		$sku 			= $rowEntityId['1'];
		$attributeSetId = $rowEntityId['2'];
		$flag 			= true;
		$data     = '';
		// SELECT optionId of the products		
		$sqlCheck = 'SELECT CPO.option_id '
					. '	FROM '
					. ' catalog_product_option AS CPO '
					. ','.' catalog_product_option_title AS CPOT '
					. ' WHERE '
					. ' CPO.option_id = CPOT.option_id '
					. ' AND '.' CPO.product_id=\''.$entityId.'\''
					;
		
		if ($resultCheck = $db->query($sqlCheck)){
			if($db->affected_rows>0){
				while ($rowCheck	= $resultCheck->fetch_array()) {
					if($rowCheck[0] > 0) {
						$option_id = $rowCheck[0];
						$flag = true;					
			
			// SELECT Options
			$sqlCPO = ' SELECT * FROM catalog_product_option WHERE option_id IN ('.$option_id.')';
			
			if ($resultSqlCPO = $db->query ($sqlCPO)){
				
				if($db->affected_rows>0){
					while ($row	= $resultSqlCPO->fetch_array()) {
						$data['sku']    = $sku;
						$option_id		= $row['option_id'];
						$type         	= $row['type'];
						$Value_Sku     	= $row['sku'];
						$is_require     = $row['is_require'];	
						$max_characters = $row['max_characters'];
						$sort_order     = $row['sort_order'];
						$file_extension = $row['file_extension'];
						$image_size_x   = $row['image_size_x'];
						$image_size_y   = $row['image_size_y'];
						
						// SELECT Option Title
						$sqlCPOT = ' SELECT title FROM `catalog_product_option_title` '
								. ' WHERE '
								. ''.' `option_id` =\''.$option_id.'\'';
								
						if ($resultSqlCPOT = $db->query ($sqlCPOT)){
							if($db->affected_rows>0){
								while ($row	= $resultSqlCPOT->fetch_array()) {
									$data['custom_title']   = $row['title'];
								}
							}
						}						
						$data['type'] = $type;
						$data['is_require']     = $is_require;						
						
						//SELECT FROM catalog_product_option_price table only if type is area / field
						if($type=='area' || $type=='field' || $type=='date'|| $type=='date_time' || $type=='time')
						{
							// SELECt Option Title
							$sqlPrice = ' SELECT price, price_type '
									   .' FROM catalog_product_option_price '
									   .' WHERE option_id = '.$option_id;
									  
							if ($resultPrice = $db->query ($sqlPrice)){
					
								if($db->affected_rows>0){
									while ($row	= $resultPrice->fetch_array()) {
										$option_price       = $row['price']; 
										$option_price_type  = $row['price_type'];										
									}
								}
							}
							else {
									$option_price       = '';
									$option_price_type  = '';	
							}							
						}// end of area or field if
						
						$valueTitle = array();	
						$ValueSku = array();
						$value_price = array();
						//SELECT Option Value
						if($type=='drop_down' || $type=='radio' || $type=='checkbox' || $type=='multiple') {
							// SELECT Option
							$sqlCPOTV = ' SELECT option_type_id, sku,sort_order FROM `catalog_product_option_type_value`'
										. ' WHERE '
										. ''.' `option_id` =\''.$option_id.'\''; 
								
							if($resultsqlCPOTV = $db->query ( $sqlCPOTV )){
								if($db->affected_rows>0){
									while ($row	= $resultsqlCPOTV->fetch_array()) {
										$option_type_id 	= $row['option_type_id'];
										$ValueSku[]  = $row['sku'];
										$option_sort_order  = $row['sort_order'];
										$option_price       = '';
										$option_price_type  = '';
										
										$sqlCPOTT = ' SELECT title FROM `catalog_product_option_type_title` '
													. ' WHERE '
													. ''.' `option_type_id` =\''.$option_type_id.'\'';						
											
										if($resultsqlCPOTT = $db->query ( $sqlCPOTT )){
											while ($row	= $resultsqlCPOTT->fetch_array()) {
												$valueTitle[] = $row['title'];
											}
										}
									 
										$sqlCPOTP = ' SELECT price, price_type FROM `catalog_product_option_type_price` '
													. ' WHERE '
													. ''.' `option_type_id` =\''.$option_type_id.'\'';
											
										if($resultsqlCPOTP = $db->query ( $sqlCPOTP )){
											while ($row	= $resultsqlCPOTP->fetch_array()) {
												$value_price[] 		= $row['price'];
												$value_Price_Type 	= $row['price_type'];
											}
										}
										
										//IF Setup Price is there
										//Check if catalog_product_option_type_setup_price Table exits
										$table = 'catalog_product_option_type_setup_price';
										$sql_table = "SHOW tables LIKE '".$table."'";
										//echo $table_results = $db->query($sql_table);
										
										//if( $table_results->num_rows > 0 ) {
								
											$sqlCPOTSP = ' SELECT setup_price, setup_price_type FROM `catalog_product_option_type_setup_price` '
														. ' WHERE '
														. ''.' `option_type_id` =\''.$option_type_id.'\'';
												
											if($resultsqlCPOTSP = $db->query ( $sqlCPOTSP )){
												while ($row	= $resultsqlCPOTSP->fetch_array()) {
													$value_setup_price 		= $row['setup_price'];
													$value_setup_price_type	= $row['setup_price_type'];
												}
											}
											else {
												$value_setup_price 		= '';
												$value_setup_price_type	= '';
											}
										//}
										}
								}
							}
						//if(count($ValueSku) == 1 && $ValueSku[0] != '' ) {$Value_Sku = $ValueSku;}
						}// end of type if
						
						if(!$max_characters) { $max_characters = 0;}
						$string = '';
						if(count($valueTitle) > 1 ) {
							foreach($valueTitle as $key=>$value) {
								$data['option_price']			= $option_price;
								$data['option_price_type']		= $option_price_type;
								$data['Value_Title']  			= $value;
								$data['Value_Sku']  			= $ValueSku[$key];
								$data['Value_Price']  			= $value_price[$key];
								$data['Value_Price_Type'] 		= $value_Price_Type;
								$data['max_characters'] 		= $max_characters;
								$data['value_setup_price'] 		= $value_setup_price;
								$data['value_setup_price_type'] = $value_setup_price_type;
								$data['sort_order']     		= $sort_order;
								$data['option_sort_order']  	= $option_sort_order;
								$data['file_extension'] 		= $file_extension;
								$data['image_size_x']   	 	= $image_size_x;
								$data['image_size_y']   	 	= $image_size_y;
								
								$string .= "<tr><td>".$data['sku']."</td><td>".$entityId."</td><td>".$data['custom_title']."</td><td>".$data['type']."</td><td>".$data['Value_Title']."</td></tr>";	
								
								importProducts($data, $file_to_write);
								$totalRecords = $totalRecords + 1;
							}							
						}
						else{
								$data['option_price']			= $option_price;
								$data['option_price_type']		= $option_price_type;
								$data['Value_Title']			= @$valueTitle[0];
								$data['Value_Sku']  			= $Value_Sku;
								$data['Value_Price']       		= '';
								$data['Value_Price_Type']  		= '';
								$data['max_characters'] 		= $max_characters;
								@$data['value_setup_price'] 		= $value_setup_price;
								@$data['value_setup_price_type'] = $value_setup_price_type;
								$data['sort_order']     		= $sort_order;
								@$data['option_sort_order']  	= $option_sort_order;
								$data['file_extension'] 		= $file_extension;
								$data['image_size_x']   	 	= $image_size_x;
								$data['image_size_y']   	 	= $image_size_y;
								
								importProducts($data, $file_to_write);
								$totalRecords = $totalRecords + 1;
								$string .= "<tr><td>".$data['sku']."</td><td>".$entityId."</td><td>".$data['custom_title']."</td><td>".$data['type']."</td><td>".$data['Value_Title']."</td></tr>";
						}						
						$exportStringResult[] = $string;
					}
				}// end of while
			}// end of no. of rows if
		}// end of flag if
		}
		}
			$resultCheck->close();
		}
	}// end of while
}// end of if

/*******************************************
* 					FUNCTIONS			   *
*******************************************/
/**
 * Used to Import Report in CSV File
 *
 * @param  $data	$filename
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
	fputcsv($fh, $data);
	fclose($fh);
}
?>