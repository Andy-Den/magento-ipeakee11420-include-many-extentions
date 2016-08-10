<?php
/**
 * Short description for file
 * 		This files is used to Clean the logs from database and folder(/var/log/ and /var/report/).
 *
 * PHP versions All
 *
 * @category   PHP Coding File
 * @package
 */

/*********************************************************
* Licence:
* This file is sole property of the installer.
* Any type of copy or reproduction without the consent
* of owner is prohibited.
* If in any case used leave this part intact without
* any modification.
* All Rights Reserved
* Copyright 2012 Owner
*******************************************************/
/*******************************************
* FOLLOWING PARAMETER NEED TO BE CHANGED *
*******************************************/
	@date_default_timezone_set('America/Chicago');

	$cleanLogTablesFlag 	= true;
	$cleanVarDirectoryFlag 	= true;

	/**
	* BOF :: Database Log Clean Setting
	*/
	$tables = array(
				  'dataflow_batch_export'
				, 'dataflow_batch_import'
				, 'log_url'
				, 'log_url_info'
				, 'log_visitor'
				, 'log_visitor_info'
				, 'log_visitor_online'
			);
	// Other tables , 'log_customer' , 'log_quote' 	, 'log_summary'	, 'log_summary_type' , 'report_event' , 'report_viewed_product_index', 'report_compared_product_index'

	/**
	* BOF :: Folder Log Clean Setting
	*/
	$date 				= date("Ymd");
	$path 				= dirname(dirname(__FILE__)).'/var/';
	$backupFolder 		= 'var_backup/';
	$backupFolderDir 	= $path . $backupFolder;

	$targetDirPath 			= $backupFolderDir . $date . '/';
	$targetDirPathReport 	= $backupFolderDir . $date . '/report/';

	$cleanVarDirectoryCommands = array(
										'mkdir '.$targetDirPath
										, 'chmod 777 '.$targetDirPath
										, 'mv ' .$path.'log/* '.$targetDirPath
										, 'mkdir '.$targetDirPathReport
										, 'chmod 777 '.$targetDirPathReport
										, 'mv ' .$path.'report/* '.$targetDirPathReport
									);

	/**
	* BOF :: Mail Setting
	*/
	$arrMail['projectName']		= 'Treeet'; // No Blank Space
	$arrMail['mailFrom']		= $arrMail['projectName'].' :: Server Log Clean';
	$arrMail['mailFromEmail']	= 'manish.pawar@zeonsolutions.com';
	$arrMail['mailTo']			= 'manish.pawar@zeonsolutions.com';
	$arrMail['subject']			= $arrMail['projectName'].' :: ';
	$arrMail['logFile']			= $path.$arrMail['projectName'].'-log-clean-'.date('Ymd').'.txt';
	$fp							= openLogFile($arrMail);

/*******************************************
* FOLLOWING PARAMETER NEED TO BE CHANGED *
*******************************************/

	/**
	* BOF :: Clean the Logs from database
	*/
	writeLog($fp, '--------------TRUNCATE Table Start Here--------------');
	if ( $cleanLogTablesFlag ) {
		cleanLogTables($tables, $fp, $arrMail);
    } else {
		writeLog($fp, 'cleanLogTablesFlag Flag is Disabled');
	}
    /**
	* EOF :: Clean the Logs from database
	*/

	/**
	* BOF :: Clean the var Directory
	*/
	writeLog($fp, '');
	writeLog($fp, '--------------VAR Folder Clean Start Here--------------');
	if ( $cleanVarDirectoryFlag ) {
		// Check directory is present or not
		if ( !is_dir($backupFolderDir) ) {
			try {
				// Create Backup Directory
				$directoryCommands = array(
									'mkdir '.$backupFolderDir
									, 'chmod 777 '.$backupFolderDir
								);
				writeLog($fp, '');
				writeLog($fp, '--------------'.$backupFolderDir.' Folder Check Start Here--------------');
				runCommand($directoryCommands, $fp);
			}
			catch (Exception $e) {
				writeLog($fp, $backupFolderDir.' Error : '.$e->getMessage());
			}
		}

		// Clean the Folder
		writeLog($fp, '');
		writeLog($fp, '--------------Folder Empty Start Here--------------');
		runCommand($cleanVarDirectoryCommands, $fp);

	} else {
		writeLog($fp, $cleanVarDirectoryFlag.' Flag is Disabled.');
	}
	/**
	* EOF :: Clean the var Directory
	*/

	/**
	* BOF :: Send Status Mail to Admin
	*/
	$logContents = getLog($arrMail['logFile']);
	sendMail($arrMail['mailFrom'], $arrMail['mailFromEmail'], $arrMail['mailTo'], $arrMail['subject'].' Log Status', $logContents);

	// Move Log File
	exec('mv ' .$arrMail['logFile'].' '.$targetDirPath);

/*******************************************
* FUNCTIONALITY CODE STARTS BELOW *
*******************************************/

/**
* Function is used to TRUNCATE the table data
* @param	array	$table		List of tables
* @param	object	$fp			File Object
* @param	array	$arrMail	Mail Details
*
*/
function cleanLogTables($tables, $fp, $arrMail) {
	/*
	* Get database details from local.xml file
	*/
	$localXmlPath = dirname(dirname(__FILE__)).'/app/etc/local.xml';
	$xml = simplexml_load_file($localXmlPath, NULL, LIBXML_NOCDATA);

	$db['host'] = $xml->global->resources->default_setup->connection->host;
	$db['name'] = $xml->global->resources->default_setup->connection->dbname;
	$db['user'] = $xml->global->resources->default_setup->connection->username;
	$db['pass'] = $xml->global->resources->default_setup->connection->password;
	$db['pref'] = $xml->global->resources->db->table_prefix;

	if ( !mysql_connect($db['host'], $db['user'], $db['pass']) ) {
		writeLog($fp, 'Database Connection Error : '.mysql_error());
		sendMail($arrMail['mailFrom'], $arrMail['mailFromEmail'], $arrMail['mailTo'], $arrMail['subject'].' Database Connection Error', 'Database Connection Error');
		die();
	}
	if ( !mysql_select_db($db['name']) ) {
		writeLog($fp, 'Select Database Error: '.mysql_error());
		sendMail($arrMail['mailFrom'], $arrMail['mailFromEmail'], $arrMail['mailTo'], $arrMail['subject'].' Select Database Error', 'Select Database Error');
		die();
	}

	if ( is_array($tables) && count($tables) > 0 ) {
		foreach($tables as $key => $tableName) {
			if ( mysql_query('TRUNCATE `'.$db['pref'].$tableName.'`') ) {
				writeLog($fp, 'RUN SUCCESSFULLY :: TRUNCATE `'.$db['pref'].$tableName.'`');
			}
			else {
				writeLog($fp, 'ERROR :: TRUNCATE `'.$db['pref'].$tableName.'` : '.mysql_error());
			}
		}
	}
	else {
		writeLog($fp, 'Tables Array is Empty');
	}
}

/**
* Function is used to moved the files from folder to backup folder
* @param	array	$arrCommands	List of Command
* @param	object	$fp			File Object
*/
function runCommand($arrCommands, $fp) {

	if ( is_array($arrCommands) && count($arrCommands) > 0 ) {
		foreach($arrCommands as $key => $command) {
			try {
				exec($command);
				writeLog($fp, 'RUN SUCCESSFULLY :: '.$command);
			} catch (Exception $e) {
				writeLog($fp, 'ERROR :: '.$command.' : '. $e->getMessage());
			}
		}
	}
	else {
		writeLog($fp, 'Command Array is Empty');
	}
}

/**
 * Open the Log File
 * @param	array	$arrMail	Mail Details
 */
function openLogFile($arrMail)
{
	$logFile = $arrMail['logFile'];

	$fh = fopen($logFile, 'a+') or die("can't open file");
	if (!$fh ) {
		sendMail($arrMail['mailFrom'], $arrMail['mailFromEmail'], $arrMail['mailTo'], $arrMail['subject'].' Log file Not Created', $logFile.' = Log file Not Created');
		die('Log file Not Created');
	}
	else {
		exec('chmod 777 '.$logFile);
	}
	return $fh;
}

/**
 * Open the Log File
 *
 * @param  object	$fh			File Open Object
 * @param  string	$message	Log Message
 * @param  int		$action		File Name
 */
function writeLog($fh, $message)
{
	echo ''.$msg = date("d-M-Y H:i:s")." :: ".$message."\n";
	fwrite($fh,$msg);
}

function getLog($logFile)
{
	return file_get_contents($logFile);
}

function closeLogFile($fh)
{
	//unlink($fh);
}

/**
 * Function Is used to send the mail To user
 *
 * @param  string	$mailFrom		Name
 * @param  string	$mailFromEmail	Email ID
 * @param  string	$mailTo			Mail To Email ID
 * @param  string	$mailSubject	Subject
 * @param  string	$mailBody		Message
 * @param  string	$mailType		Mail Type (TEXT / HTML)
 */
// Function Is used to send the mail To user
function sendMail($mailFrom='', $mailFromEmail='', $mailTo='', $mailSubject='', $mailBody='', $mailType='TEXT')
{

	$headers  = "MIME-Version: 1.0" . "\r\n";

	if($mailType == "TEXT") {
		$headers .= "Content-type: text/plain; charset=iso-8859-1" . "\r\n";
	}
	else {
		$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
	}

	$headers .= 'From: '.$mailFrom.' <'.$mailFromEmail.'>' . "\r\n".
			'X-Mailer: PHP/' . phpversion();

	if( $mailTo != "" ) {
		// echo $mailTo."<br>".$mailSubject."<br>".$mailBody."<br>".$headers ;
		$result = mail($mailTo , $mailSubject , $mailBody , $headers );
	}

	return $result ;
}
?>