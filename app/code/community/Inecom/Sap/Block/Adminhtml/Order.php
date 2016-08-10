<?php

class Inecom_Sap_Block_Adminhtml_Order extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_order';
        $this->_blockGroup = 'sap';
        $this->_headerText = Mage::helper('inecom_sap')->__('Order Queue');
        $this->_addButtonLabel = Mage::helper('inecom_sap')->__('Add Item');
        parent::__construct();
        $this->_removeButton('add');
    }
}