<?php

class Magestore_Megamenu_Block_Products extends Mage_Catalog_Block_Product_Abstract
{
    
    protected $_productUrl = null;
    /**
     *
     * @return type 
     */
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }         
    
    public function getTemplateProduct($template)
    {          
        $processor = Mage::helper('cms')->getBlockTemplateProcessor();
        return $processor->filter($template);
    }
}