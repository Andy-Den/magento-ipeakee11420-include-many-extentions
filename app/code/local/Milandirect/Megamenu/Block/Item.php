<?php

/**
 * Override Milandirect_Megamenu to fix problem on get children category
 *
 * @category  Milandirect
 * @package   Milandirect_Megamenu
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */
class Milandirect_Megamenu_Block_Item extends Magestore_Megamenu_Block_Item
{
    /**
     * Get Children category
     * @param $category Varien_Object category level
     * @param $level    int category level
     * @return array
     */
    public function getChildrenCategoriesbByLevel($category, $level)
    {
        if ($level == 2) {
            if (count(get_class_methods($category->getChildrenCategories())) > 0) {
                $childrens = $category->getChildrenCategories()
                    ->addFieldToFilter('entity_id', array('in' => $this->getItem()->getCategoryIds()))
                    ->addFieldToFilter('entity_id', array('neq' => $category->getId()))
                    ->setOrder('position', 'ASC')
                    ->addAttributeToSelect('*');
            } else {
                $childrenIds = $category->getAllChildren();
                $childrenIds = explode(',', $childrenIds);
                $childrens = Mage::getResourceModel('catalog/category_collection')
                    ->addFieldToFilter('entity_id', array('in' => $childrenIds))
                    ->addFieldToFilter('entity_id', array('neq' => $category->getId()))
                    ->addAttributeToFilter('level', (int)($category->getLevel()+1))
                    ->setOrder('position', 'ASC')
                    ->addAttributeToSelect('*');
            }
        } elseif ($level == 3) {
            $childrenIds = $category->getAllChildren();
            $childrenIds = explode(',', $childrenIds);
            $childrenIds = array_intersect($childrenIds, $this->getItem()->getCategoryIds());
            $childrens = Mage::getResourceModel('catalog/category_collection')
                ->addFieldToFilter('entity_id', array('in' => $childrenIds))
                ->addFieldToFilter('entity_id', array('neq' => $category->getId()))
                ->setOrder('position', 'ASC')
                ->addAttributeToSelect('*');
        }
        return $childrens;
    }
}
