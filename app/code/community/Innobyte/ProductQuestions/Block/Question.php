<?php

class Innobyte_ProductQuestions_Block_Question extends Mage_Core_Block_Template 
{   
    const GENERAL_XML_PATH = 'Innobyte_ProductQuestions/settings';
    
    const STATUS_RESPONSED = 1;
    const STATUS_NOT_RESPONSED = 0;
    
    const NOT_VISIBLE = 0;
    const VISIBLE = 1;
    
    const SORT_BY_DATE_CREATED = 1;
    const SORT_BY_VOTES_NR = 0;
        
    public $sortBy  = self::SORT_BY_DATE_CREATED;
    public $sortDir = 'DESC';
    public $limitMultiplier = 1;

    protected function _toHtml()
    {
        if (!$this->isProductQuestionsEnabled()) {
            return '';
        }
        
        return parent::_toHtml();
    }
    
    public function getQuestion() 
    {
        if (!$this->hasData('question')) {
            $this->setData('question', Mage::registry('question'));
        }
        return $this->getData('question');
    }
    
    public function getSortingOptions()
    {
        $settings = Mage::getStoreConfig(self::GENERAL_XML_PATH
                . '/customersorting');
        $sortingOptions = explode(",", $settings);

        return $sortingOptions;
    }
  
    public function getAction()
    {
        return '';
    }
    
    public function allowProductQuestions()
    {
        return Mage::getStoreConfig(self::GENERAL_XML_PATH
                .'/can_add_questions');
    }
    
    public function isProductQuestionsEnabled()
    {
        return Mage::getStoreConfig(self::GENERAL_XML_PATH
                .'/active');
    }
    
    public function allowGuestToAskQuestions()
    {
        $allowGuest = Mage::getStoreConfig(self::GENERAL_XML_PATH
                .'/guestallowed');
               
        if ($allowGuest) {
            return true;
        } else {
            return $this->helper('customer')->isLoggedIn();
        }
    }
    
    public function allowGuestToVoteQuestions()
    {
        $allowGuest = Mage::getStoreConfig(self::GENERAL_XML_PATH
                .'/guestcanvote');
               
        if ($allowGuest) {
            return true;
        } else {
            return $this->helper('customer')->isLoggedIn();
        }
    }
    
    public function getGuestMessage()
    {
        Mage::getSingleton('customer/session')
            ->setBeforeAuthUrl(Mage::helper('core/url')->getCurrentUrl());

        return Mage::getStoreConfig(self::GENERAL_XML_PATH
                .'/guestmessage');
    }
    
    public function allowPrivateQuestions()
    {
        return Mage::getStoreConfig(self::GENERAL_XML_PATH
                .'/customervisibilityallowed');
    }
    
    
    public function setFilters($sortBy, $sortDir, $limitMultiplier)
    {
        $this->sortBy = $sortBy;
        $this->sortDir = $sortDir;
        $this->limitMultiplier = $limitMultiplier;
    }
  
    /**
     * extract all questions with answer for given product
     * @param type $productId
     * @return collection
     */
    public function getAskedQuestions($productId)
    {
        // Get current store id 
        $storeId = Mage::app()->getStore()->getId();

        // Get Config
        $noQuestions = Mage::getStoreConfig(self::GENERAL_XML_PATH . '/numberquestions', $storeId);
        $limit = $noQuestions * $this->limitMultiplier;
        
        $sortBy = 'votes';
        if ($this->sortBy == self::SORT_BY_DATE_CREATED) {
            $sortBy = 'created';
        }

        $collection = Mage::getModel('innobyte_product_questions/question')->getCollection();
        $collection->setPageSize($limit + 1) // +1 is added to verify if "Show more questions button should show or not"
            ->setCurPage(1)
            ->addFieldToFilter('product_id', array('eq' => $productId))
            ->addFieldToFilter('status', array('eq' => self::STATUS_RESPONSED))
            ->addFieldToFilter('visibility', array('eq' => self::VISIBLE))
            ->setOrder($sortBy, $this->sortDir);
        
        $data['collection'] = $collection;
        $data['requested'] = $limit;
        $data['extracted'] = $collection->count();
        
        return $data;
    }
    
    /**
     * check if question has vote from current customer
     * @return boolean
     */
    protected function questionHasVote($questionId)
    {
        //get model
        $model = Mage::getModel('innobyte_product_questions/votes')->getCollection()
            ->addFieldToFilter('question_id', array('eq' => $questionId));
            
        // if user is logged in filter question by user_id
        if(Mage::getSingleton('customer/session')->isLoggedIn()){
            $model->addFieldToFilter('user_id',
                               array('eq' => (int) Mage::getSingleton('customer/session')->getCustomer()->getId()));
        }
        // if user is no logged in filter question by ip address
        else {
            $model->addFieldToFilter('ip',
                               array('eq' => long2ip(Mage::helper('core/http')->getRemoteAddr(true))));
        }
        
        $question = $model->getData();
        
        if ($model->count() > 0) {
            return $question;
        }
        return false;
    }

}