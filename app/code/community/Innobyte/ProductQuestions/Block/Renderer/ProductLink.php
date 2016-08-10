<?php

class Innobyte_ProductQuestions_Block_Renderer_ProductLink extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value =  $row->getData($this->getColumn()->getIndex());
        $productId = $row->getData($this->getColumn()->getIndex());
        
        $productName = $row->getData('product_name');
        if (!$productName) {
            $productName = $productId;
        }
                
        $link = Mage::helper("adminhtml")->getUrl("adminhtml/catalog_product/edit/",array('id' => $productId));
        return '<a href="'.$link.'" target="_blank">'.$productName.'</a>';
    }
}