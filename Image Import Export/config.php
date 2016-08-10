<?php
/*******************************************
************** Do Not Change **************
*******************************************/

$base_path = dirname(dirname(dirname(__FILE__)));

$localXmlFilename = $base_path . DIRECTORY_SEPARATOR .'app' . DIRECTORY_SEPARATOR .'etc'. DIRECTORY_SEPARATOR .'local.xml';

$xml = simplexml_load_file($localXmlFilename, NULL, LIBXML_NOCDATA);
 
$dbHostName = $xml->global->resources->default_setup->connection->host;
$dbName		= $xml->global->resources->default_setup->connection->dbname;
$dbUserName = $xml->global->resources->default_setup->connection->username;
$dbPassword = $xml->global->resources->default_setup->connection->password;
?>