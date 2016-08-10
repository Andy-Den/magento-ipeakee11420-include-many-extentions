<?php

class Magestore_Megamenu_Model_Megamenu extends Mage_Core_Model_Abstract
{
    const CONTENT_ONLY = 1;
    const PRODUCT_LISTING = 2;
    const CATEGORY_LISTING = 3;
    const CONTACT_FORM = 4;
    const GROUP_CATEGORY_LISTING = 5;
    const ANCHOR_TEXT = 6;
    const PRODUCT_GRID =7 ;


    protected $_eventPrefix = 'megamenu_item';
    protected $_eventObject = 'megamenu_item';
    
    protected $_parentCategories;
    protected $_categoryCollection;


    protected $_html;


    public function _construct(){
		parent::_construct();
		$this->_init('megamenu/megamenu');
	}
    
    /**
     * get menu html from database
     * @return string
     */
    public function getMenuHtml(){
        if($this->_html)
            return $this->_html;
        $html = '';
        if($this->getId()){
            $mode = $this->getStyleShow();
            $html = $this->getContentOnlyHtml();
        }
        return $html;
    }
    
    public function getContentOnlyHtml(){
        $template = $this->getCodeTemplate();
        return $template;
    }
    
    public function getFeaturedProductIds(){
        $productIds = array();
        if($this->getId()){
            $productIds = explode(',', $this->getFeaturedProducts());
        }
        return $productIds;
    }
    
    public function getTemplateFilename(){
        $filename = '';
        if($this->getId()){
            $menu_type = $this->getMenuType();
            if($menu_type == self::CONTENT_ONLY){
                $filename = 'content_only/default.phtml';
            }elseif($menu_type == self::PRODUCT_LISTING){
                $filename = 'product_listing/general_products.phtml';
            }elseif($menu_type == self::CATEGORY_LISTING){
                $filename = 'category_listing/detailed_categories.phtml';
            }elseif($menu_type == self::CONTACT_FORM){
                $filename = 'contact_form/default.phtml';
            }elseif ($menu_type == self::GROUP_CATEGORY_LISTING) {
                $filename = 'anchor_text/default.phtml';
            }elseif ($menu_type == self::ANCHOR_TEXT) {
                $filename = 'anchor_text/default.phtml';
            }elseif ($menu_type == self::PRODUCT_GRID) {
                $filename = 'product_listing/detailed_products.phtml';
            }
        }
        return $filename;
    }
    public function getTemplateFilenameforMobile(){
        $filename = '';
        if($this->getId()){
            $menu_type = $this->getMenuType();
            if($menu_type == self::CONTENT_ONLY){
                $filename = 'content_only/default.phtml';
            }elseif($menu_type == self::PRODUCT_LISTING){
                $filename = 'product_listing/general_products.phtml';
            }elseif($menu_type == self::CATEGORY_LISTING){
                $filename = 'category_listing/detailed_categories.phtml';
            }elseif($menu_type == self::CONTACT_FORM){
                $filename = 'contact_form/mobile_default.phtml';
            }elseif ($menu_type == self::GROUP_CATEGORY_LISTING) {
                $filename = 'anchor_text/default.phtml';
            }elseif ($menu_type == self::ANCHOR_TEXT) {
                $filename = 'anchor_text/default.phtml';
            }elseif ($menu_type == self::PRODUCT_GRID) {
                $filename = 'product_listing/detailed_products.phtml';
            }
        }
        return $filename;
    }
    public function getMenutypeOptions() {
        return array(
            array(
                'label' => 'Content',
                'value' => self::CONTENT_ONLY
            ),
            array(
                'label' => 'Product Listing',
                'value' => self::PRODUCT_LISTING
            ),
            array(
                'label' => 'Product Grid',
                'value' => self::PRODUCT_GRID
            ),
            array(
                'label' => 'Category Listing',
                'value' => self::CATEGORY_LISTING
            ),
            array(
                'label' => 'Contact Form',
                'value' => self::CONTACT_FORM
            ),
            array(
                'label' => 'Anchor Text',
                'value' => self::ANCHOR_TEXT
            ),
        );
    }
    public function getSubmenualignOptions() {
        return array(
            array(
                'label' => 'From left menu',
                'value' => 0
            ),
            array(
                'label' => 'From right menu',
                'value' => 1
            ),
            array(
                'label' => 'From left item',
                'value' => 2
            ),
            array(
                'label' => 'From right item',
                'value' => 3
            ),
        );
    }
    public function getLeftSubmenualignOptions() {
        return array(
            array(
                'label' => 'From top menu',
                'value' => 0
            ),
            array(
                'label' => 'From top item',
                'value' => 1
            ),
           
        );
    }
    public function getMegamenutypeOptions() {
        return array(
            array(
                'label' => 'Top Menu',
                'value' => 0
            ),
            array(
                'label' => 'Left Menu',
                'value' => 1
            ),
           
        );
    }
    
    public function getCategoryCollection($store = null){
         if(is_null($this->_categoryCollection)){
            $data = $this->getData('menu_item');
            $catIds = array(0);
            if($this->getId()){
                $catIds = explode(', ', $this->getCategories());
            }
            
            $collection = Mage::getResourceModel('catalog/category_collection')
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', array('in' => $catIds))
                ->addFieldToFilter('is_active', 1)
				->setOrder('position','ASC');
            if(!is_null($store))
                $collection->setStore($store);
            $this->_categoryCollection = $collection;
        }
        return $this->_categoryCollection;
    }
    
    public function getParentCategories($store = null){
        if(is_null($this->_parentCategories)){
            $parentIds = array();
            $categories = $this->getCategoryCollection();
            $categoryIds = $categories->getAllIds();
            foreach($categories as $category){
                $parents = $category->getParentIds();
                if(count(array_intersect($parents, $categoryIds))== 0)
                        $parentIds[] = $category->getId();
            }
            $collection = Mage::getResourceModel('catalog/category_collection')
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', array('in' => $parentIds))
                ->addFieldToFilter('is_active', 1)
				->setOrder('position','ASC');
            if(!is_null($store))
                $collection->setStore($store);
            $this->_parentCategories = $collection;
        }
        return $this->_parentCategories;
    }
    
    public function getCategoryIds(){
        $categoryIds = array();
        if($this->getId()){
            $stringIds = $this->getCategories();
            $categoryIds = explode(', ', $stringIds);
        }
        return $categoryIds;
    }
    public function getCategorytypeOptions(){
        return array(
            array(
                'label' => 'List all items of each category in one column',
                'value' => 0
            ),
            array(
                'label' => 'Automatically arrange items of category in columns equally',
                'value' => 1
            ),  
        );
    }
    public function getCategoryshowtypeOptions(){
        return array(
            array(
                'label' => 'Show all categories selected',
                'value' => 0
            ),
            array(
                'label' => 'Show by 3 category levels',
                'value' => 1
            ),  
        );
    }
}