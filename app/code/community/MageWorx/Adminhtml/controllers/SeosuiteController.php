<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_SeoSuite
 * @copyright  Copyright (c) 2012 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * SEO Suite extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoSuite
 * @author     MageWorx Dev Team
 */

class MageWorx_Adminhtml_SeosuiteController extends Mage_Adminhtml_Controller_Action {
    
    private $_product;        
    
    public function applyUrlAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function runApplyUrlAction() {
        @ini_set('max_execution_time', 1800);
        @ini_set('memory_limit', 734003200);
        $limit = 50;        
        $current = intval($this->getRequest()->getParam('current', 0));
        $reindex = $this->getRequest()->getParam('reindex', '');        
        $result = array();
        
        if ($reindex) {
            // make reindex
            $catalogUrlModel = Mage::getModel('catalog/url');
            $stores = $catalogUrlModel->getStores();
            $total = count($stores);
            
            if ($reindex=='start') {
                if ($this->_getTotalProductCount()<5000 || $total==0) {
                    // one step reindex
                    $result['url'] = $this->getUrl('*/*/runApplyUrl/', array('reindex'=>'run'));                    
                } else {
                    // multistep reindex
                    $result['url'] = $this->getUrl('*/*/runApplyUrl/', array('reindex'=>'clearStoreInvalidRewrites', 'current'=>0));
                }                
                $result['text'] = $this->__('Starting reindex catalog URL rewrites...');                
            } elseif ($reindex=='run') {            
                $catalogUrlModel->refreshRewrites(); // make reindex
                $result['text'] = $this->__('Catalog URL rewrites reindexed (100%)...');
                $result['stop'] = 1;
                $result['url'] = '';
            } elseif ($reindex=='clearStoreInvalidRewrites') {
                // 1/4 reindex
                $storeId = $this->_getStoreIdByIndex($stores, $current);
                if (!is_null($storeId)) {
                    $catalogUrlModel->clearStoreInvalidRewrites($storeId);
                    $result['url'] = $this->getUrl('*/*/runApplyUrl/', array('reindex'=>'refreshCategoryRewrite', 'current'=>$current));
                    $result['text'] = $this->__('Total %1$s, processed %2$s stores (%3$s%%) - clear invalid rewrites...', $total, $current+1, round((($current+1)*4-3)*100/($total*4), 2));
                } else {
                    $result['text'] = $this->__('Catalog URL rewrites reindexed (100%)...');
                    $result['stop'] = 1;
                    $result['url'] = '';
                }                
            } elseif ($reindex=='refreshCategoryRewrite') {
                // 2/4 reindex
                $storeId = $this->_getStoreIdByIndex($stores, $current);
                $catalogUrlModel->refreshCategoryRewrite($catalogUrlModel->getStores($storeId)->getRootCategoryId(), $storeId, false);
                $result['url'] = $this->getUrl('*/*/runApplyUrl/', array('reindex'=>'refreshProductRewrites', 'current'=>$current));
                $result['text'] = $this->__('Total %1$s, processed %2$s stores (%3$s%%) - refresh category rewrite...', $total, $current+1, round((($current+1)*4-2)*100/($total*4), 2));
            } elseif ($reindex=='refreshProductRewrites') {
                // 3/4 reindex
                $storeId = $this->_getStoreIdByIndex($stores, $current);
                $catalogUrlModel->refreshProductRewrites($storeId);
                $result['url'] = $this->getUrl('*/*/runApplyUrl/', array('reindex'=>'clearCategoryProduct', 'current'=>$current));
                $result['text'] = $this->__('Total %1$s, processed %2$s stores (%3$s%%) - refresh product rewrites...', $total, $current+1, round((($current+1)*4-1)*100/($total*4), 2));
            } elseif ($reindex=='clearCategoryProduct') {
                // 4/4 reindex
                $storeId = $this->_getStoreIdByIndex($stores, $current);
                $catalogUrlModel->getResource()->clearCategoryProduct($storeId);
                $result['url'] = $this->getUrl('*/*/runApplyUrl/', array('reindex'=>'clearStoreInvalidRewrites', 'current'=>$current+1));
                $result['text'] = $this->__('Total %1$s, processed %2$s stores (%3$s%%) - clear category product...', $total, $current+1, round((($current+1)*4)*100/($total*4), 2));                
            }            
        } else {
            // applyUrl
            $total = $this->_getTotalProductCount();
            if ($current<$total) {
                $this->_applyUrl($current, $limit);
                $current += $limit;            
                if ($current>=$total) {
                    $current = $total;                    
                    $result['url'] = $this->getUrl('*/*/runApplyUrl/', array('reindex'=>'start'));
                } else {
                    $result['url'] = $this->getUrl('*/*/runApplyUrl/', array('current'=>$current));
                }
                $result['text'] = $this->__('Total %1$s, processed %2$s records (%3$s%%)...', $total, $current, round($current*100/$total, 2));

            }
        }
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
        
    protected function _getStoreIdByIndex($stores, $current) {
        $storeKeys = array_keys($stores);
        $storeId = null;
        if (isset($storeKeys[$current])) {
            $store = $stores[$storeKeys[$current]];
            $storeId = $store->getId();
        }       
        return $storeId;
    }
    
    protected function _getTotalProductCount() {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string)Mage::getConfig()->getTablePrefix();
        $select = $connection->select()->from($tablePrefix.'catalog_product_entity', 'COUNT(*)');
        $total = $connection->fetchOne($select);        
        return intval($total);
    }
    
    
    protected function _applyUrl($from, $limit) {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string)Mage::getConfig()->getTablePrefix();

        $select = $connection->select()->from($tablePrefix.'eav_entity_type')->where("entity_type_code = 'catalog_product'");
        $productTypeId = $connection->fetchOne($select);

        $select = $connection->select()->from($tablePrefix.'eav_attribute')->where("entity_type_id = $productTypeId AND (attribute_code = 'url_path')");
        $urlPathId = $connection->fetchOne($select);
        $select = $connection->select()->from($tablePrefix.'eav_attribute')->where("entity_type_id = $productTypeId AND (attribute_code = 'url_key')");
        $urlKeyId = $connection->fetchOne($select);
        
        
        $select = $connection->select()->from($tablePrefix.'catalog_product_entity')->limit($limit, $from);
        $products = $connection->fetchAll($select);
        
        
        $storeCode = Mage::app()->getRequest()->getParam('store', false);
        if ($storeCode) {
            $store = Mage::getModel('core/store')->load($storeCode);
            
            $stores = array(
                $store->getId()
            );
        } else {
            $stores = Mage::getModel('core/store')->getCollection()->load()->getAllIds();
            array_unshift($stores, 0);
        }
        
        $helper = Mage::helper('seosuite');
        
        foreach ($products as $_product) {
            foreach ($stores as $storeId){
                $this->_product = Mage::getSingleton('catalog/product')->setStoreId($storeId)->load($_product['entity_id']);
                if ($this->_product){
                    $urlKeyTemplate = (string) Mage::getStoreConfig('mageworx_seo/seosuite/product_url_key', $storeId);
                    
                    $template = Mage::getModel('seosuite/catalog_product_template_url');
                    $template->setTemplate($urlKeyTemplate)
                        ->setProduct($this->_product);

                    $urlKey = $template->process();

                    if ($urlKey == '') {
                        $urlKey = $this->_product->getName();
                    }
                    
                    $urlKey = $helper->formatUrlKey($urlKey);

                    $urlSuffix = Mage::getStoreConfig('catalog/seo/product_url_suffix', $storeId);
                    
                    $select = $connection->select()->from($tablePrefix.'catalog_product_entity_varchar')->
                            where("entity_type_id = $productTypeId AND attribute_id = $urlKeyId AND entity_id = {$this->_product->getId()} AND store_id = {$storeId}");
                    $row = $connection->fetchRow($select);
                    if ($row) {
                        $connection->update($tablePrefix.'catalog_product_entity_varchar', array('value' => $urlKey), "entity_type_id = $productTypeId AND attribute_id = $urlKeyId AND entity_id = {$this->_product->getId()} AND store_id = {$storeId}");
                    } else {
                        $data = array(
                            'entity_type_id' => $productTypeId,
                            'attribute_id' => $urlKeyId,
                            'entity_id' => $this->_product->getId(),
                            'store_id' => $storeId,
                            'value' => $urlKey
                        );
                        $connection->insert($tablePrefix.'catalog_product_entity_varchar', $data);
                    }
                    
                    $select = $connection->select()->from($tablePrefix.'catalog_product_entity_varchar')->
                            where("entity_type_id = $productTypeId AND attribute_id = $urlPathId AND entity_id = {$this->_product->getId()} AND store_id = {$storeId}");
                    $row = $connection->fetchRow($select);
                    if ($row) {
                        $connection->update($tablePrefix.'catalog_product_entity_varchar', array('value' => $urlKey . $urlSuffix), "entity_type_id = $productTypeId AND attribute_id = $urlPathId AND entity_id = {$this->_product->getId()} AND store_id = {$storeId}");
                    } else {
                        $data = array(
                            'entity_type_id' => $productTypeId,
                            'attribute_id' => $urlPathId,
                            'entity_id' => $this->_product->getId(),
                            'store_id' => $storeId,
                            'value' => $urlKey . $urlSuffix
                        );
                        $connection->insert($tablePrefix.'catalog_product_entity_varchar', $data);
                    }
                }
            }
        }        
    }
    
    
}