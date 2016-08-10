<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Balance
 * @package    ConfigurableSimplePriceOverride
 * @copyright  Copyright (c) 2011 Balance
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

class Balance_ConfigurableSimplePriceOverride_Model_Catalog_Resource_Eav_Mysql4_Product_Indexer_Price_Configurable extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price_Configurable {

    protected function _isManageStock() {
        return Mage::getStoreConfigFlag(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
    }

    protected function _applyConfigurableOption() {
        return $this;
    }

    /**
     * Prepare Query to Add Lowest Price Data to product index price table
     * 
     * @see Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price_Configurable::_prepareFinalPriceData()
     */
    protected function _prepareFinalPriceData($entityIds = null) {

        $moduleStatus = Mage::helper('configurablesimplepriceoverride')->checkModuelGlobalStatus();
        if ($moduleStatus) {


            $this->_prepareDefaultFinalPriceTable();

            $write = $this->_getWriteAdapter();
            $select = $write->select()
                    ->from(
                            array('e' => $this->getTable('catalog/product')), array())
                    ->joinLeft(
                            array('l' => $this->getTable('catalog/product_super_link')), 'l.parent_id = e.entity_id', array())
                    ->join(
                            array('ce' => $this->getTable('catalog/product')), 'ce.entity_id = l.product_id', array())
                    ->join(
                            array('cg' => $this->getTable('customer/customer_group')), '', array())
                    ->join(
                            array('pi' => $this->getIdxTable()), 'ce.entity_id = pi.entity_id', array())
                    ->join(
                            array('cw' => $this->getTable('core/website')), 'pi.website_id = cw.website_id', array())
                    ->join(
                            array('cwd' => $this->_getWebsiteDateTable()), 'cw.website_id = cwd.website_id', array())
                    ->join(
                            array('csg' => $this->getTable('core/store_group')), 'csg.website_id = cw.website_id AND cw.default_group_id = csg.group_id', array())
                    ->join(
                            array('cs' => $this->getTable('core/store')), 'csg.default_store_id = cs.store_id AND cs.store_id != 0', array())
                    ->joinLeft(
                            array('tp' => $this->_getTierPriceIndexTable()), 'tp.entity_id = e.entity_id AND tp.website_id = cw.website_id'
                            . ' AND tp.customer_group_id = cg.customer_group_id', array())
                    ->join(
                            array('cis' => $this->getTable('cataloginventory/stock')), '', array())
                    ->joinLeft(
                            array('cisi' => $this->getTable('cataloginventory/stock_item')), 'cisi.stock_id = cis.stock_id AND cisi.product_id = ce.entity_id', array())
                    ->where('e.type_id=?', $this->getTypeId()); ## is this one needed?



            $productStatusExpr = $this->_addAttributeToSelect($select, 'status', 'ce.entity_id', 'cs.store_id');

            if ($this->_isManageStock()) {
                $stockStatusExpr = new Zend_Db_Expr('IF(cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 0,' . ' 1, cisi.is_in_stock)');
            } else {
                $stockStatusExpr = new Zend_Db_Expr('IF(cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 1,' . 'cisi.is_in_stock, 1)');
            }
            $isInStockExpr = new Zend_Db_Expr("IF({$stockStatusExpr}, 1, 0)");

            $isValidChildProductExpr = new Zend_Db_Expr("{$productStatusExpr}");

            //$prodstatusCond = $write->quoteInto('=?', 1);
            $productStatusOverExpr = $this->_addAttributeToSelect($select, 'scpproductspecific', 'e.entity_id', 'cs.store_id');

            $price = $this->_addAttributeToSelect($select, 'price', 'e.entity_id', 'cs.store_id');
            $specialPrice = $this->_addAttributeToSelect($select, 'special_price', 'e.entity_id', 'cs.store_id');
            $specialFrom = $this->_addAttributeToSelect($select, 'special_from_date', 'e.entity_id', 'cs.store_id');
            $specialTo = $this->_addAttributeToSelect($select, 'special_to_date', 'e.entity_id', 'cs.store_id');
            $currentDate = $write->getDatePartSql('cwd.website_date');
            // $groupPrice     = $write->getCheckSql('gp.price IS NULL', "{$price}", 'gp.price');

            $specialFromDate = $write->getDatePartSql($specialFrom);
            $specialToDate = $write->getDatePartSql($specialTo);

            $specialFromUse = $write->getCheckSql("{$specialFromDate} <= {$currentDate}", '1', '0');
            $specialToUse = $write->getCheckSql("{$specialToDate} >= {$currentDate}", '1', '0');
            $specialFromHas = $write->getCheckSql("{$specialFrom} IS NULL", '1', "{$specialFromUse}");
            $specialToHas = $write->getCheckSql("{$specialTo} IS NULL", '1', "{$specialToUse}");
            $finalPrice = $write->getCheckSql("{$specialFromHas} > 0 AND {$specialToHas} > 0"
                    . " AND {$specialPrice} < {$price}", $specialPrice, $price);
            //   $finalPrice         = $write->getCheckSql("{$groupPrice} < {$finalPrice}", $groupPrice, $finalPrice);
            $finaleOverOPrice = $write->getCheckSql("{$productStatusOverExpr} !=1 OR {$productStatusOverExpr}  IS NULL", 'pi.price', "{$price}");

            $finaleOverSPrice = $write->getCheckSql("{$productStatusOverExpr} !=1 OR {$productStatusOverExpr}  IS NULL", 'pi.final_price', "{$finalPrice}");



            $select->columns(array(
                'entity_id' => new Zend_Db_Expr('e.entity_id'),
                'customer_group_id' => new Zend_Db_Expr('pi.customer_group_id'),
                'website_id' => new Zend_Db_Expr('cw.website_id'),
                'tax_class_id' => new Zend_Db_Expr('pi.tax_class_id'),
                'orig_price' => $finaleOverOPrice,
                'price' => $finaleOverSPrice,
                'min_price' => $finaleOverSPrice,
                'max_price' => $finaleOverSPrice,
                'tier_price' => new Zend_Db_Expr('tp.min_price'),
                //  'tier_price' => new Zend_Db_Expr('pi.tier_price'),
                'base_tier' => new Zend_Db_Expr('pi.tier_price'),
                'group_price' => new Zend_Db_Expr('pi.tier_price'),
                'base_group_price' => new Zend_Db_Expr('pi.tier_price'),
            ));

            # Mage::log("SCP Price inner query: " . $select->__toString());

            if (!is_null($entityIds)) {

                # Mage::log("SCP Price inner query: " . var_dump($entityIds));

                $select->where('e.entity_id IN(?)', $entityIds);
            }


            $sortExpr = new Zend_Db_Expr("${isInStockExpr} DESC, pi.final_price ASC, pi.price ASC");
            $select->order($sortExpr);

            /**
             * Add additional external limitation
             */
            Mage::dispatchEvent('prepare_catalog_product_index_select', array(
                'select' => $select,
                'entity_field' => new Zend_Db_Expr('e.entity_id'),
                'website_field' => new Zend_Db_Expr('cw.website_id'),
                'store_field' => new Zend_Db_Expr('cs.store_id')
            ));



            $outerSelect = $write->select()
                    ->from(array("inner" => $select), 'entity_id')
                    ->group(array('inner.entity_id', 'inner.customer_group_id', 'inner.website_id'));

            $outerSelect->columns(array(
                'customer_group_id',
                'website_id',
                'tax_class_id',
                'orig_price',
                'price',
                'min_price',
                'max_price' => new Zend_Db_Expr('MAX(inner.max_price)'),
                'tier_price',
                'base_tier',
                'group_price',
                'base_group_price',
                    #'base_tier',
                    #'child_entity_id'
            ));

            $query = $outerSelect->insertFromSelect($this->_getDefaultFinalPriceTable());

            $write->query($query);


            return $this;
        } else {
            return parent::_prepareFinalPriceData($entityIds = null);
        }
    }

    protected function _getWebsiteDateTable() {
        return $this->getTable('catalog/product_index_website');
    }

    protected function _getTierPriceIndexTable() {
        return $this->getTable('catalog/product_index_tier_price');
    }

}