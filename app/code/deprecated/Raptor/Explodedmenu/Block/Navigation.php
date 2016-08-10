<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog navigation
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Raptor_Explodedmenu_Block_Navigation extends Mage_Catalog_Block_Navigation
{
    private $_parentCategoryName = '';
    private $_blockPrefix = 'great_value_deal_';
    protected function _construct()
    {
        $secure = Mage::app()->getStore()->isCurrentlySecure() ? 'secure': 'unsecure';
        $storeId = Mage::app()->getStore()->getId();
        $this->addData(array(
            'cache_lifetime'    => false,
            'cache_tags'        => array(Mage_Catalog_Model_Category::CACHE_TAG, Mage_Core_Model_Store_Group::CACHE_TAG),
            'cache_key' => $storeId.'-'.$secure
        ));
    }

    public function drawItem($category, $level=0, $last=false) {
        $layer = Mage::getSingleton('catalog/layer');
        $_category = $layer->getCurrentCategory();
        $currentCategoryId = $_category->getId();

        $html = '';
        if (!$category->getIsActive()) {
            return $html;
        }
        $activeChildren = $this->getActiveChildren($category);
        $html.= '<li class="top_level nav-' . $this->getCategoryPath($category);
        if ($this->isCategoryActive($category)) {
            $html.= ' active';
        }
        $html .= '"';
        if (sizeof($activeChildren) > 0) {
            $html.= ' onmouseover="toggleMenu(this,1)" onmouseout="toggleMenu(this,0)"';        }


        if ($last) {
            $html .= ' last';
        }
        $html.= '>'."\n";

        if($currentCategoryId == $category->getId()) {
            $html.= '<a href="'.$this->getCategoryUrl($category).'"  class="select"><span>'.$this->htmlEscape($category->getName()).'</span></a>'."\n";
            $this->_parentCategoryName = $category->getName();
            if (sizeof($activeChildren) > 0) {
                $html .= $this->drawColumns($activeChildren,$category->getId());
            }
            $html .= "</li>";
         } else {
             $html.= '<a href="'.$this->getCategoryUrl($category).'"><span>'.$this->htmlEscape($category->getName()).'</span></a>'."\n";
            $this->_parentCategoryName = $category->getName();
            if (sizeof($activeChildren) > 0) {
                $html .= $this->drawColumns($activeChildren,$currentCategoryId,$category->getId());
            }
            $html .= "</li>";
         }
        return $html;
    }

    /**
     * Responsible for splitting the drop down box into columns and rendering the nested menus
     *
     * @param unknown_type $children
     * @return unknown
     */
    public function drawColumns($children,$id) {
        if ($id == 25)
        {
            $categoriesPerColumn = 12;

        }
        else
        {
            $categoriesPerColumn = $this->getCategoriesPerColumn();
        }
        $html = '';
        $chunks = array_chunk($children, $categoriesPerColumn);
        $chunkCount  = count($chunks);

        if(Mage::getModel('cms/block')->load($this->_blockPrefix.(str_replace(' ','_', strtolower($this->_parentCategoryName))))->getIsActive())
        {
            $chunkCount =$chunkCount+1;
            $html .= '<ul class="column-count-'.$chunkCount.'"><div class="mega-sep">';
        }
        else{
            $html .= '<ul class="column-count-'.$chunkCount.'"><div class="mega-sep">';
        }
        $i = 0;
        foreach ($chunks as $key=>$value) {
            $html .= '<li class="columns col_' . $i . '">';
            $html .= $this->drawNestedMenus($value, 1);
            $html .= '</li>';
            $i++;
        }

        // Added for static block image
        if(Mage::getModel('cms/block')->load($this->_blockPrefix.(str_replace(' ','_', strtolower($this->_parentCategoryName))))->getIsActive())
        {
            $html .= '<li class="last columns col_' . $i . '">';
            $html .= $this->getLayout()->createBlock('cms/block')->setBlockId($this->_blockPrefix.(str_replace(' ','_', strtolower($this->_parentCategoryName))))->toHtml();
            $html .= '</li>';
        }
        $html .= '</div></ul>';
        return $html;
    }

    public function drawNestedMenus($children,$level=1) {
    	$selectedaCtegory = '';
        $html = '<ul>';
        $currentCategory = Mage::registry('current_category');
        if($currentCategory){
        	$selectedaCtegory = $currentCategory->getId();
        }
        foreach ($children as $child) {

            if ($child->getIsActive()) {
                $html .= '<li class="level' . $level . '">';

				if(198 != $child->getId() && 136 != $child->getId()){
               		if ($selectedaCtegory == $child->getId()){
                        $html .= '<a href="'.$this->getCategoryUrl($child).'" class="select"><span>'.$this->htmlEscape($child->getName()).$this->_getProductCount($child, $level).'</span></a>';
                    } else {
                        $html .= '<a href="'.$this->getCategoryUrl($child).'"><span>'.$this->htmlEscape($child->getName()).$this->_getProductCount($child, $level).'</span></a>';
                    }
				}
                $activeChildren = $this->getActiveChildren($child);
                if (sizeof($activeChildren) > 0) {
                    $html .= $this->drawNestedMenus($activeChildren, $level+1);
                }
                $html .= '</li>';
            }
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * Gets all the active children of a category and puts them into an array. N.B.
     * we need an array because of the array_chunk() call in drawColumns();
     *
     * @param Category $parent
     * @return unknown
     */
    protected function getActiveChildren($parent) {
        $activeChildren = array();
        if (Mage::helper('catalog/category_flat')->isEnabled()) {
            $children = $parent->getChildrenNodes();
            $childrenCount = count($children);
        } else {
            $children = $parent->getChildren();
            $childrenCount = $children->count();
        }
        $hasChildren = $children && $childrenCount;
        if ($hasChildren) {
            foreach ($children as $child) {
                if ($child->getIsActive()) {
                    array_push($activeChildren, $child);
                }
            }
        }
        return $activeChildren;
    }

    protected function getCategoriesPerColumn() {
        $config = Mage::getStoreConfig('explodedmenu');
            if (isset($config['columns']) && isset($config['columns']['categories_per_column'])) {
                return $config['columns']['categories_per_column'];
            }
        else return 3;
    }

    /**
     * Get url for category data
     *
     * @param Mage_Catalog_Model_Category $category
     * @return string
     */
    public function getCategoryPath($category)
    {
    if ($category instanceof Mage_Catalog_Model_Category) {
        echo ("<!-- is category -->");
            $url = $category->getPathInStore();
        $url = strtr($url, ".", "-");
        $url = strtr($url, "/", "-");
        } else {
        echo ("<!-- is not category -->");
            $url = $this->_getCategoryInstance()
                ->setData($category->getData())
                ->getRequestPath();
        $url = strtr($url, ".", "-");
        $url = strtr($url, "/", "-");
        }
        return $url;
    }

    /**
     * Get product count for category of level 2
     *
     * @param $category - category object
     * @param $level - level of category
     * @return string product count enclosed in brackets
     */
    private function _getProductCount($category, $level) {
        if($level > 1)
        {
          /*  $attributeId = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'status')->getAttributeId();

        	$productTable = Mage::getSingleton('core/resource')->getTableName('catalog/category_product');
        	$productEntityIntTable = Mage::getSingleton('core/resource')->getTableName(array('catalog/product', 'int'));
            $productWebsiteTable = Mage::getSingleton('core/resource')->getTableName('catalog/product_website');
            $resourceModel = Mage::getResourceModel('catalog/category');

        	$select = $resourceModel->getReadConnection()->select()
            	->from(
                	array('main_table' => $productTable),
                	array(new Zend_Db_Expr('COUNT(main_table.product_id)'))
            	)
            	->join(array('product_entity_int' => $productEntityIntTable),
                    'main_table.product_id = product_entity_int.entity_id',
            	    array())
            	->join(array('product_website' => $productWebsiteTable),
                    'main_table.product_id = product_website.product_id',
            	    array())
            	->where('main_table.category_id = :category_id')
            	->where('product_entity_int.attribute_id = :attribute_id')
            	->where('product_entity_int.value = :value')
            	->where('product_website.website_id = :website_id');

        	$bind = array('category_id' => (int)$category->getId(),
        				  'attribute_id' => (int)$attributeId,
        				  'value' => 1,
        				  'website_id' => (int)Mage::app()->getStore()->getWebsiteId()
        				 );
		
        	$counts = $resourceModel->getReadConnection()->fetchOne($select, $bind);
			if(intval($counts) > 0) return ' ('.intval($counts).')';*/

$resourceModel = Mage::getResourceModel('catalog/category');
$productWebsiteTable = Mage::getSingleton('core/resource')->getTableName('catalog/category_product_index');
               $sql = 'select count(product_id) from '.$productWebsiteTable.' where category_id="'.$category->getId().'" AND store_id ="'.(int)Mage::app()->getStore()->getId().'"';
               $counts = $resourceModel->getReadConnection()->fetchOne($sql);
                       if(intval($counts) > 0) return ' ('.intval($counts).')';

        }
    }

}
