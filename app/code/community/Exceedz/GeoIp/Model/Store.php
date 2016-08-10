<?php
/**
 * Rewrites Mage_Core_Model_Store
 *
 * @category   Exceedz
 * @package    Exceedz_GeoIp
 * @version    0.1.0
 */

class Exceedz_GeoIp_Model_Store extends Mage_Core_Model_Store
{
	private $_cookieName = 'geoip_store';
	private $_iphoneCookieName = 'geoip_iphone_store';

    protected $_isCurrentlySecure = null;

    /**
     * Retrieve base URL
     *
     * @param string $type
     * @param boolean|null $secure
     * @return string
     */
    public function getBaseUrl($type = self::URL_TYPE_LINK, $secure = null)
    {
        if (is_null($this->_isCurrentlySecure)) {
            $this->_isCurrentlySecure = Mage::app()->getStore()->isCurrentlySecure();
        }
        if (is_null($secure) && $this->_isCurrentlySecure) {
            $secure = true;
        }

        $cacheKey = $type . '/' . (is_null($secure) ? 'null' : ($secure ? 'true' : 'false'));
        if (!isset($this->_baseUrlCache[$cacheKey])) {
            switch ($type) {
                case self::URL_TYPE_WEB:
                    $secure = is_null($secure) ? $this->isCurrentlySecure() : (bool)$secure;
                    $url = $this->getConfig('web/' . ($secure ? 'secure' : 'unsecure') . '/base_url');
                    break;

                case self::URL_TYPE_LINK:
                    $secure = (bool) $secure;
                    $url = $this->getConfig('web/' . ($secure ? 'secure' : 'unsecure') . '/base_link_url');
                    $url = $this->_updatePathUseRewrites($url);
                    $url = $this->_updatePathUseStoreView($url);
                    break;

                case self::URL_TYPE_DIRECT_LINK:
                    $secure = (bool) $secure;
                    $url = $this->getConfig('web/' . ($secure ? 'secure' : 'unsecure') . '/base_link_url');
                    $url = $this->_updatePathUseRewrites($url);
                    break;

                case self::URL_TYPE_SKIN:
                case self::URL_TYPE_JS:
                    $secure = is_null($secure) ? $this->isCurrentlySecure() : (bool) $secure;
                    $url = $this->getConfig('web/' . ($secure ? 'secure' : 'unsecure') . '/base_' . $type . '_url');
                    break;

                case self::URL_TYPE_MEDIA:
                    $url = $this->_updateMediaPathUseRewrites($secure);
                    break;

                default:
                    throw Mage::exception('Mage_Core', Mage::helper('core')->__('Invalid base url type'));
            }

            if (false !== strpos($url, '{{base_url}}')) {
                $baseUrl = Mage::getConfig()->substDistroServerVars('{{base_url}}');
                $url = str_replace('{{base_url}}', $baseUrl, $url);
            }

            $this->_baseUrlCache[$cacheKey] = rtrim($url, '/') . '/';
        }

        return $this->_baseUrlCache[$cacheKey];
    }

	/**
     * Check and set the store by visitor country
     *
     * @return boolean true on success.
     */
    public function checkStore()
	{
		$storeCookieCountry = $this->isStoreCookieSet();
		if ($storeCookieCountry) {
			return true;
		}
		else {
			$countryName = Mage::helper('geoip')->getCountryName();
			if ($countryName == 'Australia' || $countryName == 'United Kingdom') {
				$storeCodeByCountryName = str_replace(' ','_', strtolower($countryName));
				if($storeCodeByCountryName == Mage::app()->getStore()->getCode()) {
					$this->setStoreCookie($countryName);
					return true;
				} else {
					return false;
				}
			}
			else {
				return false;
			}
		}
	}

	/**
     * set the store in a cookie
     *
     * @return boolean true on success.
     */
	public function setStoreCookie($country, $path = true)
	{
        //$this->clearAllCookies();
		$cookiePeriod = 31536000;
		$storeCode = str_replace(' ','_', strtolower($country));
		if($path) {
           	Mage::getModel('core/cookie')->set($this->_cookieName, $storeCode, $cookiePeriod, '/', $host);
           	Mage::getModel('core/cookie')->set($this->_iphoneCookieName, $storeCode, $cookiePeriod, '/');
        } else {
           	Mage::getModel('core/cookie')->set($this->_cookieName, $storeCode, $cookiePeriod,  '', $host);
           	Mage::getModel('core/cookie')->set($this->_iphoneCookieName, $storeCode, $cookiePeriod,  '');
        }
		Mage::getSingleton('core/session')->setStoreCookieValue($storeCode);
		return true;
	}

	/**
     * check for the store cookie
     *
     * @return boolean true on success.
     */
	public function isStoreCookieSet()
	{
		$cookieValue = Mage::getModel('core/cookie')->get($this->_cookieName);
		if(empty($cookieValue)) {
			$cookieValue = Mage::getSingleton('core/session')->getStoreCookieValue();
		}
		if ($cookieValue == 'australia' || $cookieValue == 'united_kingdom')
			return $cookieValue;
		else
			return false;
	}

	/**
     * set the store using visitor country
     *
     * @return boolean true on success.
     */
	public function setStoreByCountry($country)
	{
		$stores = Mage::app()->getStores();
		$country = str_replace(' ', '_', strtolower($country));
		foreach ($stores as $_store) {
			if ($country == $_store->getCode()) {
				//Mage::app()->init($_store->getCode());
				Mage::getModel('core/store')->load($_store->getId());
				break;
			}
		}
	}

    /**
     * retrieve the store url by it's code
     *
     * @return store url.
     */
	public function getStoreUrlByCode($country, $withCode = true)
	{
		$stores = Mage::app()->getStores();
		$code = str_replace(' ', '_', strtolower($country));
		foreach ($stores as $_store) {
			if ($code == $_store->getCode()) {
				return $_store->getBaseUrl() . (($withCode) ? '?store=' . $_store->getCode() : '');
			}
		}
	}

    /**
     * clear all cookie
     *
     * @return boolean true on success.
     */
	public function clearAllCookies()
	{
        try {
            $cookies = Mage::getModel('core/cookie')->get();
            foreach($cookies as $cookieName => $cookieValue) {
                Mage::getModel('core/cookie')->delete($cookieName);
            }
            return true;
        }
        catch (Exception $e) {
            return false;
        }
	}
}
