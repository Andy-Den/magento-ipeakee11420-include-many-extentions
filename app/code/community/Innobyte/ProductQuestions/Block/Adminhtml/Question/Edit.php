<?php

class Innobyte_ProductQuestions_Block_Adminhtml_Question_Edit 
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'innobyte_product_questions';
        $this->_controller = 'adminhtml_question';
        
        $this->_updateButton('save', 'label', Mage::helper('innobyte_product_questions')->__('Save Question'));
        $this->_updateButton('delete', 'label', Mage::helper('innobyte_product_questions')->__('Delete Question'));
             
        if(strpos($_SERVER['HTTP_REFERER'], 'catalog_product')){
            $this->_updateButton(
                'back',
                'onclick',
                'setLocation(\'' . $this->getUrl(
                    'adminhtml/catalog_product/edit',
                    array('id' => Mage::registry('current_question')->getProductId())) .'\')'
            );
        }
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);
        
        $this->_addButton('saveandemail', array(
            'label' => $this->__('Save And Send Email'),
            'onclick' => 'saveAndReply()',
            'class' => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
            
            function toggleEditor() {
                if (tinyMCE.getInstanceById('productquestions_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'productquestions_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'productquestions_content');
                }
            }

            function saveAndReply(){
                editForm.submit($('edit_form').action+'reply/1/');
            }
        ";

        $this->_formScripts[] = "
            
        ";
    }
    
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    public function getHeaderText()
    {
        if( Mage::registry('question_data') && Mage::registry('question_data')->getId() ) {
            return Mage::helper('innobyte_product_questions')->__("Edit Question '%s'", $this->htmlEscape(Mage::registry('question_data')->getTitle()));
        } else {
            return Mage::helper('innobyte_product_questions')->__('Edit Question');
        }
    }
}