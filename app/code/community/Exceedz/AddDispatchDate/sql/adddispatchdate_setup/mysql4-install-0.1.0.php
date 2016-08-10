<?php

$installer = $this;
$installer->startSetup();
$installer->addAttribute(
        'order_item', 'dispatch_date', array(
    'type' => 'varchar',
    'nullable' => true,
    'default' => null,
    'grid' => false,
    'comment' => 'calculated dispatch date for the product'
        )
);

$installer->addAttribute(
        'quote_item', 'dispatch_date', array(
    'type' => 'varchar',
    'nullable' => true,
    'default' => null,
    'grid' => false,
    'comment' => 'calculated dispatch date for the product'
        )
);
$installer->endSetup();