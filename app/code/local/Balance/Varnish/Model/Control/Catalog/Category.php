<?php

class Balance_Varnish_Model_Control_Catalog_Category
    extends Balance_Varnish_Model_Control_Abstract
{
    protected $_helperName = 'varnish/control_catalog_category';

    /**
     * Purge Category
     *
     * @param Mage_Catalog_Model_Category $category
     *
     * @return Balance_Varnish_Model_Control_Catalog_Category
     */
    public function purge(Mage_Catalog_Model_Category $category)
    {
        if ($this->_canPurge()) {
            $this->_purgeById($category->getId());
            if ($categoryName = $category->getName()) {
                $this->_getSession()->addSuccess(
                    Mage::helper('varnish')->__('Varnish cache for "%s" has been purged.', $categoryName)
                );
            }
        }

        return $this;
    }

    /**
     * Purge Category by id
     *
     * @param int $id
     *
     * @return Balance_Varnish_Model_Control_Catalog_Category
     */
    public function purgeById($id)
    {
        if ($this->_canPurge()) {
            $this->_purgeById($id);
        }

        return $this;
    }

    /**
     * Purge Category by id
     *
     * @param int $id
     *
     * @return Balance_Varnish_Model_Control_Catalog_Category
     */
    protected function _purgeById($id)
    {
        $collection = $this->_getUrlRewriteCollection()
            ->filterAllByCategoryId($id);
        foreach ($collection as $urlRewriteRule) {
            $urlRegexp = '/' . $urlRewriteRule->getRequestPath();
            $this->_getCacheControl()
                ->clean($this->_getStoreDomainList(), $urlRegexp);
        }

        return $this;
    }
}
