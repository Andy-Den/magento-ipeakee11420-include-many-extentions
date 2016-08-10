<?php

$installer = $this;

$installer->startSetup();
$installer->run("

DROP TABLE IF EXISTS {$this->getTable('megamenu_item_template')};

CREATE TABLE {$this->getTable('megamenu_item_template')} (
  `template_id` int(10) unsigned NOT NULL auto_increment,
  `menu_type` int unsigned,
  `name` varchar(255) NOT NULL default '',
  `filename` varchar(255) NOT NULL default '',
  PRIMARY KEY (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'menu_type', 'int unsigned NOT NULL default "0"');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'template_id', 'int unsigned NOT NULL default "0"');
//$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'category_ids', 'text NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'products', 'text NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'products_box_title', 'varchar(255) NOT NULL default "Products"');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'categories_box_title', 'varchar(255) NOT NULL default "Categories"');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'header', 'text NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'footer', 'text NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'featured_type', 'smallint(2) NOT NULL default "0"');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'featured_products', 'text NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'featured_products_box_title', 'varchar(255) NOT NULL default "Featured Products"');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'featured_categories', 'text NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'featured_categories_box_title', 'varchar(255) NOT NULL default "Featured Categories"');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'number_column', 'int unsigned');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'size_bar', 'int unsigned');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'border_size', 'int unsigned');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'background_color', 'text NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'border_color', 'text NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'title_color', 'text NOT NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'title_background_color', 'text NOT NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'title_font', 'text NOT NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'title_font_size', 'int unsigned default "0"');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'subtitle_color', 'text NOT NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'subtitle_font', 'text NOT NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'subtitle_font_size', 'int unsigned default "0"');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'link_color', 'text NOT NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'hover_color', 'text NOT NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'link_font', 'text NOT NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'link_font_size', 'int unsigned default "0"');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'text_color', 'text NOT NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'text_font', 'text NOT NULL default ""');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'text_font_size', 'int unsigned default "0"');
$installer->getConnection()->addColumn($installer->getTable('megamenu'), 'item_icon', 'text NULL default ""');


$data = array(
    array(
        'menu_type' => '1',
        'name' => 'Content Only 01',
        'filename' => 'default.phtml'
    ),
   
    array(
        'menu_type' => '2',
        'name' => 'Product Listing 01',
        'filename' => 'detailed_products.phtml'
    ),
    array(
        'menu_type' => '2',
        'name' => 'Product Listing 02',
        'filename' => 'general_products.phtml'
    ),
	
    array(
        'menu_type' => '3',
        'name' => 'Category Listing 01',
        'filename' => 'detailed_categories.phtml'
    ),
    array(
        'menu_type' => '3',
        'name' => 'Category Listing 02',
        'filename' => 'general_categories.phtml'
    ),
   
    array(
        'menu_type' => '4',
        'name' => 'Contact Form 01',
        'filename' => 'default.phtml'
    ),
    array(
        'menu_type' => '5',
        'name' => 'Group Meu Items 01',
        'filename' => 'default.phtml'
    ),
    
    array(
        'menu_type' => '6',
        'name' => 'Anchor Text 01',
        'filename' => 'default.phtml'
    )
);

$installer->getConnection()->insertMultiple($installer->getTable('megamenu_item_template'), $data);
	
$installer->endSetup();