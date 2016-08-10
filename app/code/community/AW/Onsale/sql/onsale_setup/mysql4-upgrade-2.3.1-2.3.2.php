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

if (!function_exists('updateAttribute')) {
    function updateAttribute(Mage_Eav_Model_Entity_Setup $setup, $entityTypeId, $code, $key, $value)
    {
        $id = $setup->getAttribute($entityTypeId, $code, 'attribute_id');
        $setup->updateAttribute($entityTypeId, $id, $key, $value);
    }
}

updateAttribute($setup, 'catalog_product', 'created_at', 'used_in_product_listing', '1');

$installer->endSetup();