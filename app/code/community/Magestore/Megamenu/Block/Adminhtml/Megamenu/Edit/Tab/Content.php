<?php

class Magestore_Megamenu_Block_Adminhtml_Megamenu_Edit_Tab_Content extends Mage_Adminhtml_Block_Widget_Form {

    public function __construct() {
        parent::__construct();
        $this->setTemplate('megamenu/content.phtml');
    }

    protected function _prepareLayout() {
        $this->setChild('load_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(
                                array(
                                    'label' => Mage::helper('adminhtml')->__('Load Template'),
                                    'onclick' => 'templateControl.load();',
                                    'type' => 'button',
                                    'class' => 'save'
                                )
                        )
        );
      
        
        $this->setChild('header_and_footer', $this->getLayout()->createBlock('megamenu/adminhtml_megamenu_edit_tab_content_headerfooter')
        );


        $this->setChild('main_content', $this->getLayout()->createBlock('megamenu/adminhtml_megamenu_edit_tab_content_maincontent')
                        ->setData(
                                array(
                                    'label' => Mage::helper('adminhtml')->__('Main Content'),
                                    'onclick' => 'templateControl.load();',
                                    'type' => 'button',
                                    'class' => 'save'
                                )
                        )
        );

        $this->setChild('featured_item', $this->getLayout()->createBlock('megamenu/adminhtml_megamenu_edit_tab_content_featureditem')
                        ->setData(
                                array(
                                    'label' => Mage::helper('adminhtml')->__('Featured Item'),
                                    'onclick' => 'templateControl.load();',
                                    'type' => 'button',
                                    'class' => 'save'
                                )
                        )
        );

        return parent::_prepareLayout();
    }
    
    public function getLoadButtonHtml() {
        return $this->getChildHtml('load_button');
    }

    public function getLoadUrl() {
        return $this->getUrl('*/*/gettemplate');
    }

    public function getSaveUrl() {
        return $this->getUrl('*/*/save');
    }

    public function getFormData() {
        if (Mage::getSingleton('adminhtml/session')->getMegamenuData()) {
            $data = Mage::getSingleton('adminhtml/session')->getMegamenuData();
            Mage::getSingleton('adminhtml/session')->setMegamenuData(null);
        } elseif (Mage::registry('megamenu_data'))
            $data = Mage::registry('megamenu_data')->getData();
        return $data;
    }

}