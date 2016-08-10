<?php

class Magestore_Megamenu_Block_Adminhtml_Megamenu_Edit_Tab_CustomStyle extends Mage_Adminhtml_Block_Widget_Form {

    public function __construct() {
        parent::__construct();
        $this->setTemplate('megamenu/customstyle.phtml');
    }

    public function  _prepareLayout() {
        $this->getLayout()->getBlock('head')->addJs('magestore/mega_menu/prototype_colorpicker.js');
        $this->getLayout()->getBlock('head')->addCss('css/magestore/mega_menu/prototype_colorpicker.css');
        parent::_prepareLayout();
    }
    //get data from data base
    public function getFormData(){
        if (Mage::getSingleton('adminhtml/session')->getMegamenuData()){
                $data = Mage::getSingleton('adminhtml/session')->getMegamenuData();
                Mage::getSingleton('adminhtml/session')->setMegamenuData(null);
        }elseif(Mage::registry('megamenu_data'))
                $data = Mage::registry('megamenu_data')->getData();
        return $data;
    }
    
}