<?php

class Magestore_Megamenu_Model_Mysql4_Megamenu extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct(){
		$this->_init('megamenu/megamenu', 'megamenu_id');
	}
}