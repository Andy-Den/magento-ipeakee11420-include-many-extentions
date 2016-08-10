<?php

class Innobyte_ProductQuestions_Block_Adminhtml_Catalog_Product_Questions 
    extends Mage_Adminhtml_Block_Template 
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    /**
     * Set label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Product Questions');
    }

    /**
     * Set title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Product Questions');
    }

    /**
     * Show tab
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Stops tab being hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Class name of the tab, return 'ajax' to load content with Ajax
     *
     * return string
     */
    public function getTabClass()
    {
        return 'ajax';
    }

    /**
     * Generate content on load or via AJAX
     *
     * @return bool
     */
    public function getSkipGenerateContent()
    {
        return false;
    }

    /**
     * Return the URL here used to load the content by Ajax
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('adminhtml/innobyteproductquestions_question/grid',
                             array('_current' => true));
    }

}