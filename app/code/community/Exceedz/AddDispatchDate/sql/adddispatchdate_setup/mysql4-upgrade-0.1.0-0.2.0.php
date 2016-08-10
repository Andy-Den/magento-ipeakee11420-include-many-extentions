<?php
$installer = $this;
$installer->startSetup();

$installer->addAttribute(
		'order_item', 'dispatch_note', array(
				'type' => 'varchar',
				'nullable' => true,
				'default' => null,
				'grid' => false,
				'comment' => 'dispatch note for the item'
		)
);

$installer->addAttribute(
		'quote_item', 'dispatch_note', array(
				'type' => 'varchar',
				'nullable' => true,
				'default' => null,
				'grid' => false,
				'comment' => 'dispatch note for the item'
		)
);
$installer->endSetup();