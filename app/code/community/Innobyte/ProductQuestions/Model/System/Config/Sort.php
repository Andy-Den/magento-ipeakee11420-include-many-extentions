<?php

class Innobyte_ProductQuestions_Model_System_Config_Sort
{
    public function toOptionArray()
    {
        return array(
            array(
                'value'=>1, 
                'label'=>Mage::helper('innobyte_product_questions')->__('Date')),
            array(
                'value'=>2, 
                'label'=>Mage::helper('innobyte_product_questions')->__('Helpfulness')),
        );
    }

}