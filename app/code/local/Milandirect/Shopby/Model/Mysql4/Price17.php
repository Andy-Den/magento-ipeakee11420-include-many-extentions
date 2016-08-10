<?php

/**
 * Use min of max price
 *
 * @package    Milandirect_Shopby
 * @author     Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2016 Balance
 */
class Milandirect_Shopby_Model_Mysql4_Price17 extends Amasty_Shopby_Model_Mysql4_Price17
{
    /**
     * @param Mage_Catalog_Model_Layer_Filter_Price $filter
     */
    protected function _computeMinMaxPriceFromDb($filter)
    {
        $select = clone $filter->getLayer()->getProductCollection()->getSelect();

        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::ORDER);

        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('catalog/product_collection');

        $priceExpression = $collection->getPriceExpression($select) . ' ' . $collection->getAdditionalPriceExpression($select);

        $select = $this->_removePriceFromSelect($select, $priceExpression);

        $sqlEndPart = ') * ' . $collection->getCurrencyRate() . ')';
        $select->columns('CEIL(MAX(' . $priceExpression . $sqlEndPart . ' as max_price');
        $select->columns('FLOOR(MIN(' . $priceExpression . $sqlEndPart . ' as min_price');
        $select->where($collection->getPriceExpression($select) . ' IS NOT NULL');

        $this->_maxMinPrice = $collection->getConnection()->fetchRow($select, array(), Zend_Db::FETCH_NUM);
    }
}