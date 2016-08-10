<?php

class Magestore_Megamenu_Block_Adminhtml_Template_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm(){

            $form = new Varien_Data_Form();
            $this->setForm($form);
            //$form123 = new Varien_Data_Form();
            $data = array();
            if (Mage::getSingleton('adminhtml/session')->getTemplateData())
                    $data = Mage::getSingleton('adminhtml/session')->getTemplateData();
            elseif (Mage::registry('template_data'))
                    $data = Mage::registry('template_data')->getData();

            //$fieldset123 = $form->addFieldset('description_fieldset123', array('legend'=>Mage::helper('megamenu')->__('Load Template')));
            $fieldset = $form->addFieldset('description_fieldset', array('legend'=>Mage::helper('megamenu')->__('Template Information')));                                
            $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
            $wysiwygConfig->addData(array(
                    'add_variables'		=> false,
                    'plugins'			=> array(),
                    'widget_window_url'	=> Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/widget/index'),
                    'directives_url'	=> Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive'),
                    'directives_url_quoted'	=> preg_quote(Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive')),
                    'files_browser_window_url'	=> Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index'),
            ));
            $fieldset->addField('name_template', 'text', array(
                    'name'		=> 'name_template',
                    'label'		=> Mage::helper('megamenu')->__('Name'),
                    'title'		=> Mage::helper('megamenu')->__('Name'),			
                    'required'	=> true,			
            ));
            $fieldset->addField('description', 'textarea', array(
                    'name'		=> 'description',
                    'label'		=> Mage::helper('megamenu')->__('Description'),
                    'title'		=> Mage::helper('megamenu')->__('Description'),								
            ));

            $fieldset->addField('code_template', 'editor', array(
                    'name'		=> 'code_template',
                    'label'		=> Mage::helper('megamenu')->__('Template Content'),
                    'title'		=> Mage::helper('megamenu')->__('Template Content'),
                    'wysiwyg'	=> true,
                    'required'	=> true,
                    'style'		=> 'width: 600px;',
                    'config'	=> $wysiwygConfig,
            ));
            if(isset($data['image'])){
                $data['image'] = 'megamenu/image/'.$this->getRequest()->getParam('id').'/'.$data['image'];
            }            
            $fieldset->addField('image', 'image', array(			
                    'name'		=> 'image',
                    'label'		=> Mage::helper('megamenu')->__('Up Image Template'),
                    'title'		=> Mage::helper('megamenu')->__('Up Image Template'),                    
            ));
            $form->setValues($data);
            // $form123->setValues($data);
            return parent::_prepareForm();
    }
}