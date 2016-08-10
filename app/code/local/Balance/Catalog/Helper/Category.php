<?php

/**
 * Class Balance_Catalog_Helper_Category
 *
 * @author Derek Li
 */
class Balance_Catalog_Helper_Category extends Mage_Core_Helper_Data
{
    /**
     * The category paths.
     *
     * @var array
     */
    protected $_requestPaths = array();

    /**
     * Set the category path.
     *
     * @param Mage_Catalog_Model_Category $category The category.
     * @param string $path Request path to set.
     * @return $this
     */
    public function setRequestPath(Mage_Catalog_Model_Category $category, $path)
    {
        if ($category->getId()) {
            $this->_requestPaths[$category->getId()] = $path;
        }
        return $this;
    }

    /**
     * Get the request path from the category.
     *
     * @param Mage_Catalog_Model_Category $category The category.
     * @return mixed Request path if found or false if not found.
     */
    public function getRequestPath(Mage_Catalog_Model_Category $category)
    {
        if (!$category->getId()) {
            return false;
        }
        if (!array_key_exists($category->getId(), $this->_requestPaths)) {
            $this->_loadRequestPath($category);
        }
        return $this->_requestPaths[$category->getId()];
    }

    /**
     * Load category request path.5
     *
     * @param Mage_Catalog_Model_Category $category
     * @return $this
     */
    protected function _loadRequestPath(Mage_Catalog_Model_Category $category)
    {
        if ($category->getId()) {
            $categoryRewrite = Mage::getModel('enterprise_catalog/category')->loadByCategory($category);
            if ($categoryRewrite->getId()) {
                $this->_requestPaths[$category->getId()] = $categoryRewrite->getRequestPath();
            } else {
                $this->_requestPaths[$category->getId()] = null;
            }
        }
    }
}
