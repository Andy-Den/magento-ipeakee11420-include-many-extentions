<?php

include 'app/Mage.php';

Mage::app();

//header('Content-type: text/xml');

//echo Mage::app()->getConfig()->getNode()->asXML();

echo get_class(Mage::getModel('sales/quote'));
