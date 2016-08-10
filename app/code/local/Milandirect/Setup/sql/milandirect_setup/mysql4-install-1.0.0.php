<?php

$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->startSetup();

for($i=0; $i< 5; $i++ ) {
	$setup->addAttribute('catalog_product', 'custom_label_'.$i, array(
		'group'         => 'General',
		'input'         => 'text',
		'type'          => 'varchar',
		'label'         => 'Custome Label '.$i,
		'backend'       => '',
		'visible'       => 1,
		'required'      => 0,
		'user_defined' => 1,
		'searchable' => 1,
		'filterable' => 0,
		'comparable'    => 1,
		'visible_on_front' => 1,
		'visible_in_advanced_search'  => 0,
		'is_html_allowed_on_front' => 0,
		'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));
}

$installer->endSetup();