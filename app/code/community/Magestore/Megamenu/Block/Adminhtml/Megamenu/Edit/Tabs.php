<?php

class Magestore_Megamenu_Block_Adminhtml_Megamenu_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct(){
		parent::__construct();
		$this->setId('megamenu_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('megamenu')->__('Menu Information'));
                
	}

	protected function _beforeToHtml(){
		$this->addTab('form_section', array(
			'label'	 => Mage::helper('megamenu')->__('General Information'),
			'title'	 => Mage::helper('megamenu')->__('General    Information'),
			'content'	 => $this->getLayout()->createBlock('megamenu/adminhtml_megamenu_edit_tab_form')->toHtml(),
		));
        $this->addTab('content_section', array(
			'label'	 => Mage::helper('megamenu')->__('Content'),
			'title'	 => Mage::helper('megamenu')->__('Content'),
            'content'=> $this->getLayout()->createBlock('megamenu/adminhtml_megamenu_edit_tab_content')->toHtml(),
		));
        /*
         * 
        $this->addTab('custom_style',array(
            'label'     =>  Mage::helper('megamenu')->__('Custom Style'),
            'title'     =>  Mage::helper('megamenu')->__('Custom Style'),
            'content'   =>  $this->getLayout()->createBlock('megamenu/adminhtml_megamenu_edit_tab_customStyle')->toHtml(),
        ));
         * 
         */
		
		return parent::_beforeToHtml();
	}
}
