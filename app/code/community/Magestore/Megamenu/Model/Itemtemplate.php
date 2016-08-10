<?php

class Magestore_Megamenu_Model_Itemtemplate extends Mage_Core_Model_Abstract
{
	public function _construct(){
		parent::_construct();
		$this->_init('megamenu/itemtemplate');
	}
    
    public function getFolder(){
        $folder = '';
        if($this->getId()){
            switch ($this->getMenuType()) {
                case '1':
                    $folder = 'content_only';
                    break;
                case '2':
                    $folder = 'product_listing';
                    break;
                case '3':
                    $folder = 'category_listing';
                    break;
                case '4':
                    $folder = 'contact_form';
                    break;
                case '5':
                    $folder = 'group_category_listing';
                    break;
                case '6':
                    $folder = 'anchor_text';
                default:
                    break;
            }
        }
        return $folder;
    }
}