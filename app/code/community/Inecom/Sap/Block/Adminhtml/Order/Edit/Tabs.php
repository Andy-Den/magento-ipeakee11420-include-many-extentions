<?php

class Inecom_Sap_Block_Adminhtml_Order_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('sap_order_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('inecom_sap')->__('Failed Order'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('inecom_sap')->__('Information'),
          'title'     => Mage::helper('inecom_sap')->__('Information'),
          'content'   => $this->getLayout()->createBlock('sap/adminhtml_order_edit_tab_form')->toHtml(),
      ));

      return parent::_beforeToHtml();
  }
}