<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$trackingNumber = 'LK970012566AU';
$ga_username = 'anonymous@auspost.com.au';
$ga_password = 'password';
$postUrl = 'https://devcentre.auspost.com.au/myapi/QueryTracking.xml?q='.$trackingNumber;
$postUrl = 'https://devcentre.auspost.com.au/myapi/CustomerCollectionPoints.xml';

$ch = curl_init($postUrl);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_USERPWD, $ga_username . ":" . $ga_password);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_COOKIE, 'OBBasicAuth=fromDialog');
$result = curl_exec($ch);
print_r($result);
exit;
