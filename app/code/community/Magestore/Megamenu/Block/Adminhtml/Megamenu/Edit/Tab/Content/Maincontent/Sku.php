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
class Magestore_Megamenu_Block_Adminhtml_Megamenu_Edit_Tab_Content_Maincontent_Sku extends Mage_Adminhtml_Block_Widget_Grid {
    

    protected $_selectedProducts;
	
	public function __construct() {
		parent::__construct ();
		$this->setUseAjax ( true );
	}
	
        /**
     * Grid Row JS Callback
     *
     * @return string
     */
    public function getRowClickCallback()
    {
        $chooserJsObject = $this->getId();
        $js = "  
            function (grid, event) {
                var trElement = Event.findElement(event, 'tr');
                var isInput = Event.element(event).tagName == 'INPUT';
                var input = $('".$this->getInput()."');
                if (trElement) {
                    var checkbox = Element.select(trElement, 'input');
                    if (checkbox[0]) {
                        var checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                        if(checked){
                            if(input.value == '')
                                input.value = checkbox[0].value;
                            else
                                input.value = input.value + ', '+checkbox[0].value;
                                
                        }else{
                            var vl = checkbox[0].value;
                            if(input.value.search(vl) == 0){
                                if(input.value == vl) input.value = '';
                                input.value = input.value.replace(vl+', ','');
                            }else{
                                input.value = input.value.replace(', '+ vl,'');
                            }
                        }
                        checkbox[0].checked =  checked;
                        grid.reloadParams['selected[]'] = input.value.split( ', ');
                    }
                }
            }
        ";
        return $js;
    }
    public function getCheckboxCheckCallback(){
        $js = ' function (grid, element, checked) {
        var input = $("'.$this->getInput().'");
        if (checked) {
            $$("#'.$this->getId().' input[type=checkbox][class=checkbox]").each(function(e){
                if(e.name != "check_all"){
                    if(!e.checked){
                        if(input.value == "")
                            input.value = e.value;
                        else
                            input.value = input.value + ", "+e.value;
                        e.checked = true;
                        grid.reloadParams["selected[]"] = input.value.split(", ");
                    }
                }
            });
        }else{
            $$("#'.$this->getId().' input[type=checkbox][class=checkbox]").each(function(e){
                if(e.name != "check_all"){
                    if(e.checked){
                        var vl = e.value;
                        if(input.value.search(vl) == 0){
                            if(input.value == vl) input.value = "";
                            input.value = input.value.replace(vl+", ","");
                        }else{
                            input.value = input.value.replace(", "+ vl,"");
                        }
                        e.checked = false;
                        grid.reloadParams["selected[]"] = input.value.split(", ");
                    }
                }
            });
                            
        }
    } ';
        return $js;
    }
    public function getRowInitCallback(){
       $js =' function (grid, row) {
            if (!grid.reloadParams) {
                grid.reloadParams["selected[]"] = $("'.$this->getInput().'").value.split(", ");
            }
        } ';
        return $js;
    }

    
    protected function _prepareCollection() {
	$collection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('name')
            ->addAttributeToFilter('visibility', array('in'=>Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds()))
            ->addAttributeToFilter('status', array('in'=>Mage::getSingleton('catalog/product_status')->getVisibleStatusIds()));
	$this->setCollection($collection);
	return parent::_prepareCollection();
    }
	
    protected function _prepareColumns() {
        $this->addColumn('in_products', array(
            'header_css_class'  => 'a-center',
            'type'              => 'checkbox',
            'field_name'              => 'in_products[]',
            'values'            => $this->getRequest()->getParam('selected'),
            'align'             => 'center',	
            'index'             => 'entity_id'
        ));
                
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => '60px',
            'index'     => 'entity_id'
        ));
        $this->addColumn('sku', array(
            'header' => Mage::helper('catalog')->__('SKU'),
            'align' => 'right',
            'column_css_class' => 'small_width',
            'index' => 'sku',
        ));
        $this->addColumn('name', array(
            'header' => Mage::helper('catalog')->__('Product Name'),
            'align' => 'right',
            'column_css_class' => 'small_width',
            'index' => 'name',
        ));
        return parent::_prepareColumns();
    }
    public function getGridUrl(){
        return $this->getUrl('*/*/'.$this->getGridUrlCall(), array(
            '_current'          => true,
            'selected'   => $this->getRequest()->getParam('selected'),
            'collapse'          => null
        ));
    }
}

