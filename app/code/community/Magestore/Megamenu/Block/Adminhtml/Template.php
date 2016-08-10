<?php

class Magestore_Megamenu_Block_Adminhtml_Template extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct(){
            $this->_controller = 'adminhtml_template';
            $this->_blockGroup = 'megamenu';
            $this->_headerText = Mage::helper('megamenu')->__('Template Manager');
            $this->_addButtonLabel = Mage::helper('megamenu')->__('Add Template');
            parent::__construct();               	
    }
}