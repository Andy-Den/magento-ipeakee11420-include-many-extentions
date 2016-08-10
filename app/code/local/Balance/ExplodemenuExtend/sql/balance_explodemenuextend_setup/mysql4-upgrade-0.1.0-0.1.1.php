<?php
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
//$setup->removeAttribute('catalog_category', 'width_menu');
$setup->addAttribute(
    'catalog_category',
    'width_menu',
    array(
        'group'        => 'General Information',
        'type'         => 'int',
        'label'        => 'Width of menu',
        'input'        => 'text',
        'visible'      => true,
        'required'     => false,
        'user_defined' => true,
        'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'default'      => '',
        'note'         => 'Only apply for top menu. If you don\'t set, width of menu depend on text of menu.',
    )
);
