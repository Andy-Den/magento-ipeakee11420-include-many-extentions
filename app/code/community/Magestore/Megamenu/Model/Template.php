<?php

class Magestore_Megamenu_Model_Template extends Mage_Core_Model_Abstract
{
	public function _construct(){
		parent::_construct();
		$this->_init('megamenu/template');
	}
}