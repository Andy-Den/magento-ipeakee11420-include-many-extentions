<?php
$installer = $this;
$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('addressbypostcode/address')}`  (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `pcode` varchar(10) NOT NULL ,
    `locality` varchar(10) NOT NULL ,
    `state` varchar(10) NOT NULL ,
    `comment` varchar(50)  NULL ,
    `category` varchar(50)  NULL
) ENGINE = InnoDB ;
");
$installer->endSetup();