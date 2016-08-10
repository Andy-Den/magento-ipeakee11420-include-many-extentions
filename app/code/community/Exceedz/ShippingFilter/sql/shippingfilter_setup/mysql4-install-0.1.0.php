<?php
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$setup->addAttribute('catalog_product', 'product_shipping_methods', array(
	'group'           => 'Prices',
	'type'            => 'varchar',
	'label'           => 'Disable shipping methods for this product',
	'input'           => 'multiselect',
	'source'          => 'shippingfilter/config_source_shipping_methods',
	'backend'         => 'eav/entity_attribute_backend_array',
	'global'          => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
	'required'        => true,
	'default'         => '',
	'user_defined'    => 1,
	'required'        => 0,
));

$installer->endSetup();