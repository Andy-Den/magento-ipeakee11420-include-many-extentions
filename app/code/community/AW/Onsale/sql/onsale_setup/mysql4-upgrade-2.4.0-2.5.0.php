<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Onsale
 * @version    2.5.4
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

$installer = $this;
$installer->startSetup();
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->updateAttribute(
'catalog_product',
'aw_os_product_image',
'frontend_input_renderer',
'onsale/adminhtml_product_attribute_render_image'
);
$setup->updateAttribute(
'catalog_product',
'aw_os_category_image',
'frontend_input_renderer',
'onsale/adminhtml_product_attribute_render_image'
);
$installer->run("
    ALTER TABLE `{$this->getTable('onsale/rule')}` ADD `customer_group_ids` VARCHAR (255) NOT NULL AFTER `product_page_text`;
    ALTER TABLE `{$this->getTable('onsale/rule')}` ADD `store_ids` VARCHAR (255) NOT NULL AFTER `customer_group_ids`;
    UPDATE `{$this->getTable('onsale/rule')}` AS aor INNER JOIN
    (SELECT GROUP_CONCAT(customer_group_id) as group_ids, rule_id
    FROM `{$this->getTable('onsale/customer_group')}`) AS aocg ON aor.rule_id = aocg.rule_id
    SET aor.customer_group_ids = group_ids;
    UPDATE `{$this->getTable('onsale/rule')}` AS aor INNER JOIN
    (SELECT rule_id, (SELECT GROUP_CONCAT(store_id) FROM `{$this->getTable('core_store')}` AS cs WHERE cs.website_id IN (ow.website_id)) AS website_store_ids
    FROM `{$this->getTable('onsale/website')}` AS ow) AS ow ON aor.rule_id = ow.rule_id
    SET aor.store_ids = website_store_ids;
    DROP TABLE IF EXISTS `{$this->getTable('onsale/website')}`;
    DROP TABLE IF EXISTS `{$this->getTable('onsale/customer_group')}`;
    DROP TABLE IF EXISTS `{$this->getTable('onsale/rule_product')}`;
    CREATE TABLE IF NOT EXISTS {$this->getTable('onsale/rule_product')} (
      `rule_product_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Rule Product Id',
      `rule_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Rule Id',
      `from_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'From Time',
      `to_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'To time',
      `customer_group_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Customer Group Id',
      `product_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Product Id',
      `sort_order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Sort Order',
      `store_id` smallint(5) unsigned NOT NULL COMMENT 'Store Id',
      PRIMARY KEY (`rule_product_id`),
      UNIQUE KEY `EAA51B56FF092A0DCB795D1CEF812B7C` (`rule_id`,`from_time`,`to_time`,`store_id`,`customer_group_id`,`product_id`,`sort_order`),
      KEY `IDX_ONSALE_PRODUCT_RULE_ID` (`rule_id`),
      KEY `IDX_ONSALE_PRODUCT_CUSTOMER_GROUP_ID` (`customer_group_id`),
      KEY `IDX_ONSALE_PRODUCT_STORE_ID` (`store_id`),
      KEY `IDX_ONSALE_PRODUCT_FROM_TIME` (`from_time`),
      KEY `IDX_ONSALE_PRODUCT_TO_TIME` (`to_time`),
      KEY `IDX_ONSALE_PRODUCT_PRODUCT_ID` (`product_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='On Sale Rule Product';
");
$installer->endSetup();