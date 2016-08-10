<?php

class Innobyte_ProductQuestions_Block_Adminhtml_Question_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('question_form', array('legend'=>Mage::helper('innobyte_product_questions')->__('Item Information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('innobyte_product_questions')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('innobyte_product_questions')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('innobyte_product_questions')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('innobyte_product_questions')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('innobyte_product_questions')->__('Content'),
          'title'     => Mage::helper('innobyte_product_questions')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => true,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getWebData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getWebData());
          Mage::getSingleton('adminhtml/session')->setWebData(null);
      } elseif ( Mage::registry('question_data') ) {
          $form->setValues(Mage::registry('question_data')->getData());
      }
      return parent::_prepareForm();
  }
}