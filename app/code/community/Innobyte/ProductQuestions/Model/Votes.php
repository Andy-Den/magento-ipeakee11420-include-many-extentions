<?php

class Innobyte_ProductQuestions_Model_Votes extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('innobyte_product_questions/votes');
    }
}
