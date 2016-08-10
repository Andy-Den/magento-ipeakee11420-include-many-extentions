<?php

class Innobyte_ProductQuestions_Model_Resource_Question_Collection 
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('innobyte_product_questions/question');
    }
}