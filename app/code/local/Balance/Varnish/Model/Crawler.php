<?php
/**
 *  extension for Magento
 *
 * Long description of this file (if any...)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Balance Varnish module to newer versions in the future.
 * If you wish to customize the Balance Varnish module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Balance
 * @package    Balance_Varnish
 * @copyright  Copyright (C) 2013
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Short description of the class
 *
 * Long description of the class (if any...)
 *
 * @category   Balance
 * @package    Balance_Varnish
 * @subpackage Model
 * @author     Richard Cai <richard@balanceinternet.com.au>
 */
class Balance_Varnish_Model_Crawler extends Mage_Core_Model_Abstract
{
    const XML_PATH_CRAWLER_ENABLED = 'system/varnish_crawler/enabled';
    const XML_PATH_CRAWLER_DESIGN_EXCEPTIONS = 'system/varnish_crawler/design_exceptions';
    const XML_PATH_CRAWLER_MULTICURRENCY = 'system/varnish_crawler/multicurrency';
    const XML_PATH_CRAWLER_THREADS_NUM = 'system/varnish_crawler/threads_num';

    const XML_PATH_CRAWLER_INCLUDE_HOMEPAGE = 'system/varnish_crawler/include_homepage';
    const XML_PATH_CRAWLER_INCLUDE_CATEGORY = 'system/varnish_crawler/include_category';
    const XML_PATH_CRAWLER_INCLUDE_PRODUCT = 'system/varnish_crawler/include_product';
    const XML_PATH_CRAWLER_INCLUDE_CMS = 'system/varnish_crawler/include_cms';

    const XML_PATH_CRAWLER_ADAPTER = 'system/varnish_crawler/adapter';

    const XML_PATH_CRAWLER_SET_CURRENCY_COOKIE = 'system/varnish_crawler/set_currency_cookie';

    const XML_PATH_CRAWLER_INTERVAL = 'system/varnish_crawler/interval';

    const LOG_NAME = 'varnish_crawler.log';

    protected $_adapter;

    /* (non-PHPdoc)
     * @see Varien_Object::_construct()
    */
    protected function _construct()
    {
        $this->_init('varnish/crawler');

        $this->setData('crawl_homepage', true);
        $this->setData('crawl_categories', true);
        $this->setData('crawl_products', true);
        $this->setData('crawl_cms', true);
    }

    /**
     * Crawl stores
     *
     * @return Balance_Varnish_Model_Crawler
     */
    public function run()
    {
        foreach ($this->getStores() as $storeId => $store) {
            if (!Mage::getStoreConfig(self::XML_PATH_CRAWLER_ENABLED, $storeId)) {
                continue;
            }

            if (Mage::getStoreConfigFlag(self::XML_PATH_CRAWLER_SET_CURRENCY_COOKIE, $storeId)) {
                foreach ($this->getCurrencies($store) as $currencyCode) {
                    foreach ($this->getUserAgents($store) as $userAgent) {
                        $this->_run($store, $currencyCode, $userAgent);
                    }
                }
            } else {
                foreach ($this->getUserAgents($store) as $userAgent) {
                    // currency code not in use.
                    $currencyCode = '';
                    $this->_run($store, $currencyCode, $userAgent);
                }
            }
        }

        return $this;
    }

    /**
     * Crawl store
     *
     * @param Mage_Core_Model_Store $store
     * @param string                $currencyCode
     * @param string                $userAgent
     *
     * @return Balance_Varnish_Model_Crawler
     */
    protected function _run(Mage_Core_Model_Store $store, $currencyCode, $userAgent)
    {
        $storeId = $store->getId();
        $baseUrl = $store->getBaseUrl();

        $defaultStoreId = $store->getWebsite()->getDefaultStore()->getId();
        $defaultBaseUrl = $store->getWebsite()->getDefaultStore()->getBaseUrl();

        $options = array();
        if (($baseUrl == $defaultBaseUrl) && ($storeId != $defaultStoreId)) {
            $options[CURLOPT_COOKIE] = sprintf('store=%s;', $store->getCode());
        }

        if (Mage::getStoreConfigFlag(self::XML_PATH_CRAWLER_SET_CURRENCY_COOKIE, $storeId)) {
            if ($currencyCode != $store->getDefaultCurrencyCode()) {
                $currency = Mage::getModel('directory/currency')->load($currencyCode);
                $options[CURLOPT_COOKIE] = sprintf('currency=%s;', $currency->getCode());
            }
        }

        $customOptions = new Varien_Object();
        Mage::dispatchEvent('varnish_crawler_set_options', array('options' => $customOptions, 'store' => $store));

        foreach ($customOptions->getData() as $key => $value) {
            $options[$key] = $value;
        }

        $options[CURLOPT_USERAGENT] = $userAgent;
        $options[CURLOPT_HTTPHEADER]= array('IsCrawler:Yes');

        $threadsNum = intval(Mage::getStoreConfig(self::XML_PATH_CRAWLER_THREADS_NUM, $storeId));
        $threadsNum = empty($threadsNum) ? 1 : $threadsNum;

        $interval = round(Mage::getStoreConfig(self::XML_PATH_CRAWLER_INTERVAL, $storeId), 2) * 1000000;

        $urlTypes = array();
        if ($this->getData('crawl_homepage')
            && Mage::getStoreConfigFlag(
                self::XML_PATH_CRAWLER_INCLUDE_HOMEPAGE,
                $storeId
            )
        ) {
            $urlTypes[] = 'homepage';
        }
        if ($this->getData('crawl_categories')
            && Mage::getStoreConfigFlag(
                self::XML_PATH_CRAWLER_INCLUDE_CATEGORY,
                $storeId
            )
        ) {
            $urlTypes[] = 'category';
        }
        if ($this->getData('crawl_products')
            && Mage::getStoreConfigFlag(
                self::XML_PATH_CRAWLER_INCLUDE_PRODUCT,
                $storeId
            )
        ) {
            $urlTypes[] = 'product';
        }
        if ($this->getData('crawl_cms') && Mage::getStoreConfigFlag(self::XML_PATH_CRAWLER_INCLUDE_CMS, $storeId)) {
            $urlTypes[] = 'cms';
        }

        $ips = array();
        $servers = Mage::getStoreConfig('system/varnish/servers');
        $servers = str_replace(',', ';', $servers);
        $ips = explode(';', $servers);

        $port = Mage::getStoreConfig('system/varnish/port');
        $options[CURLOPT_PORT] = empty($port) ? 80 : $port;

        if (empty($ips)) {
            throw new Exception('No server set in varnish configuration.');

            return this;
        }

        foreach ($urlTypes as $type) {

            $storeUrls = $this->_getUrls($store, $type);


            foreach (array_chunk($storeUrls, $threadsNum) as $urls) {

                if (!$this->getData('dry_run')) {
                    $this->process($urls, $options, $ips);
                }

                Mage::helper('varnish')->debug(
                    array(
                        'urls' => $urls,
                        'options' => $options
                    )
                );

                if (!empty($interval)) {
                    usleep($interval);
                }
            }
        }

        return $this;
    }

    /**
     * Returns available stores
     *
     * @return array
     */
    public function getStores()
    {
        return Mage::app()->getStores();
    }

    /**
     * Returns available currencies
     *
     * @param Mage_Core_Model_Store $store
     *
     * @return string
     */
    public function getCurrencies(Mage_Core_Model_Store $store)
    {
        if (Mage::getStoreConfig(self::XML_PATH_CRAWLER_MULTICURRENCY, $store->getId())) {
            $currencies = $store->getAvailableCurrencyCodes(true);
        } else {
            $currencies = array($store->getDefaultCurrencyCode());
        }

        return $currencies;
    }

    /**
     * Returns User Agent according to design exceptions
     *
     * @param Mage_Core_Model_Store $store
     *
     * @return array
     */
    public function getUserAgents(Mage_Core_Model_Store $store)
    {
        return array('VarnishCrawler');
    }

    /**
     * Returns urls to crawl
     *
     * @param Mage_Core_Model_Store $store
     *
     * @return string
     */
    protected function _getUrls(Mage_Core_Model_Store $store, $type)
    {
        $urls = array();
        switch ($type) {
            case 'homepage':
                $urls = array($store->getBaseUrl());
                break;
            case 'category':
                $urls = $this->getCategoryUrls($store);
                break;
            case 'product':
                $urls = $this->getProductUrls($store);
                break;
            case 'cms':
                $urls = $this->getCmsUrls($store);
                break;
            case 'all':
                $urls = $this->getAllUrls($store);
                break;
        }

        return $urls;
    }

    /**
     * Gather all urls for crawler from core_url_rewrite or enterprise_url_rewrite table
     *
     * @param $store
     *
     * @return array
     */
    protected function getAllUrls(Mage_Core_Model_Store $store)
    {
        $urls = array();

        $stmt = $this->_getResource()->getUrlStmt($store->getId());

        while ($row = $stmt->fetch()) {
            $urls[] = $store->getBaseUrl() . $row['request_path'];
        }

        return $urls;
    }

    /**
     * Gather crawler urls for category pages
     *
     * @param $store
     *
     * @return array
     */
    protected function getCategoryUrls(Mage_Core_Model_Store $store)
    {
        $urls = array();

        $stmt = $this->_getResource()
            ->getCategoryStatement($store->getId());

        $categoryCount = 0;
        while ($row = $stmt->fetch()) {
            $urls[] = $store->getBaseUrl() . $row['request_path'];
            $categoryCount++;
        }

        Mage::log('added ' . $categoryCount . ' category urls', Zend_Log::DEBUG, self::LOG_NAME);

        return $urls;
    }

    /**
     * Gather crawler urls for product pages
     *
     * @param $store
     *
     * @return array
     */
    protected function getProductUrls(Mage_Core_Model_Store $store)
    {
        $urls = array();

        $stmt = $this->_getResource()
            ->getProductStatement($store->getId());

        $productCount = 0;
        while ($row = $stmt->fetch()) {
            $urls[] = $store->getBaseUrl() . $row['request_path'];
            $productCount++;
        }

        Mage::log('added ' . $productCount . ' product urls', Zend_Log::DEBUG, self::LOG_NAME);

        return $urls;
    }

    /**
     * Gather crawler urls for cms pages
     *
     * @param $store
     *
     * @return array
     */
    protected function getCmsUrls(Mage_Core_Model_Store $store)
    {
        $urls = array();

        $pages = Mage::getModel("cms/page")->getCollection()
            ->addStoreFilter($store, false)
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('identifier', array('neq' => 'no-route'));

        $baseUrl = $store->getBaseUrl();
        $cmsCount = 0;

        foreach ($pages as $page) {
            $urls[] = $baseUrl . $page->getIdentifier();
            $cmsCount++;
        }

        Mage::log('added ' . $cmsCount . ' page urls', Zend_Log::DEBUG, self::LOG_NAME);

        return $urls;
    }

    /**
     * Returns curl adapter
     *
     * @return Varien_Http_Adapter_Curl
     */
    protected function _getAdapter()
    {
        if (!$this->_adapter) {
            $adapterClass = Mage::getStoreConfig(self::XML_PATH_CRAWLER_ADAPTER);
            $adapterClass = empty($adapterClass) ? 'varnish/adapter_default' : $adapterClass;
            $this->_adapter = Mage::getModel($adapterClass);
        }

        return $this->_adapter;
    }

    /**
     * Parses urls from text/html
     *
     * @param string $html
     *
     * @return array
     */
    public function parseUrls($html)
    {
        $urls = array();
        preg_match_all("/\s+href\s*=\s*[\"\']?([^\s\"\']+)[\"\'\s]+/ims", $html, $urls);

        return $urls[1];
    }

    public function process($urls, $options, $ips)
    {
        $port = isset($opitons[CURLOPT_PORT]) ? $options[CURLOPT_PORT] : 80;
        foreach ($urls as $url) {
            foreach ($ips as $ip) {
                $this->_getAdapter()->purge($url, $ip, $port);
            }
        }
        $this->_getAdapter()->multiRequest($urls, $options);
    }
}

