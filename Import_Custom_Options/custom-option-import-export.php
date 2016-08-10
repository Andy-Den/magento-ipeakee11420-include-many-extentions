<?php
/**
 * Short description for file
 * 		This files is Import Custom Option. 
 */
/*******************************************
* FOLLOWING PARAMETER NEED TO BE CHANGED *
*******************************************/
include('config.php');

$db = new mysqli ( $dbHostName, $dbUserName, $dbPassword, $dbName );

if (mysqli_connect_errno()) {
	writeLog($fp, " Connect failed: ".mysqli_connect_error());
	exit();
}
$selectedOption = NULL;
$exportStringResult = array();
$selectedOption = isset($_REQUEST['selectedOption'])?$_REQUEST['selectedOption']:'';
$stringResult 	= array();
$msg			= '';
$message		= '';
$importStringResult = array();
$downloadSampleFile = 'custom-option-template.csv';
/************************************** BOF :: Export Custom Options *********************************************/ 
if(isset($selectedOption) && ($selectedOption == 'exportDiv') && (isset($_REQUEST['submit']) && ($_REQUEST['submit']=='Export'))){
	require_once('custom-option-export-data.php');
	if($totalRecords) {
		$msg = $totalRecords." Records found";
	}
	else {
		$msg = "0 Records found";
	}

}/************************************** BOF :: Export Custom Options *********************************************/ 
elseif(isset($selectedOption) && ($selectedOption == 'importDiv') && (isset($_REQUEST['submit']) && ($_REQUEST['submit']=='Import'))){
    
	if($_FILES['ImportSheet']['name'] ) {
		$fileToRead = $_FILES['ImportSheet']['name']; 	
		$_FILES['ImportSheet']['tmp_name'];
		$uploaddir  = "sheets/";
		$filePath	= $uploaddir.$fileToRead;
	}
	else {
		$path = $_POST['ImportSheetName']; 
		$file = explode('/', $path);
		$uploaddir  = $file[0];
		$fileToRead = $file[1];	
		$filePath = $uploaddir."/".$fileToRead; 
	}
	
	$ext = substr(strrchr($fileToRead, '.'), 1);
		
	if($fileToRead != '' && $ext == 'csv') {
		
		if (is_uploaded_file($_FILES['ImportSheet']['tmp_name'])) {
			move_uploaded_file($_FILES['ImportSheet']['tmp_name'], $filePath);
		}			
		if(file_exists($filePath)){
			require_once('custom-option-import-data.php');
			$message = "Data Inserted Succesfully";
		}
		else {
			$message = "File Does Not Exists.";
		}
	}
	else {
		$message = "Please Provide Csv File To Import Custom Options.";
	}
}

$attributeString = getAttributeSet($db);

?>
<title>Script: Custom Options</title>
<form name="productForm" method="post" enctype="multipart/form-data">
    <?php	/************************************** BOF :: Choose Options - Export Or Import ******************************/  ?>
    <table align="center" width="100%" style="font-family: Verdana; font-size: 11px" border="1">
        <tr>
            <td colspan="2"> <H2 align="center">Custom Options</H2></td>
        </tr>
        <tr>
            <td> <H3  align="center">Choose Option</H3></td>
            <td><input type="radio" name="selectedOption" value="exportDiv" onClick="ShowHide(this.value);" <?php if ( $_REQUEST['selectedOption'] == 'exportDiv' ) { echo 'checked="checked"'; }?> />Export
                <input type="radio" name="selectedOption" value="importDiv" onClick="ShowHide(this.value);" <?php if ( $_REQUEST['selectedOption'] == 'importDiv' ) { echo 'checked="checked"'; }?> />Import
            </td>
        </tr>			
    </table>
    <?php /************************************** EOF :: Choose Options - Export Or Import *******************************/ ?>
     
    <?php /************************************** BOF :: Export Custom Options *********************************************/ ?>
	<div id="exportDiv" <?php if(!isset($_POST['submit']) || (isset($_POST['submit']) && $_POST['submit'] == 'Import')):?>style="display:none"<?php endif;?>>
		<table align="center" width="100%" style="font-family: Verdana; font-size: 14px">
			<tr align="center"><td colspan="2">&nbsp;</td></tr>
			<tr align="center"><td colspan="2"><strong>Export Custom Option</strong></td></tr>
			<tr align="center"><td colspan="2">&nbsp;</td></tr>
			<tr>
				<td colspan="2" align="center"><select name='attribute_set'>
									<option value=''>Select Attribute Set</option>
									<option value='All'>All Attributes</option>
									<?php echo $attributeString; ?>
								</select>
				</td>
			 <tr>
				<td align="center" style="vertical-align:top;padding-top:10px;"><input type="submit" name="submit" value="Export" /></td>
				<?php if(count($exportStringResult) > 0) : ?>
				<td align="center" style="font-family: Verdana; font-size: 11px;"><a href="downloadReport.php?fileName=<?php echo $fileName; ?>">Download Report</a></td>
				<?php endif; ?>
			</tr>
			<tr align="center"><td colspan="2">&nbsp;</td></tr>
			<tr><td colspan="2" align="center" style="color:#FF3333;"><?php echo $msg ?></td></tr>
		</table>
		<?php if(count($exportStringResult) > 0) : ?>
		<table align="center" width="100%" style="font-family: Verdana; font-size: 11px" border="1" >
				<tr>
					<td align="center"><strong>Sku</strong></td>
					<td align="center"><strong>Enity Id</strong></td>
					<td align="center"><strong>Title</strong></td>
					<td align="center"><strong>Type</strong></td>
					<td align="center"><strong>Option Title</strong></td>
				</tr>	
				<?php
					foreach($exportStringResult as $value) {
						echo $value;
					}						
				?>
			</table>
			<?php endif; ?>	
	</div>
    <?php /************************************** EOF :: Export Custom Options *********************************************/ ?>

	 <?php /************************************* BOF :: Import Custom Options *********************************************/ ?>
	<div id="importDiv" <?php if(!isset($_POST['submit']) || (isset($_POST['submit']) && $_POST['submit'] == 'Export')):?>style="display:none"<?php endif;?>>    
		<table align="center" width="100%" style="font-family: Verdana; font-size: 11px">
			<tr>
				<td>
					<table align="center" width="100%" style="font-family: Verdana; font-size: 11px" border="1">
						<tr><td colspan="2"><H2 align="center">Import Custom Options In Database</H2></td></tr>
						<tr align="center">
							<td colspan="2">
								<table width="100%" style="font-family: Verdana; font-size: 11px">
									<tr align="center">
										<td style="padding-left:100px;">
										Import Sheet To Insert Data  : <input type="file" name="ImportSheet" />
										</td>
									</tr>
									<tr><td style="padding-left:450px;"><strong>OR </strong></td></tr>
									<tr><td style="padding-left:420px;">File Path   : <input type="text" name="ImportSheetName" value=""/></td></tr>
									<tr><td style="padding-left:480px;">
										<em><span style="text-align:right">File name in text box should be 'FolderName/FileName'</span></em></td>
									</tr> 
							   </table>
						   </td>
					   </tr>
					   <tr>
							<td align="center" style="vertical-align:top;padding-top:10px;">
							<input type="submit" name="submit" value="Import" />   
							<a href="downloadReport.php?fileName=<?php echo $downloadSampleFile; ?>">Download Sample Report</a></td>
					   </tr>
					   <tr><td colspan="2" align="center" style="color:#FF3333;"><?php echo $message ?></td></tr>	
					</table>
				</td>
			</tr>            
		</table>
		<?php if(count($importStringResult) >= 1) : ?>
		<table align="center" width="100%" style="font-family: Verdana; font-size: 11px" border="1">
					<tr>
						<td><strong>Sku</strong></td>
						<td><strong>Enity Id</strong></td>
						<td><strong>Title</strong></td>
						<td><strong>Type</strong></td>
						<td><strong>Option Title</strong></td>
						<td><strong>Record Status</strong></td>
						<td><strong>Option Title Status</strong></td>
						<td><strong>Option Price Status</strong></td>
						<td><strong>Setup Price Status</strong></td>
						<td><strong>Product Status</strong></td>
					</tr>	
					<?php 	foreach($importStringResult as $value) {
									echo $value;
							}
					?>					
			</table>		
			<?php $importStringResult=''; endif; ?>
	</div>	
	<?php /************************************** EOF :: Import Custom Options *****************************************/  ?>
</form>
<script type="text/javascript">
function ShowHide(thediv) {
	if (thediv == 'exportDiv') {
		document.getElementById('exportDiv').style.display = "block";
		document.getElementById('importDiv').style.display = "none";
	}
	else {
		document.getElementById('importDiv').style.display = "block";
		document.getElementById('exportDiv').style.display = "none";
	}
}
</script>
<?php
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
?>