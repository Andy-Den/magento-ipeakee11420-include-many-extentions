<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('innobyte_product_questions/question')};
CREATE TABLE {$this->getTable('innobyte_product_questions/question')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `customer_name` varchar(60) NULL,
  `customer_email` varchar(60) NULL,
  `content` text NOT NULL default '',
  `visibility` smallint(6) NOT NULL default '0' COMMENT '0 - private : not visible on front; 1 - public: visible on front',
  `answer` text NOT NULL default '',
  `status` smallint(6) NOT NULL default '0' COMMENT '0 - not responsed; 1 - responsed',
  `votes` int(5) NOT NULL default '0',
  `created` datetime NULL,
  `product_id` int(10) unsigned NOT NULL,
  `product_name` varchar(360) NULL,
  `vendor_id` int(10) unsigned,
  `store_id` int(3) unsigned,
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_INNO_PRODUCT_QUESTION` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog/product')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('innobyte_product_questions/votes')};
CREATE TABLE {$this->getTable('innobyte_product_questions/votes')} (
  `id` INT(11) unsigned NOT NULL auto_increment,
  `question_id` INT(9) NOT NULL,
  `user_id` INT(9) NULL,
  `vote` TINYINT(1),
  `vote_date` DATETIME,
  `ip` VARCHAR(255), 
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_INNO_PRODUCT_VOTES` FOREIGN KEY (`question_id`) REFERENCES `{$installer->getTable('innobyte_product_question')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

$installer->endSetup();

Mage::getModel('core/config')->saveConfig('advanced/modules_disable_output/Innobyte_ProductQuestions', '1');