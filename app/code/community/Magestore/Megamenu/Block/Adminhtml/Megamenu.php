<?php

class Magestore_Megamenu_Block_Adminhtml_Megamenu extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct(){
		$this->_controller = 'adminhtml_megamenu';
		$this->_blockGroup = 'megamenu';
		$this->_headerText = Mage::helper('megamenu')->__('Menu Manager');
		$this->_addButtonLabel = Mage::helper('megamenu')->__('Add Menu Item');
		parent::__construct();
	}
}