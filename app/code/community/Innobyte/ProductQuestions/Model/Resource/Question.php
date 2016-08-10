<?php

class Innobyte_ProductQuestions_Model_Resource_Question 
    extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('innobyte_product_questions/question', 'id');
    }
}