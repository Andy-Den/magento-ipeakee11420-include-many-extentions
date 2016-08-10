<?php
/**
 * @copyright   Copyright (c) 2009-2011 Amasty (http://www.amasty.com)
 */
class Amasty_Xcoupon_Model_Salesrule_Mysql4_Rule_Collection extends Mage_SalesRule_Model_Mysql4_Rule_Collection
{

//    public function setValidationFilter($websiteId, $customerGroupId, $couponCode = '', $now = null)
//    {
//        if (!$this->getFlag('validation_filter')) {
//
//            /* We need to overwrite joinLeft if coupon is applied */
//            $this->getSelect()->reset();
//            parent::_initSelect();
//
//            $this->addWebsiteGroupDateFilter($websiteId, $customerGroupId, $now);
//            $select = $this->getSelect();
//
//            $connection = $this->getConnection();
//            if (strlen($couponCode)) {
//                $select->joinLeft(
//                    array('rule_coupons' => $this->getTable('salesrule/coupon')),
//                    $connection->quoteInto(
//                        'main_table.rule_id = rule_coupons.rule_id AND main_table.coupon_type != ?',
//                        Mage_SalesRule_Model_Rule::COUPON_TYPE_NO_COUPON
//                    ),
//                    array('code')
//                );
//
//                $noCouponCondition = $connection->quoteInto(
//                    'main_table.coupon_type = ? ',
//                    Mage_SalesRule_Model_Rule::COUPON_TYPE_NO_COUPON
//                );
//
//                $orWhereConditions = array(
//                    $connection->quoteInto(
//                        '(main_table.coupon_type = ? AND rule_coupons.type = 0)',
//                        Mage_SalesRule_Model_Rule::COUPON_TYPE_AUTO
//                    ),
//                    $connection->quoteInto(
//                        '(main_table.coupon_type = ? AND main_table.use_auto_generation = 1 AND rule_coupons.type = 1)',
//                        Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC
//                    ),
//                    $connection->quoteInto(
//                        '(main_table.coupon_type = ? AND main_table.use_auto_generation = 0 AND rule_coupons.type = 0)',
//                        Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC
//                    ),
//                );
//                $orWhereCondition = implode(' OR ', $orWhereConditions);
//                $select->where(
//                    $noCouponCondition . ' OR ((' . $orWhereCondition . ') AND rule_coupons.code = ?)', $couponCode
//                );
//            } else {
//                $this->addFieldToFilter('main_table.coupon_type', Mage_SalesRule_Model_Rule::COUPON_TYPE_NO_COUPON);
//            }
//            $this->setOrder('sort_order', self::SORT_ORDER_ASC);
//            $this->setFlag('validation_filter', true);
//        }
//
//        return $this;
//    }

}