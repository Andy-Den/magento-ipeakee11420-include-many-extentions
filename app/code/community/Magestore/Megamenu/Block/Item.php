<?php

class Magestore_Megamenu_Block_Item extends Mage_Core_Block_Template {

    protected $_titles = array();
    protected $_categoryCollection;
    protected $_productCollection;
    protected $_featuredCategoryCollection;
    protected $_featuredProductCollection;
    protected $_parentCategories;

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function tranlateTitle($title) {
        return $title;
    }

    public function addTitle($title) {
        $this->_titles[] = $title;
    }

    public function getItemData() {
        return $this->getData('menu_item');
    }

    public function setDefaultHeaderFooter($header, $footer) {
        $data = array(
            'header' => $header,
            'footer' => $footer
        );
        $this->setData('headerfooter', $data);
    }

    public function setDefaultGeneralStyle($background_color, $border_color, $border_size) {
        $data = array(
            'background_color' => $background_color,
            'border_color' => $border_color,
            'border_size' => $border_size
        );
        $this->setData('general_style', $data);
    }

    public function setDefaultTitleStyle($title_color, $title_background_color, $title_font, $title_font_size) {
        $data = array(
            'title_color' => $title_color,
            'title_background_color' => $title_background_color,
            'title_font' => $title_font,
            'title_font_size' => $title_font_size,
        );
        $this->setData('title_style', $data);
    }

    public function setDefaultSubtitleStyle($subtitle_color, $subtitle_font, $subtitle_font_size) {
        $data = array(
            'subtitle_color' => $subtitle_color,
            'subtitle_font' => $subtitle_font,
            'subtitle_font_size' => $subtitle_font_size,
        );
        $this->setData('subtitle_style', $data);
    }

    public function setDefaultLinkStyle($link_color, $hover_color, $link_font, $link_font_size) {
        $data = array(
            'link_color' => $link_color,
            'hover_color' => $hover_color,
            'link_font' => $link_font,
            'link_font_size' => $link_font_size,
        );
        $this->setData('link_style', $data);
    }

    public function setDefaultTextStyle($text_color, $text_font, $text_font_size) {
        $data = array(
            'text_color' => $text_color,
            'text_font' => $text_font,
            'text_font_size' => $text_font_size
        );
        $this->setData('text_style', $data);
    }

    public function setDefaultContentGeneral($mega_size, $column_number, $column_size) {
        $data = array(
            'size_megamenu' => $mega_size,
            'colum' => $column_number,
            'size_colum' => $column_size
        );
        $this->setData('content_general', $data);
    }
	

    public function getMegamenuSize() {
        $data = $this->getItemData();
        if (isset($data['size_megamenu']) && $data['size_megamenu'])
            return $data['size_megamenu'];
        return '';
    }

    /**
     * get category collection from data of menu item
     * @return category collection
     */
    public function getCategories() {
        if (is_null($this->_categoryCollection)) {
            $item = $this->getItem();
            if ($item && $item->getId()) {
                $collection = $item->getCategoryCollection();
                $this->_categoryCollection = $collection;
            }
        }
        return $this->_categoryCollection;
    }

    public function getAllChildrenOfGroup($category) {
        $categoryIds = explode(',', $category->getAllChildren());
        $collection = Mage::getResourceModel('catalog/category_collection')
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', array('in' => $categoryIds))
                ->addFieldToFilter('entity_id', array('neq' => $category->getId()))
				->setOrder('position','ASC') 
        ;
        return $collection;
    }

    public function setCategories($categoryIds) {
        if (is_array($categoryIds)) {
            $collection = Mage::getResourceModel('catalog/category_collection')
                    ->addAttributeToSelect('*')
                    ->addFieldToFilter('entity_id', array('in' => $categoryIds))
					->setOrder('position','ASC');
            $this->_categoryCollection = $collection;
        }
    }

    public function setParentCategories($categoryIds) {
        if (is_array($categoryIds)) {
            $collection = Mage::getResourceModel('catalog/category_collection')
                    ->addAttributeToSelect('*')
                    ->addFieldToFilter('entity_id', array('in' => $categoryIds))
					->setOrder('position','ASC') ;
            $this->$_parentCategories = $collection;
        }
    }

    /**
     * get product collection from data of menu item
     * @return product collection
     */
    public function getProducts($store = null) {
        if (is_null($this->_productCollection)) {
            $data = $this->getData('menu_item');
            $proIds = array(0);
            if (isset($data['products']) && $data['products']) {
                $proIds = explode(', ', $data['products']);
            }

            $collection = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect('*')
                    ->addFieldToFilter('entity_id', array('in' => $proIds))
            ;
            if ($store)
                $collection->addStoreFilter($store);
            $collection->addAttributeToFilter('status', 1);
            $visibleStatus = array(
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG, 
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
            );
            //$collection->setVisibility(Mage::getModel('catalog/product_visibility')->getVisibleInSiteIds());
            $collection->addAttributeToFilter('visibility', array('in'=>$visibleStatus));
            //Zend_debug::dump($collection->getData());//die();
            $this->_productCollection = $collection;
        }
        return $this->_productCollection;
    }

    /**
     * get featured category collection from data of menu item
     * @return category collection
     */
    public function getFeaturedCategories() {
        if (is_null($this->_featuredCategoryCollection)) {
            $data = $this->getData('menu_item');
            $catIds = array(0);
            if (isset($data['featured_categories']) && $data['featured_categories']) {
                $catIds = explode(', ', $data['featured_categories']);
            }

            $collection = Mage::getResourceModel('catalog/category_collection')
                    ->addAttributeToSelect('*')
                    ->addFieldToFilter('entity_id', $catIds)
					->setOrder('position','ASC');
            $this->_featuredCategoryCollection = $collection;
        }
        return $this->_featuredCategoryCollection;
    }

    /**
     * get featured product collection from data of menu item
     * @return product collection
     */
    public function getFeaturedProducts() {
        if (is_null($this->_featuredProductCollection)) {
            $data = $this->getData('menu_item');
            $proIds = array(0);
            if (isset($data['featured_products']) && $data['featured_products']) {
                $proIds = explode(', ', $data['featured_products']);
            }

            $collection = Mage::getResourceModel('catalog/product_collection')
                    ->addAttributeToSelect('*')
                    ->addFieldToFilter('entity_id', $proIds);
            if ($store)
               $collection->addStoreFilter($store);
            $collection->addAttributeToFilter('status', 1);
            $visibleStatus = array(
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG, 
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
            );      
            $collection->addAttributeToFilter('visibility', array('in'=>$visibleStatus));
            $this->_featuredProductCollection = $collection;
        }
        return $this->_featuredProductCollection;
    }

    public function getHeaderContent() {
        $data = $this->getItemData();
        $header = '';
        if (isset($data['header']) && $data['header']) {
            $header = $data['header'];
        }
        return $header;
    }

    public function getFooterContent() {
        $data = $this->getItemData();
        $footer = '';
        if (isset($data['footer']) && $data['footer']) {
            $footer = $data['footer'];
        }
        return $footer;
    }

    public function getTemplateBlock($template) {
        $processor = Mage::helper('cms')->getBlockTemplateProcessor();
        return $processor->filter($template);
    }

    public function getClass() {
        $data = $this->getItemData();
        $class = '';
        if (isset($data['menu_type']) && $data['menu_type']) {
            if ($data['menu_type'] == 1) {
                $class = 'block-megamenu';
            } elseif ($data['menu_type'] == 2) {
                $class = '';
            } elseif ($data['menu_type'] == 3) {
                $class = 'grid-categories-megamenu';
            } elseif ($data['menu_type'] == 4) {
                $class = '';
            }
        }
        return $class;
    }

    public function getColumnNumber() {
        $data = $this->getItemData();
        $columnNumber = '';
        if (isset($data['colum']) && $data['colum']) {
            $columnNumber = $data['colum'];
        }
        if ($columnNumber)
            return $columnNumber;
        else 
            return 4;
    }

    public function getColumnSize() {
        $data = $this->getItemData();
        $columnSize = '';
        if (isset($data['size_colum']) && $data['size_colum']) {
            $columnSize = $data['size_colum'];
        } 
        
        if ($columnSize)
            return $columnSize;
        else 
            return 200;
    }

    public function getChildren($categoryId, $childs = null) {
        $childIdsArray = is_null($childs) ? array() : $childs;
        $category = Mage::getModel('catalog/category')->load($categoryId);
        if (count($category->getChildrenCategories()) > 0) {
            $c = count($category->getChildrenCategories());
            $tmp_array = array();
            foreach ($category->getChildrenCategories() as $cat) {
                array_push($tmp_array, $cat->getId());
                if ($cat->hasChildren()) {
                    $tmp_array = array_merge($tmp_array, $this->getChildren($cat->getId()));
                }
            }
            $childIdsArray = array_merge($childIdsArray, $tmp_array);
        } else {
            return array_unique($childIdsArray);
        }
        return $childIdsArray;
    }

    /**
     * get children category collection of category
     * @param type $categoryId
     * @return category collection
     */
    /**
     * get one featured product of menu item
     * @return one product
     */
    public function getAllFeaturedProduct() {
        $featuredProducts = array(0);
        $data = array();
        if ($this->getFeaturedProducts())
            $featuredProducts = $this->getFeaturedProducts()->getAllIds();
        foreach ($featuredProducts as $productId){
            $data[] = Mage::getModel('catalog/product')->load($productId);
        }
        return $data;
    }

    /**
     * get one featured category of menu item
     * @return one category
     */
    public function getAllFeaturedCategory() {
        $featuredCategories = array(0);
        $data = array();
        if ($this->getFeaturedCategories())
            $featuredCategories = $this->getFeaturedCategories()->getAllIds();
        foreach($featuredCategories as $featuredCategory){
            $data[] = $featuredCategory = Mage::getModel('catalog/category')->load($featuredCategory);
        }
        return $data;
    }

    /**
     * check menu item has featured products or no
     * @return boolean
     */
    public function hasFeaturedProducts() {
        $data = $this->getItemData();
        if (isset($data['featured_type']) && $data['featured_type']) {
            if ($data['featured_type'] == '1') {
                if ($this->getFeaturedProducts() && count($this->getFeaturedProducts()))
                    return true;
            }
        }
        return false;
    }

    /**
     * check menu item has featured item or no
     * @return boolean
     */
    public function hasFeaturedItem() {
        if ($this->hasFeaturedProducts() || $this->hasFeaturedCategories() || $this->hasFeaturedContent())
            return true;
        return false;
    }

    /**
     * check menu item has featured categories or no
     * @return boolean
     */
    public function hasFeaturedCategories() {
        $data = $this->getItemData();
        if (isset($data['featured_type']) && $data['featured_type']) {
            if ($data['featured_type'] == '2') {
                if ($this->getFeaturedCategories() && count($this->getFeaturedCategories()))
                    return true;
            }
        }
        return false;
    }
    public function hasFeaturedContent(){
        $data = $this->getItemData();
        if (isset($data['featured_type']) && $data['featured_type']) {
            if ($data['featured_type'] == '3') {
               
                    return true;
            }
        }
        return false;
    }

    /**
     * get value  of style in custom style tab
     * @param type $field
     * @return string
     */
    public function getCustomStyle($field) {
        $data = $this->getItemData();
        if (isset($data[$field]) && $data[$field])
            return $data[$field];
        else
            return $this->getDefaultValue($field);
    }

    /**
     * get default value of some field in database
     * @param type $field
     * @return string
     */
    public function getDefaultValue($field) {
        $defaultValues = array(
            'background_color' => '#FFFFFF',
            'border_color' => 'D5D5D5',
            'border_size' => '1',
            'title_color' => '000000',
            'title_background_color' => '',
            'title_font' => '',
            'title_font_size' => '13',
            'subtitle_color' => '000000',
            'subtitle_font' => '',
            'subtitle_font_size' => '',
            'link_color' => '000000',
            'hover_color' => '',
            'link_font' => '',
            'link_font_size' => '',
            'text_color' => '000000',
            'text_font' => '',
            'text_font_size' => '',
        );
        if (key_exists($field, $defaultValues))
            return $defaultValues[$field];
        return '';
    }

    public function isGroupCategoryListing() {
        $data = $this->getItemData();
        if (isset($data['menu_type']) && $data['menu_type']) {
            if ($data['menu_type'] == '5') {
                return true;
            }
        }
        return false;
    }

    /**
     * get parent category collection
     * @return category collection
     */
    public function getParentCategories() {
        if (is_null($this->_parentCategories)) {
            $item = $this->getItem();
            if ($item && $item->getId()) {
                $collection = $item->getParentCategories();
                $this->_parentCategories = $collection;
            }
        }
        return $this->_parentCategories;
    }
    public function getChildrenCollection($category) {
        if (is_object($category)) {
            $item = $this->getItem();
            $childrenIds = $category->getAllChildren();
            $childrenIds = explode(',', $childrenIds);
            $childrenIds = array_intersect($childrenIds, $item->getCategoryIds());
            $categoryCollection = Mage::getResourceModel('catalog/category_collection')
                    ->addFieldToFilter('entity_id', array('in' => $childrenIds))
                    ->addFieldToFilter('entity_id', array('neq' => $category->getId()))
					->setOrder('position','ASC')
                    ->addAttributeToSelect('*');
            return $categoryCollection;
        }
        return null;
    }
    public function getAllCategory(){
        $collection = $this->getParentCategories();
        $item = $this->getItem();
        $data = array();
        foreach ($collection as $category){
            $category->setLevel(1);
            $data[] = $category;
            if($category->hasChildren()){
                $children = $this->getChildrenCollection($category);
                foreach ($children as $child){
                    if(in_array($child, $data)) continue;
                    $child->setLevel(2);
                    $data[] = $child; 
                }
            }
        }
        
        return $data;
    }
    public function getChildrenCategoriesbByLevel($category,$level){
        if($level == 2){
            $childrens = $category->getChildrenCategories()
                                   ->addFieldToFilter('entity_id',array('in'=>$this->getItem()->getCategoryIds()))
                                   ->addFieldToFilter('entity_id', array('neq' => $category->getId()))
                                   ->setOrder('position','ASC')
                                   ->addAttributeToSelect('*');    
        }elseif($level == 3){
            $childrenIds = $category->getAllChildren();
            $childrenIds = explode(',', $childrenIds);
            $childrenIds = array_intersect($childrenIds, $this->getItem()->getCategoryIds());
            $childrens = Mage::getResourceModel('catalog/category_collection')
                    ->addFieldToFilter('entity_id', array('in' => $childrenIds))
                    ->addFieldToFilter('entity_id', array('neq' => $category->getId()))
                    ->setOrder('position','ASC')
                    ->addAttributeToSelect('*');           
        }
        return $childrens;
    }

    public function positionIsAuto() {
        $store = Mage::app()->getStore()->getId();
        $positionType = Mage::getStoreConfig('megamenu/general/menu_position_type', $store);
        if ($positionType == 1) {
            return true;
        }
        return false;
    }

    public function limitString($string, $limit = 100) {
        // Return early if the string is already shorter than the limit
        if (strlen($string) < $limit) {
            return $string;
        }

        $regex = "/(.{1,$limit})\b/";
        preg_match($regex, $string, $matches);
        return $matches[1];
    }

    /**
     * check product has image ?
     * @param type boolean
     */
    public function hasImage($product) {
        if ($product && $product->getId()) {
            if ($product->getImage() != 'no_selection' || $product->getSmallImage() != 'no_selection' || $product->getThumbnail() != 'no_selection')
                return true;
        }
        return false;
    }

    /**
     * get product image path
     * @param type $product
     * @param type $size
     * @return path of product image
     */
    public function getImagePath($product, $size) {
        $helper = $this->helper('catalog/image');
        try {
            if ($product->getSmallImage() != 'no_selection') {
                return $this->helper('catalog/image')->init($product, 'small_image')->resize($size);
            } elseif ($product->getThumbnail() != 'no_selection') {
                return $this->helper('catalog/image')->init($product, 'thumbnail_image')->resize($size);
            } elseif ($product->getImage() != 'no_selection') {
                return $this->helper('catalog/image')->init($product, 'image')->resize($size);
            } else {
                if (Mage::getStoreConfig('catalog/placeholder/small_image_placeholder'))
                    return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'media/catalog/product/placeholder/' . Mage::getStoreConfig('catalog/placeholder/small_image_placeholder');
                    return Mage::getDesign()->getSkinUrl('images/catalog/product/placeholder/small_image.jpg', array('_area' => 'frontend'));
                }
        } catch (Exception $e) {
            return Mage::getDesign()->getSkinUrl('images/catalog/product/placeholder/small_image.jpg', array('_area' => 'frontend'));
        }
    }
    
    
    public function setTemplateFileName($name){
		 $this->setData('template_name', $name);
	}

}