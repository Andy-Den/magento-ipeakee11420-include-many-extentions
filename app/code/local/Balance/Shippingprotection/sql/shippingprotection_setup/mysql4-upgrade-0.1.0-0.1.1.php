<?php

$installer = $this;
$installer->startSetup();

$setup = new Mage_Catalog_Model_Resource_Setup('core_setup');
$setup->removeAttribute('catalog_product', 'product_protection');
$setup->addAttribute(
    'catalog_product',
    'product_protection',
    array(
        'group'           => 'General',
        'label'           => 'Product Protection',
        'global'          => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'type'            => 'int',
        'input'           => 'boolean',
        'source'          => 'eav/entity_attribute_source_table',
        'visible'         => true,
        'default'         => 0,
        'required'        => false,
        'apply_to'        => 'virtual',
        'is_configurable' => false,
        'position'        => 11,
    )
);

$setup_sales = new Mage_Sales_Model_Resource_Setup('core_setup');

$setup_sales->addAttribute('quote', 'bi_is_protection', array('type' => 'int'));
$setup_sales->addAttribute('quote', 'bi_protection_price', array('type' => 'decimal'));
$setup_sales->addAttribute('order', 'bi_is_protection', array('type' => 'int'));
$setup_sales->addAttribute('order', 'bi_protection_price', array('type' => 'decimal'));


$installer->endSetup();