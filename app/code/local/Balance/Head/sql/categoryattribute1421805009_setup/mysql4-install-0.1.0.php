<?php
$installer = $this;
$installer->startSetup();


$installer->addAttribute("catalog_category", "meta_robots",  array(
    "type"     => "int",
    "backend"  => "",
    "frontend" => "",
    "label"    => "Meta Robots",
    "input"    => "select",
    "class"    => "",
    "source"   => "head/eav_entity_attribute_source_categoryoptions14218050090",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    "visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "default" => "INDEX, FOLLOW",
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
	
    "visible_on_front"  => false,
    "unique"     => false,
    "note"       => "Add to head site"

	));
$installer->endSetup();
	 