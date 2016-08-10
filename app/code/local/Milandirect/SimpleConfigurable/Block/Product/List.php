<?php

/**
 * Milandirect
 *
 * @category  Milandirect
 * @package   Milandirect_SimpleConfigurable
 * @copyright 2016 Balance Internet
 */
class Milandirect_SimpleConfigurable_Block_Product_List extends  Mage_Catalog_Block_Product_List
{
    /**
     * Default toolbar block name
     *
     * @var string
     */
    protected $_defaultToolbarBlock = 'catalog/product_list_toolbar';

    /**
     * Product Collection
     *
     * @var Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected $_productCollection;

    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $layer = $this->getLayer();
            /* @var $layer Mage_Catalog_Model_Layer */
            if ($this->getShowRootCategory()) {
                $this->setCategoryId(Mage::app()->getStore()->getRootCategoryId());
            }

            // if this is a product view page
            if (Mage::registry('product')) {
                // get collection of categories this product is associated with
                $categories = Mage::registry('product')->getCategoryCollection()
                    ->setPage(1, 1)
                    ->load();
                // if the product is associated with any category
                if ($categories->count()) {
                    // show products from this category
                    $this->setCategoryId(current($categories->getIterator()));
                }
            }

            $origCategory = null;
            if ($this->getCategoryId()) {
                $category = Mage::getModel('catalog/category')->load($this->getCategoryId());
                if ($category->getId()) {
                    $origCategory = $layer->getCurrentCategory();
                    $layer->setCurrentCategory($category);
                    $this->addModelTags($category);
                }
            }
            $this->_productCollection = $layer->getProductCollection();
            if (Mage::registry('simpleconfigurable') != 1) {
                Mage::unregister('simpleconfigurable');
                Mage::register('simpleconfigurable', 1);
                $this->_productCollection->joinTable(
                    array('simpleconfigurable' => 'simpleconfigurable/configurable'),
                    'entity_id=entity_id',
                    array(
                        'configurable_price_from'=>'simpleconfigurable.price_from',
                        'configurable_price_to'=>'simpleconfigurable.price_to',
                        'configurable_final_price_from'=>'simpleconfigurable.final_price_from',
                        'configurable_final_price_to'=>'simpleconfigurable.final_price_to',
                        'configurable_stock'=>'simpleconfigurable.stock',
                        'configurable_option_price'=>'simpleconfigurable.option_price',
                        'configurable_option_html'=>'simpleconfigurable.option_html',
                    ),
                    'simpleconfigurable.store_id='.Mage::app()->getStore()->getId(),
                    'left'
                );
            }
            $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

            if ($origCategory) {
                $layer->setCurrentCategory($origCategory);
            }
        }

        return $this->_productCollection;
    }
}
