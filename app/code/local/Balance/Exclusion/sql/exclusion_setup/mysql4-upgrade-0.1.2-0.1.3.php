<?php

$installer = $this;
$installer->startSetup();
$subscriberTable = $installer->getTable('newsletter_subscriber');
$conn = $installer->getConnection();


$conn->addColumn($subscriberTable, 'ip_address', "varchar(255) default NUll");
$conn->addColumn($subscriberTable, 'date_register', "datetime default NULL");
$conn->addColumn($subscriberTable, 'form_url', "varchar(255) default NUll");

$installer->endSetup();
