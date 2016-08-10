<?php

class Innobyte_ProductQuestions_Block_Widget 
    extends Mage_Core_Block_Template 
    implements Mage_Widget_Block_Interface 
{
    private $_sortingField = array(1 => 'created',
                                   2 => 'created',
                                   3 => 'votes',
                                   4 => 'votes');
    
    private $_sortingOrder = array(1 => 'asc',
                                   2 => 'desc',
                                   3 => 'asc',
                                   4 => 'desc');

    protected function _toHtml()
    {
        if (!Mage::getStoreConfig(Innobyte_ProductQuestions_Block_Question::GENERAL_XML_PATH.'/active')) {
            return '';
        }
        
        return parent::_toHtml();
    }
    
    protected function latestProductQuestions()
    {
        $sortOption = $this->getQuestionsSorting();
        $storeId = $this->helper('core')->getStoreId();

        $collection = Mage::getModel('innobyte_product_questions/question')
                ->getCollection()
                ->addFieldToFilter('visibility', array('eq' =>  Innobyte_ProductQuestions_Block_Question::VISIBLE))
                ->addFieldToFilter('status', array('eq' => Innobyte_ProductQuestions_Block_Question::STATUS_RESPONSED))
                ->addFieldToFilter('store_id', array('eq' => $storeId))
                ->addFieldToFilter('answer', array('neq' => ""))
                ->setOrder($this->_sortingField[$sortOption], $this->_sortingOrder[$sortOption])
                ->setPageSize($this->getQuestionsCount())
                ->setCurPage(1)
                ;

        return $collection;
    }
    
    
}