<?php

class Magestore_Megamenu_Block_Adminhtml_Megamenu_Edit_Tab_Content_Headerfooter extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm(){
        $form = new Varien_Data_Form();
        $this->setForm($form);
        if (Mage::getSingleton('adminhtml/session')->getMegamenuData()){
                $data = Mage::getSingleton('adminhtml/session')->getMegamenuData();
                Mage::getSingleton('adminhtml/session')->setMegamenuData(null);
        }elseif(Mage::registry('megamenu_data'))
                $data = Mage::registry('megamenu_data')->getData();
        
        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
			array(
				'hidden'=>false,
				'add_variables' => true, 
				'add_widgets' => true,
				'add_images'=>true,
				'widget_window_url'	=> Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/widget/index'),
				'directives_url'	=> Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive'),
				'directives_url_quoted'	=> preg_quote(Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive')),
				'files_browser_window_url'	=> Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index')
			)
		);
        $fieldset = $form->addFieldSet('megamenu_content_headerfooter', array('legend'=>Mage::helper('megamenu')->__('Header & Footer Content')));
        $fieldset->addField('header', 'editor', array(
            'label'     =>   Mage::helper('megamenu')->__('Header Content'),
            'title'     =>   Mage::helper('megamenu')->__('Header Content'),
            'name'      =>  'header',
            'wysiwyg'   => true,
            'style'     => 'width:600px;',
            'config'    =>  $wysiwygConfig
            )
        );
        $fieldset->addField('footer', 'editor', array(
            'label'     =>   Mage::helper('megamenu')->__('Footer Content'),
            'title'     =>   Mage::helper('megamenu')->__('Footer Content'),
            'name'      =>  'footer',
            'wysiwyg'   => true,
            'style'     => 'width:600px;',
            'config'    =>  $wysiwygConfig
            )
        );
        $form->setValues($data);
        return parent::_prepareForm();
    }
}