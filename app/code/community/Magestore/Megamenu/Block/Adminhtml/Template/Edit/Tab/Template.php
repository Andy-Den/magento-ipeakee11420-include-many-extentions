<?php

class Magestore_Megamenu_Block_Adminhtml_Template_Edit_Tab_Template extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm(){

            $form = new Varien_Data_Form();
            $this->setForm($form);
            $data = array();
            if (Mage::getSingleton('adminhtml/session')->getMegamenuData())
                    $data = Mage::getSingleton('adminhtml/session')->getMegamenuData();
            elseif (Mage::registry('megamenu_data'))
                    $data = Mage::registry('megamenu_data')->getData();

            $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
            $wysiwygConfig->addData(array(
                    'add_variables'		=> false,
                    'plugins'			=> array(),
                    'widget_window_url'	=> Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/widget/index'),
                    'directives_url'	=> Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive'),
                    'directives_url_quoted'	=> preg_quote(Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive')),
                    'files_browser_window_url'	=> Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index'),
            ));                                  
            $fieldset = $form->addFieldset('description_fieldset', array('legend'=>Mage::helper('megamenu')->__('Code Template')));                                            
			$eventElem = $fieldset->addField('default_template', 'select', array(
					'required'  => true,
                    'name'		=> 'code_template',
                    'label'		=> Mage::helper('megamenu')->__('Template'),
                    'title'		=> Mage::helper('megamenu')->__('Template'),
                    'wysiwyg'	=> true,                   
                    'style'		=> 'width: 280px;',
					'onclick' => "return false;",
					'onchange' => "return false;",
					'value'  => '4',
					'values' => array(
                                '-1'=> array( 'label' => '-- Please choose template --', 'value' => '-1'),
                                '1' => array( 
                                                'value'=> array(array('value'=>'2' , 'label' => 'Option2') , array('value'=>'3' , 'label' =>'Option3') ),
                                                'label' => 'Size'    
                                           ),
                                '2' => array( 
                                                'value'=> array(array('value'=>'4' , 'label' => 'Option4') , array('value'=>'5' , 'label' =>'Option5') ),
                                                'label' => 'Color'   
                                           ),                                          
                                  
                           ),
					'disabled' => false,
					'readonly' => false,
					'tabindex' => 1
                 //   'config'	=> $wysiwygConfig,
				));
			$eventElem->setAfterElementHtml(
			//	$this->getLayout()->createBlock('megamenu/adminhtml_template_template')->toHtml();
            );
		   $fieldset->addField('code_template', 'editor', array(
                    'name'		=> 'code_template',
                    'label'		=> Mage::helper('megamenu')->__('Template Content'),
                    'title'		=> Mage::helper('megamenu')->__('Template Content'),
                    'wysiwyg'	=> true,                   
                    'style'		=> 'width: 600px;',
                    'config'	=> $wysiwygConfig,
            ));
            $form->setValues($data);
            return parent::_prepareForm();
    }
}