<?php

class Balance_Exclusion_Block_Adminhtml_Exclusion_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('exclusion_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('exclusion')->__('Term Information'));

    }

    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_section',
            array(
                'label'   => Mage::helper('exclusion')->__('Term Information'),
                'title'   => Mage::helper('exclusion')->__('Term Information'),
                'content' => $this->getLayout()->createBlock('exclusion/adminhtml_exclusion_edit_tab_form')->toHtml(),
            )
        );

        return parent::_beforeToHtml();
    }


}