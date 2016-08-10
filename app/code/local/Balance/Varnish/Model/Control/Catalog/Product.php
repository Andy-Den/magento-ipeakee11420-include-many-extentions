<?php

class Balance_Varnish_Model_Control_Catalog_Product
    extends Balance_Varnish_Model_Control_Abstract
{
    protected $_helperName = 'varnish/control_catalog_product';

    /**
     * Purge product
     *
     * @param Mage_Catalog_Model_Product $product
     * @param bool                       $purgeParentProducts
     * @param bool                       $purgeCategories
     *
     * @return Balance_Varnish_Model_Control_Catalog_Product
     */
    public function purge(Mage_Catalog_Model_Product $product, $purgeParentProducts = false, $purgeCategories = false)
    {
        if ($this->_canPurge()) {
            $this->_purgeById($product->getId());
            $this->_getSession()->addSuccess(
                Mage::helper('varnish')->__('Varnish cache for "%s" has been purged.', $product->getName())
            );
            if ($purgeParentProducts) {
                // purge parent products
                $productRelationCollection = $this->_getProductRelationCollection()
                    ->filterByChildId($product->getId());
                foreach ($productRelationCollection as $productRelation) {
                    $this->_purgeById($productRelation->getParentId());
                }
                // purge categories of parent products
                if ($purgeCategories) {
                    $categoryProductCollection = $this->_getCategoryProductRelationCollection()
                        ->filterAllByProductIds($productRelationCollection->getAllIds());
                    $catalogCacheControl = $this->_getCategoryCacheControl();
                    foreach ($categoryProductCollection as $categoryProduct) {
                        $catalogCacheControl->purgeById($categoryProduct->getCategoryId());
                    }
                }
            }
            if ($purgeCategories) {
                $catalogCacheControl = $this->_getCategoryCacheControl();
                foreach ($product->getCategoryCollection() as $category) {
                    $catalogCacheControl->purge($category);
                }
                $this->_getSession()->addSuccess(
                    Mage::helper('varnish')->__('Varnish cache for the product\'s categories has been purged.')
                );
            }
        }

        return $this;
    }

    /**
     * Purge product by id
     *
     * @param int  $id
     * @param bool $purgeParentProducts
     * @param bool $purgeCategories
     *
     * @return Balance_Varnish_Model_Control_Catalog_Product
     */
    public function purgeById($id, $purgeParentProducts = false, $purgeCategories = false)
    {
        $product = Mage::getModel('catalog/product')->load($id);

        return $this->purge($product, $purgeParentProducts, $purgeCategories);
    }

    /**
     * Purge product by id
     *
     * @param int $id
     *
     * @return Balance_Varnish_Model_Control_Catalog_Product
     */
    protected function _purgeById($id)
    {
        $collection = $this->_getUrlRewriteCollection()
            ->filterAllByProductId($id);
        foreach ($collection as $urlRewriteRule) {
            $urlRegexp = '/' . $urlRewriteRule->getRequestPath();
            $this->_getCacheControl()
                ->clean($this->_getStoreDomainList(), $urlRegexp);
        }

        return $this;
    }

    /**
     * Get Category Cache Control model
     *
     * @return Balance_Varnish_Model_Control_Catalog_Category
     */
    protected function _getCategoryCacheControl()
    {
        return Mage::getModel('varnish/control_catalog_category');
    }
}
