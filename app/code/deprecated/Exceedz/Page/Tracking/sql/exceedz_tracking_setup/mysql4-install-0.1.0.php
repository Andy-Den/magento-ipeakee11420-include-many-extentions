<?php

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();

/* Include code from mysql4-upgrade-0.9.38-0.9.39.php */
$installer->getConnection()->addColumn($installer->getTable('sales_flat_shipment_item'),
    'tracking_number', 'varchar(255) NULL');

$installer->getConnection()->addColumn($installer->getTable('sales_flat_shipment_item'),
    'courier_company', 'varchar(255) NULL');

$installer->getConnection()->addColumn($installer->getTable('sales_flat_shipment_item'),
    'post_code', 'int(11) NULL') ;

$installer->getConnection()->addColumn($installer->getTable('sales_flat_shipment_item'),
    'uploaded_date', 'TIMESTAMP ON UPDATE NOW() NOT NULL DEFAULT NOW()');

$installer->endSetup();