<?php
/**
* @copyright Amasty.
*/
$this->startSetup();

$this->run("
ALTER TABLE `{$this->getTable('amshiprestriction/rule')}`  ADD `days` varchar(255) NOT NULL default '' AFTER `name`;
"); 

$this->endSetup();