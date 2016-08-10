<?php

class Magestore_Megamenu_Block_Staticblock extends Mage_Core_Block_Template
{
	public function _prepareLayout(){
		return parent::_prepareLayout();
	}
        
        public function getTemplateBlock($template){        
            $processor = Mage::helper('cms')->getBlockTemplateProcessor();
            return $processor->filter($template);
        }
}