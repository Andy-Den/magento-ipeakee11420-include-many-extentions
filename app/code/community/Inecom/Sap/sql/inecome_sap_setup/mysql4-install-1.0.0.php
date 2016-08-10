<?php
/**
 * @package		Infortis_UltraMegamenu
 * @author		Infortis
 * @copyright	Copyright 2012 - 2013 Infortis
 */
$installer = $this;
$installer->startSetup();
$installer->run("
	DROP TABLE IF EXISTS {$this->getTable('sap_order_queue') };
	CREATE TABLE IF NOT EXISTS {$this->getTable('sap_order_queue') } (
	    `queue_id` int(10) unsigned NOT NULL auto_increment,
	    `order_id` int(11) NOT NULL,
	    `status` enum('delivered','failed','reported','pending') DEFAULT 'pending',
	    `calculate` varchar(50),
	    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `updated_at` timestamp NULL DEFAULT NULL,
	     PRIMARY KEY (`queue_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();