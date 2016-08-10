<?php

/**
 * Rewrite Catalog from local to fix problem error log
 *
 * @category  Milandirect
 * @package   Milandirect_Catalog
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2016 Balance
 */
class Milandirect_Catalog_Model_Resource_Eav_Mysql4_Category_Flat extends Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Flat
{
    /**
     * Get design update data of parent categories
     *
     * @param Mage_Catalog_Model_Category $category category object
     * @return array
     */
    public function getDesignUpdateData($category)
    {
        $categories = array();
        $pathIds = array();
        foreach (array_reverse($category->getParentIds()) as $pathId) {
            if ($pathId == Mage::app()->getStore()->getRootCategoryId()) {
                $pathIds[] = $pathId;
                break;
            }
            $pathIds[] = $pathId;
        }
        $select = $this->_getReadAdapter()->select()
            ->from(
                array('main_table' => $this->getMainStoreTable($category->getStoreId())),
                array(
                    'main_table.entity_id',
                    'main_table.custom_design',
                    //'main_table.custom_design_apply',
                    'main_table.custom_design_from',
                    'main_table.custom_design_to',
                )
            )
            ->where('main_table.entity_id IN (?)', $pathIds)
            ->where('main_table.is_active = ?', '1')
            ->order('main_table.path ' . Varien_Db_Select::SQL_DESC);
        $result = $this->_getReadAdapter()->fetchAll($select);
        foreach ($result as $row) {
            $row['id'] = $row['entity_id'];
            $categories[$row['entity_id']] = Mage::getModel('catalog/category')->setData($row);
        }
        return $categories;
    }
}
