<?php

class Balance_Exclusion_Block_Adminhtml_Exclusion_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {

        $form = new Varien_Data_Form();

        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'exclusion_form',
            array('legend' => Mage::helper('exclusion')->__('Term information'))
        );

        $fieldset->addField(
            'term',
            'text',
            array(
                'label'              => Mage::helper('exclusion')->__('Exclusion Term'),
                'class'              => 'required-entry',
                'required'           => true,
                'name'               => 'term',
                'after_element_html' => '<br /><small>Enter the term</small>'
            )
        );

        if (Mage::getSingleton('adminhtml/session')->getExclusionData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getExclusionData());
            Mage::getSingleton('adminhtml/session')->setExclusionData(null);
        } elseif (Mage::registry('exclusion_data')) {
            $form->setValues(Mage::registry('exclusion_data')->getData());
        }

        return parent::_prepareForm();
    }

}