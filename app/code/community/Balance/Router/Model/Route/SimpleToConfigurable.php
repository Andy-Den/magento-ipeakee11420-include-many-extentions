<?php

/**
 * Class Balance_Router_Model_Route_SimpleToConfigurable
 *
 * @author Derek Li
 */
class Balance_Router_Model_Route_SimpleToConfigurable extends Balance_Router_Model_Route_Abstract
{
    /**
     * Trying to match the request.
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @return Balance_Router_Model_Route_Interface
     */
    public function match(Mage_Core_Controller_Request_Http $request)
    {
        $negativeResult = Mage::getModel('balance_router/matchResult_negative');
        if (!Mage::getStoreConfig('balance_router/product_redirect/simple_to_configurable_enabled')) {
            return $negativeResult;
        }
        /**
         * @var $requestHelper Balance_Router_Helper_Requet
         */
        $requestHelper = Mage::helper('balance_router/request');
        if ($requestHelper->matchRequest($request, 'catalog', 'product', 'view')) {
            $productId = $request->getParam('id');
            // Do nothing if the product id it not found.
            if (empty($productId)) {
                return $negativeResult;
            }
            return $this->simpleToConfigurable($negativeResult, $productId);
        } else {
            $path = explode('/', $request->getOriginalPathInfo());
            $productPath = '';
            for ($i=count($path); $i>0; $i--) {
                if (isset($path[$i-1])) {
                    if ($path[$i-1] != '') {
                        $productPath = $path[$i-1];
                        break;
                    }
                }
            }
            if ($productPath != '') {
                $resource = Mage::getSingleton('core/resource');
                $readConnection = $resource->getConnection('core_read');
                $storeId = Mage::app()->getStore()->getId();
                $table = $readConnection->getTableName('catalog_product_entity_url_key');
                $query = 'SELECT entity_id FROM '.$table
                    .' WHERE (store_id="'.$storeId.'" OR store_id=0) AND value="'
                    .$productPath.'" ORDER BY store_id DESC LIMIT 1';
                $productId = $readConnection->fetchOne($query);
                if ($productId) {
                    $matchRedirect = $this->simpleToConfigurable($negativeResult, $productId);
                    if ($matchRedirect instanceof Balance_Router_Model_MatchResult_Redirect) {
                        return Mage::app()->getResponse()
                            ->setRedirect($matchRedirect->getToUrl(), 301)
                            ->sendResponse();
                    }
                }
            }
        }
        return $negativeResult;
    }

    /** redirect simple to configurable product
     * @param object $negativeResult balance router
     * @param int    $productId      product id
     * @return Balance_Router_Model_MatchResult_Redirect
     */
    public function simpleToConfigurable($negativeResult, $productId)
    {
        $product = Mage::getModel('catalog/product')->load($productId);
        // Do nothing if the product is not a simple product.
        if ($product->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            return $negativeResult;
        }
        // Do nothing if the product is disabled so that Magento to render it to 404 by default.
        if ($product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
            return $negativeResult;
        }
        $configurableProductIds = Mage::getModel('catalog/product_type_configurable')
            ->getParentIdsByChild($productId);
        if (count($configurableProductIds) === 0) {
            return $negativeResult;
        }
        $firstConfigurableProductId = array_shift($configurableProductIds);
        $configurableProduct = Mage::getModel('catalog/product')->load($firstConfigurableProductId);
        if ($configurableProduct->getId()) {
            $toUrl = $configurableProduct->getProductUrl();
            if ($toUrl) {
                /**
                 * @var $matchRedirect Balance_Router_Model_MatchResult_Redirect
                 */
                $matchRedirect = Mage::getModel('balance_router/matchResult_redirect');
                $matchRedirect
                    ->setHeaderCode(301)
                    ->setToUrl($toUrl);
                return $matchRedirect;
            }
            return $negativeResult;
        }
        return $negativeResult;
    }
}