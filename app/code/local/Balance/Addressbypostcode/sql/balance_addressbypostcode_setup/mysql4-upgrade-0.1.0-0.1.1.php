<?php
$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `{$this->getTable('addressbypostcode/address')}`
    MODIFY COLUMN `locality` varchar(256) NOT NULL ,
    MODIFY COLUMN `comment` varchar(256)  NULL ,
    MODIFY COLUMN `category` varchar(256)  NULL
    ;
");
$installer->endSetup();