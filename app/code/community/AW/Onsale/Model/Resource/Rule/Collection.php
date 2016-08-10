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


class AW_Onsale_Model_Resource_Rule_Collection extends Mage_CatalogRule_Model_Mysql4_Rule_Collection
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('onsale/rule');
    }

    public function addStoreFilter($store)
    {
        $this
            ->getSelect()
            ->where("FIND_IN_SET(0, store_ids) OR FIND_IN_SET({$store}, store_ids)")
        ;
        return $this;
    }

    public function addActiveFilter()
    {
        $this
            ->getSelect()
            ->where("is_active = ?", 1)
        ;
        return $this;
    }
}