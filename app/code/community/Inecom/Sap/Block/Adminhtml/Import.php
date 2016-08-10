<?php

class Inecom_Sap_Block_Adminhtml_Import extends Mage_Adminhtml_Block_Template
{
//    public function __construct()
//    {
//        $this->_controller = 'adminhtml_sap';
//        $this->_blockGroup = 'sap';
//        $this->_headerText = Mage::helper('inecom_sap')->__('product Import');
//        $this->_addButtonLabel = Mage::helper('inecom_sap')->__('Add Item');
//
//        $this->setTemplate('sap/import.phtml');
//
//        parent::__construct();
//    }

    protected function _construct()
    {
        $this->setTemplate('sap/import.phtml');
    }

    protected function _prepareLayout()
    {
//        $this->setChild('save_button',
//            $this->getLayout()->createBlock('adminhtml/widget_button')
//                ->setData(array(
//                    'label'     => Mage::helper('adminhtml')->__('Save Currency Rates'),
//                    'onclick'   => 'currencyForm.submit();',
//                    'class'     => 'save'
//        )));

//        $this->setChild('reset_button',
//            $this->getLayout()->createBlock('adminhtml/widget_button')
//                ->setData(array(
//                    'label'     => Mage::helper('adminhtml')->__('Reset'),
//                    'onclick'   => 'document.location.reload()',
//                    'class'     => 'reset'
//        )));
//
//        $this->setChild('import_button',
//            $this->getLayout()->createBlock('adminhtml/widget_button')
//                ->setData(array(
//                    'label'     => Mage::helper('adminhtml')->__('Import'),
//                    'class'     => 'add',
//                    'type'      => 'submit',
//        )));
//
//        $this->setChild('rates_matrix',
//            $this->getLayout()->createBlock('adminhtml/system_currency_rate_matrix')
//        );
//
//        $this->setChild('import_services',
//            $this->getLayout()->createBlock('adminhtml/system_currency_rate_services')
//        );

        return parent::_prepareLayout();
    }

    protected function getHeader()
    {
        return Mage::helper('adminhtml')->__('Manage Currency Rates');
    }

    protected function getImportUrl()
    {
        return $this->getUrl('*/*/runImport');
    }

      protected function getExportUrl()
    {
        return $this->getUrl('*/*/runExport');
    }

//    protected function getImportButtonHtml()
//    {
//        return $this->getChildHtml('import_button');
//    }


}