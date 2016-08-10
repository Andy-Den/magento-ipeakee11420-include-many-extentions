<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales order create search products block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magestore_Megamenu_Block_Adminhtml_Megamenu_Edit_Tab_Content_Featureditem_Products extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct(){
        parent::__construct();
        $this->setId('featuredproductGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        if ($this->getProgram() && $this->getProgram()->getId()){
        	$this->setDefaultFilter(array('in_products' => 1));
        }
    }
    
    protected function _addColumnFilterToCollection($column){
    	if ($column->getId() == 'in_products'){
    		$productIds = $this->_getSelectedProducts();
    		if (empty($productIds)) $productIds = 0;
    		if ($column->getFilter()->getValue())
    			$this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
    		elseif ($productIds)
    			$this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
    		return $this;
    	}
    	return parent::_addColumnFilterToCollection($column);
    }
    
    protected function _prepareCollection(){
    	$collection = Mage::getModel('catalog/product')->getCollection()
    		->addAttributeToSelect('*');
    	
    	if ($storeId = $this->getRequest()->getParam('store', 0))
    		$collection->addStoreFilter($storeId);
    	
		$this->setCollection($collection);
		return parent::_prepareCollection();
    }
    
    protected function _prepareColumns(){
    	$currencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
    	
    	$this->addColumn('in_products', array(
            'header_css_class'  => 'a-center',
			'type'              => 'checkbox',
			'name'              => 'in_products',
			'values'            => $this->_getSelectedProducts(),
			'align'             => 'center',
			'index'             => 'entity_id',
			'use_index'			=> true,
        ));
 
		$this->addColumn('entity_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => '60',
            'index'     => 'entity_id'
        ));
        
        $this->addColumn('product_name', array(
			'header'    => Mage::helper('catalog')->__('Name'),
			'align'     => 'left',
			'index'     => 'name',
		));
		
		$this->addColumn('product_status',array(
            'header'=> Mage::helper('catalog')->__('Status'),
            'width' => '90px',
            'index' => 'status',
            'type'  => 'options',
            'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));
        
        $this->addColumn('product_sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'width'     => '80px',
            'index'     => 'sku'
        ));
		
        $this->addColumn('product_price', array(
            'header'    => Mage::helper('catalog')->__('Price'),
            'type'  	=> 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'     => 'price'
        ));
    }
    
 /*   public function getRowUrl($row){
		return $this->getUrl('adminhtml/catalog_product/edit', array(
			'id' 	=> $row->getId(),
			'store'	=>$this->getRequest()->getParam('store')
		));
	}*/
	
	public function getGridUrl(){
        return $this->getUrl('*/*/featuredproductGrid',array(
        	'_current'	=>true,
        	'id'		=>$this->getRequest()->getParam('id'),
        	'store'		=>$this->getRequest()->getParam('store')
    	));
    }
    
    protected function _getSelectedProducts(){
    	$products = $this->getProducts();
    	if (!is_array($products))
    		$products = array_keys($this->getSelectedRelatedProducts());
    	return $products;
    }
    
   public function getSelectedRelatedProducts(){
    	$products = array();
    	$id = $this->getRequest()->getParam('id');
        if($id){
            $item = Mage::getModel('megamenu/megamenu')->load($id);
            $productIds = $item->getFeaturedProductIds();
            foreach ($productIds as $productId)
                $products[$productId] = array('position' => 0);
        }
    	return $products;
    }
	
  
	/**
	 * get currrent store
	 *
	 * @return Mage_Core_Model_Store
	 */
	public function getStore(){
		$storeId = (int) $this->getRequest()->getParam('store', 0);
		return Mage::app()->getStore($storeId);
	}
}