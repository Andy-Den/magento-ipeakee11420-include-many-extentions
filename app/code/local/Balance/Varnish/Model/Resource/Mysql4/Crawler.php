<?php
/**
 *  extension for Magento
 *
 * Long description of this file (if any...)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Balance Varnish module to newer versions in the future.
 * If you wish to customize the Balance Varnish module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Balance
 * @package    Balance_Varnish
 * @copyright  Copyright (C) 2013
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Short description of the class
 *
 * Long description of the class (if any...)
 *
 * @category   Balance
 * @package    Balance_Varnish
 * @subpackage Model
 * @author     Richard Cai <richard@balanceinternet.com.au>
 */
class Balance_Varnish_Model_Resource_Mysql4_Crawler extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_use_core_url = true;
    protected $_attributeIds = array();

    protected function _construct()
    {
        $version = explode('.', Mage::getVersion());
        if ($version[1] >= 13) {
            $this->_init('enterprise_urlrewrite/url_rewrite', 'url_rewrite_id');
            $this->_use_core_url = false;
        } else {
            $this->_init('core/url_rewrite', 'url_rewrite_id');
            $this->_use_core_url = true;
        }

        $attributeModel = Mage::getModel('eav/entity_attribute');
        $this->_attributeIds['category'] = array(
            'is_active' => $attributeModel->loadByCode('catalog_category', 'is_active')->getAttributeId()
        );
        $this->_attributeIds['product'] = array(
            'status'     => $attributeModel->loadByCode('catalog_product', 'status')->getAttributeId(),
            'visibility' => $attributeModel->loadByCode('catalog_product', 'visibility')->getAttributeId()
        );
    }

    /**
     * Returns core_url_rewrite query statement
     *
     * @param int $storeId
     *
     * @return Zend_Db_Statement
     */
    public function getUrlStmt($storeId)
    {
        if ($this->_use_core_url) {
            $select = $this->_getConnection('read');
            $query = $select->select()
                ->from(
                    $this->getTable('core/url_rewrite'),
                    array('store_id', 'request_path')
                )
                ->where('store_id=?', $storeId)
                ->where('is_system=1');

            $result = $this->_getReadAdapter()->query($query);
            $select->closeConnection();
            return $result;

        } else {
            $select = $this->_getConnection('read');
            $query = $select->select()
                ->from(
                    $this->getTable('enterprise_urlrewrite/url_rewrite'),
                    array('request_path')
                );

            $result = $this->_getReadAdapter()->query($query);
            $select->closeConnection();
            return $result;
        }
    }

    /**
     * Retrieve statement for category pages
     *
     * @param unknown_type $storeId
     *
     * @return Zend_Db_Statement
     */
    public function getCategoryStatement($storeId)
    {
        if ($this->_use_core_url) {
            $select = $this->_getConnection('read');
            $query = $select->select()
                ->from(
                    array('u' => $this->getTable('core/url_rewrite')),
                    array('u.store_id', 'u.request_path')
                )
                ->joinLeft(
                    array('ei' => $this->getValueTable('catalog/category', 'int')),
                    'u.category_id=ei.entity_id AND ei.attribute_id=' . $this->_attributeIds['category']['is_active']
                    . ' AND ei.value=1 AND u.store_id=ei.store_id',
                    array()
                )
                ->joinLeft(
                    array('ei0' => $this->getValueTable('catalog/category', 'int')),
                    'u.category_id=ei0.entity_id AND ei0.attribute_id=' . $this->_attributeIds['category']['is_active']
                    . ' AND ei0.value=1 AND ei0.store_id=0',
                    array()
                )
                ->where('u.store_id=?', $storeId)
                ->where('u.is_system=1')
                ->where("u.target_path LIKE 'catalog/category/view/id/%'")
                ->where('u.category_id IS NOT NULL')
                ->where('IFNULL(ei.entity_id, ei0.entity_id) IS NOT NULL');

            $result = $this->_getReadAdapter()->query($query);
            $select->closeConnection();
            return $result;
        } else {
            $select = $this->_getConnection('read');
            $query = $select->select()
                ->from(
                    array('u' => $this->getTable('enterprise_urlrewrite/url_rewrite')),
                    array('u.request_path')
                )
                ->joinLeft(
                    array('ei' => $this->getValueTable('catalog/category', 'int')),
                    'CAST(REPLACE(u.target_path, \'catalog/category/view/id/\', \'\') AS UNSIGNED)=ei.entity_id AND ei.attribute_id='
                    . $this->_attributeIds['category']['is_active'] . ' AND ei.value=1 AND ei.store_id=' . $storeId
                )
                ->joinLeft(
                    array('ei0' => $this->getValueTable('catalog/category', 'int')),
                    'CAST(REPLACE(u.target_path, \'catalog/category/view/id/\', \'\') AS UNSIGNED)=ei0.entity_id AND ei0.attribute_id='
                    . $this->_attributeIds['category']['is_active'] . ' AND ei0.value=1 AND ei0.store_id=0',
                    array()
                )
                ->where("u.target_path LIKE 'catalog/category/view/id/%'")
                ->where('IFNULL(ei.entity_id, ei0.entity_id) IS NOT NULL');
            $result = $this->_getReadAdapter()->query($query);
            $select->closeConnection();
            return $result;
        }
//                Mage::log($storeId, Zend_Log::DEBUG, Balance_Varnish_Model_Crawler::LOG_NAME);
//        Mage::log($select->__toString(), Zend_Log::DEBUG, Balance_Varnish_Model_Crawler::LOG_NAME);


    }

    /**
     *
     * @param int $storeId
     *
     * @return Zend_Db_Statement
     */
    public function getProductStatement($storeId)
    {
        //Mage_Catalog_Model_Product_Status::STATUS_ENABLED
        //Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE

        if ($this->_use_core_url) {
            $select = $this->_getConnection('read');
            $query = $select->select()
                ->from(
                    array('u' => $this->getTable('core/url_rewrite')),
                    array('u.store_id', 'u.request_path')
                )
                ->joinLeft(
                    array('ei' => $this->getValueTable('catalog/product', 'int')),
                    'u.product_id=ei.entity_id AND ei.attribute_id=' . $this->_attributeIds['product']['status']
                    . ' AND ei.value=' . Mage_Catalog_Model_Product_Status::STATUS_ENABLED
                    . ' AND ei.store_id=u.store_id',
                    array()
                )
                ->joinLeft(
                    array('ei0' => $this->getValueTable('catalog/product', 'int')),
                    'u.product_id=ei0.entity_id AND ei0.attribute_id=' . $this->_attributeIds['product']['status']
                    . ' AND ei0.value=' . Mage_Catalog_Model_Product_Status::STATUS_ENABLED . ' AND ei0.store_id=0',
                    array()
                )
                ->joinLeft(
                    array('ev' => $this->getValueTable('catalog/product', 'int')),
                    'u.product_id=ev.entity_id AND ev.attribute_id=' . $this->_attributeIds['product']['visibility']
                    . ' AND ev.value!=' . Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE
                    . ' AND ev.store_id=u.store_id',
                    array()
                )
                ->joinLeft(
                    array('ev0' => $this->getValueTable('catalog/product', 'int')),
                    'u.product_id=ev0.entity_id AND ev0.attribute_id=' . $this->_attributeIds['product']['visibility']
                    . ' AND ev0.value!=' . Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE
                    . ' AND ev0.store_id=0',
                    array()
                )
                ->where('u.store_id=?', $storeId)
                ->where('u.is_system=1')
                ->where('u.product_id IS NOT NULL')
                ->where('IFNULL(ei.entity_id, ei0.entity_id) IS NOT NULL')
                ->where('IFNULL(ev.entity_id, ev0.entity_id) IS NOT NULL');

            $result = $this->_getReadAdapter()->query($query);
            $select->closeConnection();
            return $result;

        } else {
            $select = $this->_getConnection('read');
            $query = $select->select()
                ->from(
                    array('u' => $this->getTable('enterprise_urlrewrite/url_rewrite')),
                    array('u.request_path')
                )
                ->joinLeft(
                    array('ei' => $this->getValueTable('catalog/product', 'int')),
                    'CAST(REPLACE(u.target_path, \'catalog/product/view/id/\', \'\') AS UNSIGNED)=ei.entity_id AND ei.attribute_id='
                    . $this->_attributeIds['product']['status'] . ' AND ei.value='
                    . Mage_Catalog_Model_Product_Status::STATUS_ENABLED . ' AND ei.store_id=' . $storeId,
                    array()
                )
                ->joinLeft(
                    array('ei0' => $this->getValueTable('catalog/product', 'int')),
                    'CAST(REPLACE(u.target_path, \'catalog/product/view/id/\', \'\') AS UNSIGNED)=ei0.entity_id AND ei0.attribute_id='
                    . $this->_attributeIds['product']['status'] . ' AND ei0.value='
                    . Mage_Catalog_Model_Product_Status::STATUS_ENABLED . ' AND ei0.store_id=0',
                    array()
                )
                ->joinLeft(
                    array('ev' => $this->getValueTable('catalog/product', 'int')),
                    'CAST(REPLACE(u.target_path, \'catalog/product/view/id/\', \'\') AS UNSIGNED)=ev.entity_id AND ev.attribute_id='
                    . $this->_attributeIds['product']['visibility'] . ' AND ev.value!='
                    . Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE . ' AND ev.store_id=' . $storeId,
                    array()
                )
                ->joinLeft(
                    array('ev0' => $this->getValueTable('catalog/product', 'int')),
                    'CAST(REPLACE(u.target_path, \'catalog/product/view/id/\', \'\') AS UNSIGNED)=ev0.entity_id AND ev0.attribute_id='
                    . $this->_attributeIds['product']['visibility'] . ' AND ev0.value!='
                    . Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE . ' AND ev0.store_id=0',
                    array()
                )
                ->where("u.target_path LIKE 'catalog/product/view/id/%'")
                ->where('IFNULL(ei.entity_id, ei0.entity_id) IS NOT NULL')
                ->where('IFNULL(ev.entity_id, ev0.entity_id) IS NOT NULL');

            $result = $this->_getReadAdapter()->query($query);
            $select->closeConnection();
            return $result;

        }
//                Mage::log($storeId, Zend_Log::DEBUG, Balance_Varnish_Model_Crawler::LOG_NAME);
//        Mage::log($select->__toString(), Zend_Log::DEBUG, Balance_Varnish_Model_Crawler::LOG_NAME);
    }

}

