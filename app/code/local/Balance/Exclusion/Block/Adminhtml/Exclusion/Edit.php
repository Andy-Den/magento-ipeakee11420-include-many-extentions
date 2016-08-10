<?php

class Balance_Exclusion_Block_Adminhtml_Exclusion_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        parent::__construct();
        $badge_id = $this->getRequest()->getParam('id');

        $this->_objectId = 'id';
        $this->_blockGroup = 'exclusion';
        $this->_controller = 'adminhtml_exclusion';

        $this->_updateButton('save', 'label', Mage::helper('exclusion')->__('Save Term'));
        $this->_updateButton('delete', 'label', Mage::helper('exclusion')->__('Delete Term'));
        $this->_removeButton('reset');
    }

    public function getHeaderText()
    {
        if (Mage::registry('exclusion_data') && Mage::registry('exclusion_data')->getId()) {
            return Mage::helper('exclusion')->__(
                "Edit Item '%s'",
                $this->htmlEscape(Mage::registry('exclusion_data')->getName())
            );
        } else {
            return Mage::helper('exclusion')->__('Add Term to Exclusion List');
        }
    }

}