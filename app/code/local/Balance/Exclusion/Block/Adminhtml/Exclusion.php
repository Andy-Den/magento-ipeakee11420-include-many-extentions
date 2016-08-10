<?php

class Balance_Exclusion_Block_Adminhtml_Exclusion extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_controller = 'adminhtml_exclusion';
        $this->_blockGroup = 'exclusion';
        $this->_headerText = Mage::helper('exclusion')->__('Newsletter Exclusion Term Manager');
        $this->_addButtonLabel = Mage::helper('exclusion')->__('Add Term');
        parent::__construct();
    }

}