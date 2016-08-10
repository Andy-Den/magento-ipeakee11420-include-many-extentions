<?php
$installer = $this;
$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId('catalog_category');
$attributeSetId = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = 4;

$installer->addAttribute(
    'catalog_category',
    'custom_text_block',
    array(
        'type'     => 'text',
        'label'    => 'Custom Text Block',
        'input'    => 'textarea',
        'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'required' => false,
        'group'    => 'General Information',
    )
);

$installer->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'custom_text_block',
    '1000'
);

$attributeId = $installer->getAttributeId($entityTypeId, 'custom_text_block');

$defaultText = "";
$installer->run(
    "
INSERT INTO `{$installer->getTable('catalog_category_entity_text')}`
(`entity_type_id`, `attribute_id`, `entity_id`, `value`)
    SELECT '{$entityTypeId}', '{$attributeId}', `entity_id`, '{$defaultText}'
        FROM `{$installer->getTable('catalog_category_entity')}`;
"
);

$installer->endSetup();
