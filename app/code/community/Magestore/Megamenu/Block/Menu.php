<?php

class Magestore_Megamenu_Block_Menu extends Mage_Core_Block_Template
{
    protected $_collection;
    public function _prepareLayout(){
		return parent::_prepareLayout();
	}
    
    
    public function getItemCollection(){
        if(is_null($this->_collection)){
            $collection = Mage::getModel('megamenu/megamenu')->getCollection()
                ->addFieldToFilter('status',1);
            $this->_collection = $collection;
        }
        return $this->_collection;
    }
    
}