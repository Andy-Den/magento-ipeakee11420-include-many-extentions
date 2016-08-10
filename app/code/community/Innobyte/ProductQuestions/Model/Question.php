<?php

class Innobyte_ProductQuestions_Model_Question extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('innobyte_product_questions/question');
    }
    
    
    public function getAdminUrl() 
    {
        return Mage::getSingleton('adminhtml/url')
                ->getUrl(
                    'innoproductquestions/adminhtml_question/edit', 
                    array('id' => $this->getId())
                );
    }
}