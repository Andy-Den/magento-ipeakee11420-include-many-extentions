<?php

class Inecom_Sap_Block_Adminhtml_Order_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('sap_order_form', array('legend' => Mage::helper('inecom_sap')->__('Information')));

        $fieldset->addField('order_id', 'text', array(
            'label' => Mage::helper('inecom_sap')->__('Order Id'),
            'readonly' => true,
            'name' => 'order_id',
        ));
        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('inecom_sap')->__('Status'),
            'name' => 'status',
            'disabled' => true,
            'values' => array(
                array(
                    'value' => 'delivered',
                    'label' => Mage::helper('inecom_sap')->__('Delivered'),
                ),
                array(
                    'value' => 'failed',
                    'label' => Mage::helper('inecom_sap')->__('Failed'),
                ),
                array(
                    'value' => 'reported',
                    'label' => Mage::helper('inecom_sap')->__('Reported'),
                ),
                array(
                    'value' => 'pending',
                    'label' => Mage::helper('inecom_sap')->__('Pending'),
                ),
            ),
        ));
        $fieldset->addField('messages', 'textarea', array(
            'label' => Mage::helper('inecom_sap')->__('Web service response'),
            'readonly' => true,
            'style' => 'width:700px; height:500px;',
            'name' => 'messages',
        ));

//      $fieldset->addField('filename', 'file', array(
//          'label'     => Mage::helper('inecom_sap')->__('File'),
//          'required'  => false,
//          'name'      => 'filename',
//	  ));
//
//      $fieldset->addField('status', 'select', array(
//          'label'     => Mage::helper('inecom_sap')->__('Status'),
//          'name'      => 'status',
//          'values'    => array(
//              array(
//                  'value'     => 1,
//                  'label'     => Mage::helper('inecom_sap')->__('Enabled'),
//              ),
//
//              array(
//                  'value'     => 2,
//                  'label'     => Mage::helper('inecom_sap')->__('Disabled'),
//              ),
//          ),
//      ));
//
//      $fieldset->addField('content', 'editor', array(
//          'name'      => 'content',
//          'label'     => Mage::helper('inecom_sap')->__('Content'),
//          'title'     => Mage::helper('inecom_sap')->__('Content'),
//          'style'     => 'width:700px; height:500px;',
//          'wysiwyg'   => false,
//          'required'  => true,
//      ));

        if (Mage::getSingleton('adminhtml/session')->getOrderData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getOrderData());
            Mage::getSingleton('adminhtml/session')->setOrderData(null);
        } elseif (Mage::registry('sap_order_data')) {
            $form->setValues(Mage::registry('sap_order_data')->getData());
        }
        return parent::_prepareForm();
    }

}