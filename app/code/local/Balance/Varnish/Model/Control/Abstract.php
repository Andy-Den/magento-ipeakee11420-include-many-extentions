<?php

class Balance_Varnish_Model_Control_Abstract
{
    protected $_helperName;

    /**
     * Retrieve adminhtml session model object
     *
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    /**
     * Returns true if Varnish PageCache enabled and Purge Product config option set to 1
     *
     * @return bool
     */
    protected function _canPurge()
    {
        if (!$this->_helperName) {
            return false;
        }

        return Mage::helper($this->_helperName)->canPurge();
    }

    /**
     * Get Varnish control model
     *
     * @return Balance_Varnish_Model_Control
     */
    protected function _getCacheControl()
    {
        return Mage::helper('varnish')->getCacheControl();
    }

    /**
     * Returns domain list for store
     *
     * @return array
     */
    protected function _getStoreDomainList()
    {
        return Mage::helper('varnish/cache')->getStoreDomainList();
    }

    /**
     * Get url rewrite collection
     *
     * @return Balance_Varnish_Model_Resource_Mysql4_Core_Url_Rewrite_Collection
     */
    protected function _getUrlRewriteCollection()
    {
        return Mage::getResourceModel('varnish/core_url_rewrite_collection');
    }

    /**
     * Get product relation collection
     *
     * @return Balance_Varnish_Model_Resource_Mysql4_Catalog_Product_Relation_Collection
     */
    protected function _getProductRelationCollection()
    {
        return Mage::getResourceModel('varnish/catalog_product_relation_collection');
    }

    /**
     * Get catalog category product relation collection
     *
     * @return Balance_Varnish_Model_Resource_Mysql4_Catalog_Product_Relation_Collection
     */
    protected function _getCategoryProductRelationCollection()
    {
        return Mage::getResourceModel('varnish/catalog_category_product_collection');
    }
}
