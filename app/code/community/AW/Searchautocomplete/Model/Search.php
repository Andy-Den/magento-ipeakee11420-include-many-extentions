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
 * This software is designed to work with Magento enterprise edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Searchautocomplete
 * @version    3.4.5
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Searchautocomplete_Model_Search extends Varien_Object
{
    public function search($searchedQuery, $storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }
        if (is_null($searchedQuery) || is_null($storeId)) {
            return null;
        }

        $productCollection = null;

        if (Mage::helper('searchautocomplete')->canUseADVSearch()) {
            try {
                $productCollection = $this->searchProductsAdvancedSearch($searchedQuery, $storeId);
            } catch (Exception $e) {
                return null;
            }
        } else {
            $searchableAttributes = explode(',', Mage::helper('searchautocomplete/config')->getInterfaceSearchableAttributes());
            $productIds = array();
            foreach ($searchableAttributes as $attributeId) {
                if (Mage::helper('searchautocomplete')->isFulltext($attributeId)) {
                    $productIds = array_merge($productIds, $this->searchProductsFulltext($searchedQuery, $storeId));
                } else {
                    $productIds = array_merge($productIds, $this->searchProducts($storeId));
                }
            }

            if (Mage::helper('searchautocomplete/config')->getInterfaceSearchByTags()) {
                $tagProductsIds = $this->searchByTags();
                $productIds = array_unique(array_merge($productIds, $tagProductsIds));
            }

            if (!(Mage::helper('searchautocomplete/config')->getInterfaceShowOutOfStockProducts())) {
                $productIds = $this->_getInStockProductIdsOnly($productIds);
            }

            if (!count($productIds)) {
                return null;
            }

            $productCollection = $this->_prepareCollection();
            $productCollection
                ->addFilterByIds($productIds)
                ->addStoreFilter($storeId)
                ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                ->setVisibility(array(
                    Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
                    Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
                ))
            ;
        }

        if (is_null($productCollection)) {
            return null;
        }
        $productCollection = $this->_postProcessCollection($productCollection);
        $productCollection->setOrder('name', Varien_Data_Collection::SORT_ORDER_ASC);
        $productCollection->setPageSize(Mage::helper('searchautocomplete/config')->getInterfaceShowProducts());
        return $productCollection;
    }

    public function searchProductsAdvancedSearch($searchedQuery, $storeId)
    {
        $result = Mage::getModel('awadvancedsearch/api')->catalogQuery($searchedQuery, $storeId);
        if ($result === false) {
            return null;
        }
        return $result;
    }

    public function searchProductsFulltext($searchedQuery, $storeId)
    {
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_read');
        $select = $connection->select();
        $fullTextTable = $resource->getTableName('catalogsearch/fulltext');
        $select->from($fullTextTable, 'product_id')
            ->where('store_id = ?',$storeId)
            ->where('data_index LIKE ?', "%{$searchedQuery}%");
        $result = $connection->fetchCol($select);
        return $result;
    }

    public function searchProducts($storeId)
    {
        $ids = array();
        $resource = Mage::getSingleton('core/resource');
        $db = $resource->getConnection('core_read');

        $searchedWords = Mage::helper('searchautocomplete')->getSearchedWords();
        $attributes = $this->_getSearchableAttributesTypes();

        if (count($attributes) == 0) {
            return $ids;
        }
        $entityTypeId = Mage::helper('searchautocomplete')->getEntityTypeId();
        foreach ($attributes as $tableName) {
            if ($tableName != 'static') {
                $select = $db->select();

                if ($tableName == 'int') {
                    $eaov = $resource->getTableName('eav/attribute_option_value');
                    $cpei = $resource->getTableName('catalog/product') . '_' . $tableName;

                    $select->from(array('cpei' => $cpei), array('cpei.entity_id'))
                        ->join(array('eaov' => $eaov), 'cpei.`value` = eaov.option_id', array())
                        ->where('cpei.entity_type_id=?', $entityTypeId)
                        ->where('cpei.store_id=0 OR cpei.store_id=?', $storeId)
                        ->where('cpei.attribute_id IN (' . implode(',', array_keys($attributes)) . ')');

                    foreach ($searchedWords as $value) {
                        $select ->where('eaov.`value` LIKE "%' . addslashes($value) . '%"');
                    }
                } else {
                    $select
                        ->distinct()
                        ->from($resource->getTableName('catalog/product') . '_' . $tableName, 'entity_id')
                        ->where('entity_type_id=?', Mage::helper('searchautocomplete')->getEntityTypeId())
                        ->where('store_id=0 OR store_id=?', $storeId)
                        ->where('attribute_id IN (' . implode(',', array_keys($attributes)) . ')');
                    foreach ($searchedWords as $value) {
                        $select->where('`value` LIKE "%' . addslashes($value) . '%"');
                    }
                }
                $ids = array_merge($ids, $db->fetchCol($select));
            }
            if ($tableName == 'static') {
                $select = $db->select();

                $select
                    ->distinct()
                    ->from($resource->getTableName('catalog/product'), 'entity_id')
                    ->where('entity_type_id=?', $entityTypeId);

                foreach ($searchedWords as $value) {
                    $select->where('`sku` LIKE "%' . addslashes($value) . '%"');
                }
                $ids = array_merge($ids, $db->fetchCol($select));
            }
        }
        return array_unique($ids);
    }

    public function searchByTags()
    {
        $searchedWords = Mage::helper('searchautocomplete')->getSearchedWords();
        $tagCollection = Mage::getResourceModel('tag/tag_collection');
        foreach ($searchedWords as $word) {
            $tagCollection->getSelect()->orWhere("`name` like ?", "%{$word}%");
        }

        $tagProductIds = array();
        foreach ($tagCollection as $tag) {
            $tagProductIds = array_merge($tagProductIds, $tag->getRelatedProductIds());
        }
        return $tagProductIds;
    }

    protected function _getSearchableAttributesTypes()
    {
        $attributes = array();
        $searchableAttributes = explode(',', Mage::helper('searchautocomplete/config')->getInterfaceSearchableAttributes());
        if (count($searchableAttributes) !== 0) {
            foreach ($searchableAttributes as $attributeId) {
                $attribute = Mage::getModel('eav/entity_attribute')->load($attributeId);
                if ($attribute->getId()) {
                    $attributes[$attributeId] = $attribute->getBackendType();
                }
            }
        }
        return $attributes;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('searchautocomplete/product');
        return $collection;
    }

    protected function _postProcessCollection($productCollection)
    {
        $productCollection
            ->addAttributeToSelect('*')
            ->addMinimalPrice()
            ->addFinalPrice()
            ->groupByAttribute('entity_id')
        ;
        return $productCollection;
    }

    /**
     * @param array $productIds
     *
     * @return array
     */
    protected function _getInStockProductIdsOnly($productIds)
    {
        if (!count($productIds)) {
            return array();
        }
        $resource = Mage::getSingleton('core/resource');
        $dbConnection = $resource->getConnection('core_read');
        $select = $dbConnection->select();
        $select
            ->from($resource->getTableName('cataloginventory/stock_status'), 'product_id')
            ->where('product_id IN ('.implode(',',$productIds).') AND stock_status = 1');
        $productIds = $dbConnection->fetchCol($select);
        return $productIds;
    }
}