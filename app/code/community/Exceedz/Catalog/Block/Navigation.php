<?php
/**
 * Catalog navigation
 *
 * @category   Exceedz
 * @package    Exceedz_Catalog
 */
class Exceedz_Catalog_Block_Navigation extends Mage_Catalog_Block_Navigation
{
    private $_showSubCategoriesFor = array('Homewares', 'Designers');
    private $_skipSubCategories = array('Homewares', 'Designers');
    private $_skipMainCategories = array('Specials');
    private $_displayCount = 0;
    private $_displayLimit = 8;
    private $_currentCategory;
    private $_currentCategoryUrl;
    /**
     * Render categories menu in HTML
     *
     * @param int Level number for list item class to start from
     * @param string Extra class of outermost list items
     * @param string If specified wraps children list in div with this class
     * @return string
     */
    public function renderFooterCategoriesHtml($level = 0, $outermostItemClass = '', $childrenWrapClass = '')
    {
        $activeCategories = array();
        foreach ($this->getStoreCategories() as $child) {
            if ($child->getIsActive()) {
                $activeCategories[] = $child;
            }
        }
        $activeCategoriesCount = count($activeCategories);
        $hasActiveCategoriesCount = ($activeCategoriesCount > 0);

        if (!$hasActiveCategoriesCount) {
            return '';
        }

        $html = '';
        $j = 0;
        foreach ($activeCategories as $category) {
            if(in_array($category->getName(), $this->_skipMainCategories)) continue;
            $this->_currentCategory = $category->getName();
            $this->_currentCategoryUrl = $this->getCategoryUrl($category);
            $html .= $this->_renderFooterCategoriesHtml(
                $category,
                $level,
                ($j == $activeCategoriesCount - 1),
                ($j == 0),
                true,
                $outermostItemClass,
                $childrenWrapClass,
                true
            );
            $j++;
        }

        return $html;
    }

    /**
     * Render category to html
     *
     * @param Mage_Catalog_Model_Category $category
     * @param int Nesting level number
     * @param boolean Whether ot not this item is last, affects list item class
     * @param boolean Whether ot not this item is first, affects list item class
     * @param boolean Whether ot not this item is outermost, affects list item class
     * @param string Extra class of outermost list items
     * @param string If specified wraps children list in div with this class
     * @param boolean Whether ot not to add on* attributes to list item
     * @return string
     */
    protected function _renderFooterCategoriesHtml($category, $level = 0, $isLast = false, $isFirst = false,
        $isOutermost = false, $outermostItemClass = '', $childrenWrapClass = '', $noEventAttributes = false)
    {

        if (!$category->getIsActive()) {
            return '';
        }
        $html = array();
        $class = '';

        // get all children
        if (Mage::helper('catalog/category_flat')->isEnabled()) {
            $children = (array)$category->getChildrenNodes();
            $childrenCount = count($children);
        } else {
            $children = $category->getChildren();
            $childrenCount = $children->count();
        }
        $hasChildren = ($children && $childrenCount);

        // select active children
        $activeChildren = array();
        foreach ($children as $child) {
            if ($child->getIsActive()) {
                $activeChildren[] = $child;
            }
        }
        $activeChildrenCount = count($activeChildren);
        $hasActiveChildren = ($activeChildrenCount > 0);

        if(is_object(Mage::registry('current_category'))) {
            if (Mage::registry('current_category')->getId() == $category->getId()) {
                $class = ' class="select"';
            }
        }

        // assemble list item with attributes
        if($level == 0)  {
            $html[] = '<dl>';

            $html[] = '<dt'.$class. '>';
            $html[] = '<a href="'.$this->getCategoryUrl($category).'"'.$class. ' title="' . $this->escapeHtml($category->getName()) . '">';
            $html[] = '<h3>' . $this->escapeHtml($category->getName()) . '</h3>';
            $html[] = '</a>';
            $html[] = '</dt>';

            $this->_displayCount = 0;
        } else if(!in_array($category->getName(), $this->_skipSubCategories)) {
            $html[] = '<dd'.$class. '>';
            $html[] = '<a href="'.$this->getCategoryUrl($category).'"'.$class. ' title="' . $this->escapeHtml($category->getName()) . '">';
            $html[] = $this->escapeHtml($category->getName());
            $html[] = '</a>';
            $html[] = $this->_getProductCount($category, $level);
            $html[] = '</dd>';
            $this->_displayCount++;
        }

        if(in_array($category->getName(), $this->_showSubCategoriesFor) || $level < 2) {
            // render children
            $htmlChildren = '';
            $j = 0;
            foreach ($activeChildren as $child) {
                if(($level > 0 && !in_array($this->_currentCategory, $this->_showSubCategoriesFor)) || $this->_displayCount >= $this->_displayLimit) break;

                $htmlChildren .= $this->_renderFooterCategoriesHtml(
                    $child,
                    ($level + 1),
                    ($j == $activeChildrenCount - 1),
                    ($j == 0),
                    false,
                    $outermostItemClass,
                    $childrenWrapClass,
                    $noEventAttributes
                );
                $j++;
            }
            if (!empty($htmlChildren)) {
                $html[] = '<dd class="hide">';
                $html[] = $htmlChildren;
                $html[] = '</dd>';
            }
        }

        if($level == 0) $html[] = '</dl>';
        if($level > 1 && $this->_displayCount >= $this->_displayLimit) {
                    $html[] = '<dd>';
                    $html[] = '<a href="'.$this->_currentCategoryUrl.'" title="' . $this->escapeHtml($this->_currentCategory) . '">';
                    $html[] = $this->__('More..', true);
                    $html[] = '</a>';
                    $html[] = '</dd>';
                }

        $html = implode("\n", $html);
        return $html;
    }

    /**
     * Get product count for category of level 2
     *
     * @param $category - category object
     * @param $level - level of category
     * @return string product count enclosed in brackets
     */
    private function _getProductCount($category, $level) {
        $categories = Mage::getModel('catalog/category')->load($category->getId());
        $productCollections = $categories->getProductCollection();
        $counts = count($productCollections);
        if(intval($counts) > 0) return ' ('.intval($counts).')';
    }
}
