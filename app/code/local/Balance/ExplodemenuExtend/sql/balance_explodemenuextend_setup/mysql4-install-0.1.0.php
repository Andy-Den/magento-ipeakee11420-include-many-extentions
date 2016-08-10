<?php
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
//$setup->removeAttribute('catalog_category', 'second_row');
$setup->addAttribute(
    'catalog_category',
    'second_row',
    array(
        'group'        => 'General Information',
        'type'         => 'int',
        'label'        => 'Top menu set in second row',
        'input'        => 'select',
        'source'       => 'eav/entity_attribute_source_boolean',
        'visible'      => true,
        'required'     => false,
        'user_defined' => true,
        'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'default'      => '',
        'note'         => 'Only apply for top menu. "Yes" to set menu in second row.',
    )
);
