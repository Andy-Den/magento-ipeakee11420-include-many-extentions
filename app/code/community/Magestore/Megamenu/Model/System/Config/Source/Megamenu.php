<?php

class Magestore_Megamenu_Model_System_Config_Source_Menutype
{
    public function toOptionArray(){
        return array(
            array('value'=>1, 'label'=>'Content Only'),
            array('value'=>2, 'label'=>'Product Listing'),
            array('value'=>3, 'label'=>'Category Listing'),
            array('value'=>4, 'label'=>'Category & Product Listing'),
            array('value'=>5, 'label'=>'Contact Form')
        );
    }
}