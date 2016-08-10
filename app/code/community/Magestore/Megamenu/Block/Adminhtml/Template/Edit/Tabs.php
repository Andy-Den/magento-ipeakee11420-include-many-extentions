<?php

class Magestore_Megamenu_Block_Adminhtml_Template_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct(){
		parent::__construct();
		$this->setId('template_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('megamenu')->__('Template Information'));
	}

	protected function _beforeToHtml(){
		$this->addTab('form_section', array(
			'label'	 => Mage::helper('megamenu')->__('Template Information'),
			'title'	 => Mage::helper('megamenu')->__('Template Information'),
			'content'	 => $this->getLayout()->createBlock('megamenu/adminhtml_template_edit_tab_form')->toHtml(),
		));	
		return parent::_beforeToHtml();
	}
}