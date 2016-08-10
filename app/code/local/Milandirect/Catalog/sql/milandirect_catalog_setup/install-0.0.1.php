<?php
$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer->startSetup();
$installer->addAttribute('catalog_product', 'biggest_discount', array(
    'group'                     => 'General',
    'input'                     => 'select',
    'type'                      => 'int',
    'label'                     => 'Biggest Discount',
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'                   => 0,
    'required'                  => 0,
    'visible_on_front'          => 0,
    'is_html_allowed_on_front'  => 0,
    'is_configurable'           => 0,
    'searchable'                => 0,
    'filterable'                => 0,
    'comparable'                => 0,
    'used_for_sort_by'          => 1,
    'unique'                    => false,
    'user_defined'              => false,
    'default'           => 0,
    'is_user_defined'           => false,
    'used_in_product_listing'   => false
));
$installer->endSetup();