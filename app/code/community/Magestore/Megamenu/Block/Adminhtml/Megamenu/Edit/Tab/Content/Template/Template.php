<?php

class Magestore_Megamenu_Block_Adminhtml_Template_Template extends Mage_Adminhtml_Block_Widget_Form
{
  public function __construct(){
	
      parent::__construct();
      $this->setTemplate('megamenu/template.phtml');
	
	}

    protected function _prepareLayout()
    {     
        $this->setChild('load_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                        'label'   => Mage::helper('adminhtml')->__('Load Template'),
                        'onclick' => 'templateControl.load();',
                        'type'    => 'button',
                        'class'   => 'save'
                    )
                )
        );
        
        $this->setChild('form',
            $this->getLayout()->createBlock('megamenu/adminhtml_template_edit_tab_template')
        );
        return parent::_prepareLayout();
    }
 
    public function getFormHtml()
    {
        return $this->getChildHtml('form');
    }
    
    public function getLoadButtonHtml()
    {
        return $this->getChildHtml('load_button');
    }
    
    public function getCollectionTemplate(){
        $collection = Mage::getModel('megamenu/template')->getCollection();
        return $collection;
    }
    
    public function getLoadUrl(){        
        return $this->getUrl('*/*/gettemplate');
    }
    
    public function getSaveUrl(){        
        return $this->getUrl('*/*/save');
    }
}