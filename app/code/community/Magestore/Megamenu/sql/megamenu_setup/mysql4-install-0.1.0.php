<?php

$installer = $this;
$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('megamenu')};
CREATE TABLE {$this->getTable('megamenu')} (
    `megamenu_id` int(11) unsigned NOT NULL auto_increment,
    `name_menu` varchar(255) NOT NULL default '',   
    `status` smallint(6) NOT NULL default '0',
    `colum` int(11) NULL,
    `style_show` int(11) NULL,
    `categories` text NULL,
    `size_megamenu` int(11) NULL,
    `size_colum` int(11) NULL,
    `sort_order` int(11) NULL,
    `stores` text NULL,
    `link` text NULL,
    `colum_category` int (11) NULL,
    `size_category` int (11) NULL,    
    `code_template` mediumtext NULL,
    `created_time` datetime NULL,
    `update_time` datetime NULL,
    PRIMARY KEY (`megamenu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('megamenu_template')};
CREATE TABLE {$this->getTable('megamenu_template')}(
    `template_id` int(11) unsigned NOT NULL auto_increment,
    `name_template` varchar(255) NOT NULL default '',
    `code_template` mediumtext NULL,
    `description` varchar(255) NULL,
    `image` varchar(255) NULL,
    `created_time` datetime NULL,
    `update_time` datetime NULL,
    PRIMARY KEY (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");
$base = Mage::getBaseDir('media').DS.'import'.DS.'templatemenu.xml';
$xml = simplexml_load_file($base);
foreach($xml as $children)
  {   
    $chid = $children->children();
	$id = (int)$chid->template_id;
    $name = (string)$chid->name_template;
    $des = (string)$chid->description;
    $code = (string)$chid->code_template;
	$image = (string)$chid->name_image;
	$model = Mage::getModel('megamenu/template');
	$model->setData('name_template', $name);
	$model->setData('code_template', $code);
	$model->setData('description', $des);
	$model->setData('image', $image);
	$model->save();
	$path = Mage::helper('megamenu')->getPathImageImport($image);
	Mage::helper('megamenu')->ImportImage($image, $model->getId(), $path);
  }
$installer->endSetup();
