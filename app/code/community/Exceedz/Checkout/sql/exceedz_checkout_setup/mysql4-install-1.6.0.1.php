<?php

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();

/* Include code from mysql4-upgrade-0.9.38-0.9.39.php */
$installer->getConnection()->addColumn($installer->getTable('sales_flat_quote_item'),
    'actual_price', 'decimal(12,4) default null AFTER `gw_tax_amount`');

$installer->endSetup();