<?php
/**
 * Rewrited: avoid loading category if information about it exists in registry
 *
 * @category  Balance
 * @package   Balance_Catalog
 * @copyright 2015 Balance Internet
 */
class Balance_Catalog_Model_Product_Url extends Enterprise_Catalog_Model_Product_Url
{
    /**
     * Retrieve product URL based on requestPath param
     *
     * Rewrited: avoid loading category if information about it exists in registry
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $requestPath
     * @param array $routeParams
     *
     * @return string
     */
    protected function _getProductUrl($product, $requestPath, $routeParams)
    {
        Varien_Profiler::start(__METHOD__);

        $categoryId = $this->_getCategoryIdForUrl($product, $routeParams);

        if (!empty($requestPath)) {
            if ($categoryId) {

                $category = Mage::registry('current_category');
                if (!$category || $categoryId != $category->getId()) {
                    $category = $this->_factory->getModel('catalog/category', array('disable_flat' => true))
                                        ->load($categoryId);
                }

                if ($category->getId()) {
                    $requestPath = Mage::helper('balance_catalog/category')->getRequestPath($category).'/'.$requestPath;
                }
            }
            $product->setRequestPath($requestPath);

            $storeId = $this->getUrlInstance()->getStore()->getId();
            $requestPath = $this->_factory->getHelper('enterprise_catalog')
                ->getProductRequestPath($requestPath, $storeId);

            Varien_Profiler::stop(__METHOD__);
            return $this->getUrlInstance()->getDirectUrl($requestPath, $routeParams);
        }

        $routeParams['id'] = $product->getId();
        $routeParams['s'] = $product->getUrlKey();
        if ($categoryId) {
            $routeParams['category'] = $categoryId;
        }

        Varien_Profiler::stop(__METHOD__);

        return $this->getUrlInstance()->getUrl('catalog/product/view', $routeParams);
    }
}