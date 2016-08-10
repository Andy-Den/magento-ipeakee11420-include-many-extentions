<?php

/**
 * Milandirect
 *
 * @category  Milandirect
 * @package   Milandirect_SimpleConfigurable
 * @copyright 2016 Balance Internet
 */
$installer = $this;
$conn = $installer->getConnection();
$installer->startSetup();

/**
 * Create table 'simpleconfigurable/configurable'
 */
$table = $conn
    ->newTable($installer->getTable('simpleconfigurable/configurable'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'primary'   => true,
        'identity'  => true,
    ), 'Magento Product ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
        'nullable'  => false,
        'primary'   => true,
    ), 'Magento Store Id')
    ->addColumn('price_from', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'primary'   => false,
    ), 'Price from')
    ->addColumn('price_to', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'primary'   => false,
    ), 'Price to')
    ->addColumn('final_price_from', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'primary'   => false,
    ), 'Final price from')
    ->addColumn('final_price_to', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'primary'   => false,
    ), 'Final price to')
    ->addColumn('stock', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => false,
        'primary'   => false,
    ), 'Stock json')
    ->addColumn('option_price', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => false,
        'primary'   => false,
    ), 'Option price')
    ->addColumn('option_html', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => false,
        'primary'   => false,
    ), 'Option html');
$conn->createTable($table);

$installer->endSetup();