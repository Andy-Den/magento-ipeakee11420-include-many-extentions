<?php

class Balance_Varnish_Model_Observer
{
    const SET_CACHE_HEADER_FLAG = 'VARNISH_CACHE_CONTROL_HEADERS_SET';

    /**
     * Retrieve session model
     *
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    /**
     * Check if full page cache is enabled
     *
     * @return bool
     */
    protected function _isCacheEnabled()
    {
        return Mage::helper('varnish')->isEnabled();
    }

    /**
     * Get Varnish control model
     *
     * @return Balance_Varnish_Model_Control
     */
    protected function _getCacheControl()
    {
        return Mage::getSingleton('varnish/control');
    }

    /**
     * Clean all Varnish cache items
     *
     * @param Varien_Event_Observer $observer
     */
    public function cleanCache(Varien_Event_Observer $observer)
    {
        if ($this->_isCacheEnabled()) {
            $this->_getCacheControl()->clean(Mage::helper('varnish/cache')->getStoreDomainList());

            $this->_getSession()->addSuccess(
                Mage::helper('varnish')->__('The Varnish cache has been cleaned.')
            );
        }
    }

    /**
     * Clean media (CSS/JS) cache
     *
     * @param Varien_Event_Observer $observer
     */
    public function cleanMediaCache(Varien_Event_Observer $observer)
    {
        if ($this->_isCacheEnabled()) {
            $this->_getCacheControl()->clean(
                Mage::helper('varnish/cache')->getStoreDomainList(),
                '^/media/(js|css|css_secure)/'
            );

            // also clean HTML files
            $this->_getCacheControl()->clean(
                Mage::helper('varnish/cache')->getStoreDomainList(),
                '.*',
                Balance_Varnish_Model_Control::CONTENT_TYPE_HTML
            );

            $this->_getSession()->addSuccess(
                Mage::helper('varnish')->__('The JavaScript/CSS cache has been cleaned on the Varnish servers.')
            );
        }
    }

    /**
     * Clean catalog images cache
     *
     * @param Varien_Event_Observer $observer
     */
    public function cleanCatalogImagesCache(Varien_Event_Observer $observer)
    {
        if ($this->_isCacheEnabled()) {
            $this->_getCacheControl()->clean(
                Mage::helper('varnish/cache')->getStoreDomainList(),
                '^/media/catalog/product/cache/',
                Balance_Varnish_Model_Control::CONTENT_TYPE_IMAGE
            );

            // also clean HTML files
            $this->_getCacheControl()->clean(
                Mage::helper('varnish/cache')->getStoreDomainList(),
                '.*',
                Balance_Varnish_Model_Control::CONTENT_TYPE_HTML
            );

            $this->_getSession()->addSuccess(
                Mage::helper('varnish')->__('The catalog image cache has been cleaned on the Varnish servers.')
            );
        }

        return $this;
    }

    /**
     * Set appropriate cache control headers
     *
     * @param Varien_Event_Observer $observer
     */
    public function setCacheControlHeaders(Varien_Event_Observer $observer)
    {
        if ($this->_isCacheEnabled()) {
            if (!Mage::registry(self::SET_CACHE_HEADER_FLAG)) {
                Mage::helper('varnish/cache')->setCacheControlHeaders();
                Mage::register(self::SET_CACHE_HEADER_FLAG, true);
            }
        }
    }

    /**
     * If the page has been cached by the FPC and a NO_CACHE cookie has
     * been set, the cached Cache-Control header might allow caching of the
     * page while the NO_CACHE cookie which should prevent it.
     * To sanitize this conflict we will force a TTL=0 before sending out
     * the page.
     */
    public function sanitizeCacheControlHeader()
    {
        Mage::helper('varnish/cache')->sanitizeCacheControlHeader();
    }

    /**
     * Disable page caching by setting no-cache header
     *
     * @param Varien_Event_Observer $observer | null
     */
    public function disablePageCaching($observer = null)
    {
        if ($this->_isCacheEnabled() || Mage::app()->getStore()->isAdmin()) {
            Mage::helper('varnish/cache')->setNoCacheHeader();
        }
    }

    /**
     * Purge category
     *
     * @param Varien_Event_Observer $observer
     */
    public function purgeCatalogCategory(Varien_Event_Observer $observer)
    {
        try {
            $category = $observer->getEvent()->getCategory();
            if (!Mage::registry('varnish_catalog_category_purged_' . $category->getId())) {
                Mage::getModel('varnish/control_catalog_category')->purge($category);
                Mage::register('varnish_catalog_category_purged_' . $category->getId(), true);
            }
        } catch (Exception $e) {
            Mage::helper('varnish')->debug('Error on save category purging: ' . $e->getMessage());
        }
    }

    /**
     * Purge product
     *
     * @param Varien_Event_Observer $observer
     */
    public function purgeCatalogProduct(Varien_Event_Observer $observer)
    {
        try {
            $product = $observer->getEvent()->getProduct();
            if (!Mage::registry('varnish_catalog_product_purged_' . $product->getId())) {
                Mage::getModel('varnish/control_catalog_product')->purge($product, true, true);
                Mage::register('vanrish_catalog_product_purged_' . $product->getId(), true);
            }
        } catch (Exception $e) {
            Mage::helper('varnish')->debug('Error on save product purging: ' . $e->getMessage());
        }
    }

    /**
     * Purge Cms Page
     *
     * @param Varien_Event_Observer $observer
     */
    public function purgeCmsPage(Varien_Event_Observer $observer)
    {
        try {
            $page = $observer->getEvent()->getObject();
            if (!Mage::registry('varnish_cms_page_purged_' . $page->getId())) {
                Mage::getModel('varnish/control_cms_page')->purge($page);
                Mage::register('varnish_cms_page_purged_' . $page->getId(), true);
            }
        } catch (Exception $e) {
            Mage::helper('varnish')->debug('Error on save cms page purging: ' . $e->getMessage());
        }
    }

    /**
     * Purge product
     *
     * @param Varien_Event_Observer $observer
     */
    public function purgeCatalogProductByStock(Varien_Event_Observer $observer)
    {
        try {
            $item = $observer->getEvent()->getItem();
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            if (!Mage::registry('varnish_catalog_product_purged_' . $product->getId())) {
                Mage::getModel('varnish/control_catalog_product')->purge($product, true, true);
                Mage::register('varnish_catalog_product_purged_' . $product->getId(), true);
            }
        } catch (Exception $e) {
            Mage::helper('varnish')->debug('Error on save product purging: ' . $e->getMessage());
        }
    }

    /**
     * Sets shutdown listner to ensure cache control headers sent in case script exits unexpectedly
     */
    public function registerShutdownFunction()
    {
        if ($this->_isCacheEnabled()) {
            /**
             *  workaround for PHP bug with autoload and open_basedir restriction:
             *  ensure the Zend exception class is loaded.
             */
            $exception = new Zend_Controller_Response_Exception;
            unset($exception);

            // register shutdown method
            register_shutdown_function(array(Mage::helper('varnish/cache'), 'setCacheControlHeadersRaw'));
        }
    }


    /**
     * Purge product
     *
     * @param Varien_Event_Observer $observer
     */
    public function purgeCatalogProductByReview(Varien_Event_Observer $observer)
    {
        try {
            $productId = $observer->getEvent()->getData('entity_pk_value');
            $product = Mage::getModel('catalog/product')->load($productId);
            if (!Mage::registry('varnish_catalog_product_purged_' . $product->getId())) {
                Mage::getModel('varnish/control_catalog_product')->purge($product, true, true);
                Mage::register('varnish_catalog_product_purged_' . $product->getId(), true);
            }
        } catch (Exception $e) {
            Mage::helper('varnish')->debug('Error on save product purging: ' . $e->getMessage());
        }
    }

}
