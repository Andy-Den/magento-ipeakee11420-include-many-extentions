<?php

class Inecom_Sap_Block_Adminhtml_Order_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'sap';
        $this->_controller = 'adminhtml_order';

        $this->_updateButton('save', 'label', Mage::helper('inecom_sap')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('inecom_sap')->__('Delete Item'));

        $this->_removeButton('save');
        $this->_removeButton('reset');
        //$this->_removeButton('delete');

        //if (in_array(Mage::registry('sap_order_data')->getStatus(), array(Inecom_Sap_Helper_Order::REPORTED, Inecom_Sap_Helper_Order::FAILED))) {
            $this->_addButton('queue', array(
                'label' => Mage::helper('adminhtml')->__('Re-Queue'),
                'onclick' => 'setLocation(\'' . $this->getUrl('*/*/queue', array('id' => Mage::registry('sap_order_data')->getId())) . '\')',
                'class' => 'save',
            ), -1, 100);
        //}

//        $this->_addButton('saveandcontinue', array(
//            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
//            'onclick'   => 'saveAndContinueEdit()',
//            'class'     => 'save',
//        ), -100);

//        $this->_formScripts[] = "
//            function toggleEditor() {
//                if (tinyMCE.getInstanceById('press_content') == null) {
//                    tinyMCE.execCommand('mceAddControl', false, 'press_content');
//                } else {
//                    tinyMCE.execCommand('mceRemoveControl', false, 'press_content');
//                }
//            }
//
//            function saveAndContinueEdit(){
//                editForm.submit($('edit_form').action+'back/edit/');
//            }
//        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('sap_order_data') && Mage::registry('sap_order_data')->getId() ) {

            $order = Mage::getModel('sales/order')->load(Mage::registry('sap_order_data')->getOrderId());

//            var_dump(Mage::registry('sap_order_data')->getOrderId());
//            var_dump($order);

            return Mage::helper('inecom_sap')->__("Order # '%s'", $order->getIncrementId());
        } else {
            return Mage::helper('inecom_sap')->__('Add Item');
        }
    }
}