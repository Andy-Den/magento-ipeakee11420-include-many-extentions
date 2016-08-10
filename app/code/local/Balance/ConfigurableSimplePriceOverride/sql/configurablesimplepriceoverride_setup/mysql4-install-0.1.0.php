<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Balance
 * @package    ConfigurableSimplePriceOverride
 * @copyright  Copyright (c) 2011 Balance
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

$installer = Mage::getResourceModel('catalog/setup','default_setup');
$installer->startSetup();

// the attribute added will be displayed under the group/tab Special Attributes in product edit page
$installer->addAttribute('catalog_product', 'scpproductspecific', array(
    'group'         => 'Prices',
    'input'         => 'select',
    'type'          => 'int',
    'class'         => 'scpproductspecific_cssclass',
    'source'        => "eav/entity_attribute_source_boolean",
    'label'         => 'Disable Configurable Simple Price Override',
    'backend'       => '',
    'default'       => 1,
    'visible'       => 1,
    'required'      => 0,
    'user_defined'  => 1,
    'searchable'    => 0,
    'filterable'        => 0,
    'is_configurable'  => 0,
    'comparable'    => 0,
    'visible_on_front' => 0,
    'visible_in_advanced_search'  => 0,
    'is_html_allowed_on_front' => 0,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));
 
$installer->endSetup();