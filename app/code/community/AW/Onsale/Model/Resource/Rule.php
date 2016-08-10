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


class AW_Onsale_Model_Resource_Rule extends Mage_Rule_Model_Mysql4_Rule
{
    /**
     * Store number of seconds in a day
     */
    const SECONDS_IN_DAY = 86400;

    /**
     * Initialize main table and table id field
     */
    protected function _construct()
    {
        $this->_init('onsale/rule', 'rule_id');
    }

    /**
     * After load
     *
     * @param Mage_Core_Model_Abstract $object
     */
    public function afterLoad(Mage_Core_Model_Abstract $object)
    {
        $this->_afterLoad($object);
    }

    /**
     * Add customer group ids and store ids to rule data after load
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return Mage_CatalogRule_Model_Resource_Rule
     */
    public function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $object
            ->setData('customer_group_ids', (array)explode(',', $object->getData('customer_group_ids')))
            ->setData('store_ids', (array)explode(',', $object->getData('store_ids')))
        ;
        return parent::_afterLoad($object);
    }

    public function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        parent::_beforeSave($object);
        if (is_array($object->getCustomerGroupIds())) {
            $object->setCustomerGroupIds(join(',', $object->getCustomerGroupIds()));
        }
        if (is_array($object->getStoreIds())) {
            $object->setStoreIds(join(',', $object->getStoreIds()));
        }
        if ($object->getData('from_date') instanceof Zend_Date) {
            $object->setData(
                'from_date', $object->getData('from_date')->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)
            );
        }
        if ($object->getData('to_date') instanceof Zend_Date) {
            $object->setData('to_date', $object->getData('to_date')->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
        }
        if ($object->getData('from_date') == '') {
            $object->setData('from_date', null);
        }
        if ($object->getData('to_date') == '') {
            $object->setData('to_date', null);
        }
        return $this;
    }

    /**
     * Update products which are matched for rule
     *
     * @param Mage_CatalogRule_Model_Rule $rule
     *
     * @return Mage_CatalogRule_Model_Resource_Rule
     */
    public function updateRuleProductData(AW_Onsale_Model_Rule $rule)
    {
        $ruleId = $rule->getId();
        $write = $this->_getWriteAdapter();
        $write->beginTransaction();

        if ($rule->getProductsFilter()) {
            $write->delete(
                $this->getTable('onsale/rule_product'), array(
                    'rule_id=?'         => $ruleId,
                    'product_id IN (?)' => $rule->getProductsFilter()
                )
            );
        } else {
            $write->delete($this->getTable('onsale/rule_product'), $write->quoteInto('rule_id=?', $ruleId));
        }

        if (!$rule->getIsActive()) {
            $write->commit();
            return $this;
        }

        $storeIds = $rule->getStoreIds();

        if (count($storeIds) == 0) {
            return $this;
        }

        Varien_Profiler::start('__MATCH_PRODUCTS__');
        $result = $rule->getMatchingProductIds();
        Varien_Profiler::stop('__MATCH_PRODUCTS__');

        $customerGroupIds = $rule->getCustomerGroupIds();
        if (!is_array($customerGroupIds)) {
            $customerGroupIds = @explode(',', $customerGroupIds);
        }
        $fromTime = strtotime($rule->getFromDate());
        $toTime = strtotime($rule->getToDate());
        $toTime = $toTime ? ($toTime + self::SECONDS_IN_DAY - 1) : 0;
        $sortOrder = (int) $rule->getSortOrder();

        $rows = array();
        try {
            foreach ($result as $storeId => $productIds) {
                foreach ($productIds as $productId) {
                    foreach ($customerGroupIds as $customerGroupId) {
                        $rows[] = array(
                            'rule_id' => $ruleId,
                            'from_time' => $fromTime,
                            'to_time' => $toTime,
                            'store_id' => $storeId,
                            'customer_group_id' => $customerGroupId,
                            'product_id' => $productId,
                            'sort_order' => $sortOrder,
                        );

                        if (count($rows) == 1000) {
                            $write->insertMultiple($this->getTable('onsale/rule_product'), $rows);
                            $rows = array();
                        }
                    }
                }
            }
            if (!empty($rows)) {
                $write->insertMultiple($this->getTable('onsale/rule_product'), $rows);
            }
            $write->commit();
        } catch (Exception $e) {
            $write->rollback();
            throw $e;
        }
        return $this;
    }

    /**
     * Get all product ids matched for rule
     *
     * @param int $ruleId
     *
     * @return array
     */
    public function getRuleProductIds($ruleId)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()->from($this->getTable('onsale/rule_product'), 'product_id')
            ->where('rule_id=?', $ruleId)
        ;
        return $read->fetchCol($select);
    }

    /**
     * Get active rule data based on few filters
     *
     * @param int|string $date
     * @param int $storeId
     * @param int $customerGroupId
     * @param int $productId
     * @return array
     */
    public function getRulesFromProduct($date, $storeId, $customerGroupId, $productId)
    {
        $adapter = $this->_getReadAdapter();
        if (is_string($date)) {
            $date = strtotime($date);
        }
        $select = $adapter->select()
            ->from($this->getTable('onsale/rule_product'))
            ->where('store_id = ?', $storeId)
            ->where('customer_group_id = ?', $customerGroupId)
            ->where('product_id = ?', $productId)
            ->where('from_time = 0 or from_time < ?', $date)
            ->where('to_time = 0 or to_time > ?', $date)
        ;
        return $adapter->fetchAll($select);
    }

    public function applyToProduct($rule, $product)
    {
        if (!$rule->getIsActive()) {
            return $this;
        }
        $ruleId = $rule->getId();
        $productId = $product->getId();

        $write = $this->_getWriteAdapter();
        $write->beginTransaction();

        $write->delete($this->getTable('onsale/rule_product'), array(
            $write->quoteInto('rule_id=?', $ruleId),
            $write->quoteInto('product_id=?', $productId),
        ));

        $customerGroupIds = $rule->getCustomerGroupIds();
        $fromTime = strtotime($rule->getFromDate());
        $toTime = strtotime($rule->getToDate());
        $toTime = $toTime ? $toTime + self::SECONDS_IN_DAY - 1 : 0;
        $sortOrder = (int) $rule->getSortOrder();
        if (null === $product->getData('qty')) {
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
            $product->setData('qty', $stockItem->getQty());
        }
        $rows = array();
        $storeCollection = Mage::getModel('core/store')->getCollection();
        try {
            if ($rule->validate($product)) {
                foreach ($storeCollection as $storeModel) {
                    $_storeIds = $rule->getStoreIds();
                    if (is_string($_storeIds)) {
                        $_storeIds = @explode(',', $rule->getStoreIds());
                    }
                    if (null === $_storeIds || !is_array($_storeIds)) {
                        $_storeIds = array();
                    }
                    if ((!in_array($storeModel->getId(), $_storeIds)
                        && !in_array(0, $_storeIds)) == TRUE
                    ) {
                        continue;
                    }
                    foreach ($customerGroupIds as $customerGroupId) {
                        $rows[] = array(
                            'rule_id'           => $ruleId,
                            'from_time'         => $fromTime,
                            'to_time'           => $toTime,
                            'store_id'          => $storeModel->getId(),
                            'customer_group_id' => $customerGroupId,
                            'product_id'        => $productId,
                            'sort_order'        => $sortOrder,
                        );
                        if (count($rows) == 1000) {
                            $write->insertMultiple($this->getTable('onsale/rule_product'), $rows);
                            $rows = array();
                        }
                    }
                }
            }
            if (!empty($rows)) {
                $write->insertMultiple($this->getTable('onsale/rule_product'), $rows);
            }
        } catch (Exception $e) {
            $write->rollback();
            throw $e;
        }
        $write->commit();
        return $this;
    }
}