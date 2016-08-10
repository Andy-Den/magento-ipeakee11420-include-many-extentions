<?php

class Magestore_Megamenu_Block_Adminhtml_Template_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct(){

            parent::__construct();

            $this->_objectId = 'id';
            $this->_blockGroup = 'megamenu';
            $this->_controller = 'adminhtml_template';

            $this->_updateButton('save', 'label', Mage::helper('megamenu')->__('Save Template'));
            $this->_updateButton('delete', 'label', Mage::helper('megamenu')->__('Delete Template'));

            $this->_addButton('saveandcontinue', array(
                    'label'		=> Mage::helper('adminhtml')->__('Save And Continue Edit'),
                    'onclick'	=> 'saveAndContinueEdit()',
                    'class'		=> 'save',
            ), -100);

            $this->_formScripts[] = "
                    function toggleEditor() {
                            if (tinyMCE.getInstanceById('megamenu_content') == null)
                                    tinyMCE.execCommand('mceAddControl', false, 'megamenu_content');
                            else
                                    tinyMCE.execCommand('mceRemoveControl', false, 'megamenu_content');
                    }

                    function saveAndContinueEdit(){
                            editForm.submit($('edit_form').action+'back/edit/');
                    }
            ";
    }

    public function getHeaderText(){
            if(Mage::registry('template_data') && Mage::registry('template_data')->getId())
                    return Mage::helper('megamenu')->__("Edit Template");
            return Mage::helper('megamenu')->__('Add Template');
    }
}