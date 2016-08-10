<?php
class Innobyte_ProductQuestions_Block_Adminhtml_Question 
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_question';
        $this->_blockGroup = 'innobyte_product_questions';
        $this->_headerText = Mage::helper('innobyte_product_questions')
                ->__('Product Question');
        parent::__construct();
        $this->removeButton('add'); 
    }
}