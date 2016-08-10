<?php

class Balance_Varnish_Helper_Cache extends Mage_Core_Helper_Abstract
{
    const XML_PATH_VARNISH_CACHE_DISABLE_CACHING = 'system/varnish/disable_caching';
    const XML_PATH_VARNISH_CACHE_DISABLE_CACHING_VARS = 'system/varnish/disable_caching_vars';
    const XML_PATH_VARNISH_CACHE_DISABLE_ROUTES = 'system/varnish/disable_routes';
    const XML_PATH_VARNISH_CACHE_TTL = 'system/varnish/ttl';
    const XML_PATH_VARNISH_CACHE_ROUTES_TTL = 'system/varnish/routes_ttl';

    const REGISTRY_VAR_VARNISH_CACHE_CONTROL_HEADERS_SET_FLAG = '_varnish_cache_control_headers_set_flag';

    /**
     * Cookie name for disabling external caching
     *
     * @var string
     */
    const NO_CACHE_COOKIE = 'NO_CACHE';

    /**
     * Header for debug flag
     *
     * @var string
     * @return void
     */
    const DEBUG_HEADER = 'X-Cache-Debug: 1';


    /**
     * Get Cookie object
     *
     * @return Mage_Core_Model_Cookie
     */
    public static function getCookie()
    {
        return Mage::getSingleton('core/cookie');
    }


    /**
     * Set appropriate cache control headers
     *
     * @return Balanc_Varnish_Helper_Cache
     */
    public function setCacheControlHeaders()
    {
        if (Mage::registry(self::REGISTRY_VAR_VARNISH_CACHE_CONTROL_HEADERS_SET_FLAG)) {
            return $this;
        } else {
            Mage::register(self::REGISTRY_VAR_VARNISH_CACHE_CONTROL_HEADERS_SET_FLAG, 1);
        }

        $request = Mage::app()->getRequest();

        // set debug header
        if (Mage::helper('varnish')->isDebug()) {
            $this->setDebugHeader();
        }

        // renew no-cache cookie
        $this->setNoCacheCookie(true);

        // disable page caching for POSTs and no_cache parameters
        if ($request->isPost() || $request->getParam('no_cache')
            || !in_array(Mage::app()->getResponse()->getHttpResponseCode(), array(200, 301, 404))
        ) {
            return $this->setNoCacheHeader();
        }

        $value = Mage::getStoreConfig(self::XML_PATH_VARNISH_CACHE_TTL);
        $this->setTtlHeader(intval($value));

        return $this;
    }

    /**
     * Check for a NO_CACHE cookie and if found force a TTL=0 for this
     * page.
     *
     * @return void
     */
    public static function sanitizeCacheControlHeader()
    {
        $cookie = self::getCookie();
        if ($cookie->get(self::NO_CACHE_COOKIE)) {
            self::setNoCacheHeader();
        }
    }

    /**
     * Disable caching of this and all future request for this visitor
     *
     * @return Balanc_Varnish_Helper_Cache
     */
    public function setNoCacheCookie($renewOnly = false)
    {
        if ($this->getCookie()->get(self::NO_CACHE_COOKIE)) {
            $this->getCookie()->renew(self::NO_CACHE_COOKIE);
        } elseif (!$renewOnly) {
            $this->getCookie()->set(self::NO_CACHE_COOKIE, 1);
        }

        return $this;
    }

    /**
     * Disable caching for this request
     *
     * @return Balanc_Varnish_Helper_Cache
     */
    public static function setNoCacheHeader()
    {
        return self::setTtlHeader(0);
    }

    /**
     * Set debug flag in HTTP header
     *
     * @return Balanc_Varnish_Helper_Cache
     */
    public function setDebugHeader()
    {
        $el = explode(':', self::DEBUG_HEADER, 2);
        Mage::app()->getResponse()->setHeader($el[0], $el[1], true);

        return $this;
    }

    /**
     * Set TTL HTTP header for cache
     *
     * For mod_expires it is important to have "Expires" header. However for
     * Varnish it is easier to deal with "Cache-Control: s-maxage=xx" as it
     * is relative to its system time and not depending on timezone settings.
     *
     * Magento normaly doesn't set any Cache-Control or Expires headers. If they
     * appear the are set by PHP's setcookie() function.
     *
     * @param int   Time to life in seconds. Value greater than 0 means "cacheable".
     *
     * @return void
     */
    public static function setTtlHeader($ttl)
    {
        $maxAge = 's-maxage=' . (($ttl < 0) ? 0 : $ttl);
        $cacheControlValue = 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0, ' . $maxAge;

        // retrieve existing "Cache-Control" header
        $response = Mage::app()->getResponse();
        $headers = $response->getHeaders();

        foreach ($headers as $key => $header) {
            if ('Cache-Control' == $header['name'] && !empty($header['value'])) {
                // replace existing "max-age" value
                if (strpos($header['value'], 'age=') !== false) {
                    $cacheControlValue = preg_replace('/(s-)?max[-]?age=[0-9]+/', $maxAge, $header['value']);
                } else {
                    $cacheControlValue .= $header['value'] . ', ' . $maxAge;
                }
            }
        }

        // set "Cache-Control" header with "s-maxage" value
        $response->setHeader('Cache-Control', $cacheControlValue, true);

        // set "Expires" header in the past to keep mod_expires from applying it's ruleset
        $response->setHeader('Expires', 'Mon, 31 Mar 2008 10:00:00 GMT', true);

        // set "Pragma: no-cache" - just in case
        $response->setHeader('Pragma', 'no-cache', true);
    }

    /**
     * Find all domains for store
     *
     * @return string
     */
    public function getStoreDomainList($storeId = 0, $seperator = '|')
    {
        $storeIds = array($storeId);

        // if $store is empty or 0 get all store ids
        if (empty($storeId)) {
            $storeIds = Mage::getResourceModel('core/store_collection')->getAllIds();
        }

        $domains = array();
        $urlTypes = array(
            Mage_Core_Model_Store::URL_TYPE_LINK,
            Mage_Core_Model_Store::URL_TYPE_DIRECT_LINK,
            Mage_Core_Model_Store::URL_TYPE_WEB,
            Mage_Core_Model_Store::URL_TYPE_SKIN,
            Mage_Core_Model_Store::URL_TYPE_JS,
            Mage_Core_Model_Store::URL_TYPE_MEDIA
        );
        foreach ($storeIds as $storeId) {
            $store = Mage::getModel('core/store')->load($storeId);

            foreach ($urlTypes as $urlType) {
                // get non-secure store domain
                $domains[] = Zend_Uri::factory($store->getBaseUrl($urlType, false))->getHost();
                // get secure store domain
                $domains[] = Zend_Uri::factory($store->getBaseUrl($urlType, true))->getHost();
            }
        }

        // get only unique values
        $domains = array_unique($domains);

        return implode($seperator, $domains);
    }

    /**
     * Set appropriate cache control raw headers.
     * Called when script exits before controller_action_postdispatch
     * avoiding Zend_Controller_Response_Http#sendResponse()
     *
     * @return Balanc_Varnish_Helper_Cache
     */
    public function setCacheControlHeadersRaw()
    {
        if (Mage::registry(self::REGISTRY_VAR_VARNISH_CACHE_CONTROL_HEADERS_SET_FLAG)
            || Mage::app()->getStore()->isAdmin()
            || strtolower(Mage::app()->getRequest()->getRouteName()) == 'm2epro'
        ) {
            return $this;
        }

        try {
            $response = Mage::app()->getResponse();
            $response->canSendHeaders(true);
            $this->setCacheControlHeaders();
            foreach ($response->getHeaders() as $header) {
                header($header['name'] . ': ' . $header['value'], $header['replace']);
            }
        } catch (Exception $e) {
//            Mage::helper('varnishcache')->debug(
//                'Error while trying to set raw cache control headers: '.$e->getMessage()
//            );
        }

        return $this;
    }
}

