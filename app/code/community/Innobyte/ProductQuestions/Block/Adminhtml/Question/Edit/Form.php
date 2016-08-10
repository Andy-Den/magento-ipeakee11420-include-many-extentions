<?php

class Innobyte_ProductQuestions_Block_Adminhtml_Question_Edit_Form 
    extends Mage_Adminhtml_Block_Widget_Form 
{

    protected function _prepareForm() 
    {
        $model = Mage::registry('current_question');
        
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
            )
        );
        $helper = Mage::helper('innobyte_product_questions');

        $form->setUseContainer(true);
        $this->setForm($form);
        
        
        $fieldset = $form->addFieldset('question_form', 
                array('legend' => $helper->__('Question')));

        
        if ($model) {
            $fieldset->addField('id', 'hidden', array(
                'name' => 'id',
            ));
        }
        
        $fieldset->addField('customer_name', 'text', array(
            'label' => $helper->__('Author'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'customer_name'
        ));

        $fieldset->addField('customer_email', 'text', array(
            'label' => $helper->__('Email'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'customer_email',
        ));


        $fieldset->addField('content', 'textarea', array(
            'label' => $helper->__('Question'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'content'
        ));

        $fieldset->addField('visibility', 'select', array(
            'label' => $helper->__('Visibility'),
            'name' => 'visibility',
            'options' => array(
                0 => $helper->__('Private'),
                1 => $helper->__('Public')
            ),
        ));
        
        $fieldset->addField('answer', 'editor', array(
            'label' => $helper->__('Answer'),
            'name' => 'answer',
            'wysiwyg' => true,
            'config' => new Varien_Object(array('plugins' => array()))
        ));
        
        $fieldset->addField('status', 'select', array(
            'label' => $helper->__('Status'),
            'name' => 'status',
            'options' => array(
                0 => $helper->__('Not responsed'),
                1 => $helper->__('Responsed')
            ),
            'note' => $helper->__('If Status is Responsed and Visibility is set to Public the Answer will be visible in frontend')
        ));
        
        $fieldset->addField('votes', 'text', array(
            'label' => $helper->__('Votes'),
            'name' => 'votes',
        ));
        
        $fieldset->addField('product_id', 'hidden', array(
            'label' => $helper->__('Product Id'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'product_id',
            'note' => 'Product id'
        ));
        
        $fieldset->addField('product_name', 'text', array(
            'label'     => $helper->__('Product Name'),
            'name' => 'product_name',
            'readonly' => true,
            'disabled' => true,
        ));
        
        if ($model) {
            $data = $model->getData();
            $form->setValues($data);
        }
        $questionFormData = Mage::registry('question_form_data');
        if (!empty($questionFormData)) {
            $form->setValues(Mage::registry('question_form_data'));
        }
        $this->setForm($form);
        
        return parent::_prepareForm();
    }
}