<?php

class Balance_Varnish_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_VARNISH_CACHE_ENABLED = 'system/varnish/enabled';
    const XML_PATH_VARNISH_CACHE_DEBUG = 'system/varnish/debug';

    /**
     * Check whether Varnish cache is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_VARNISH_CACHE_ENABLED);
    }

    /**
     * Check whether debuging is enabled
     *
     * @return bool
     */
    public function isDebug()
    {
        if (Mage::getStoreConfigFlag(self::XML_PATH_VARNISH_CACHE_DEBUG)) {
            return true;
        }

        return false;
    }

    /**
     * Log debugging data
     *
     * @param string|array
     *
     * @return void
     */
    public function debug($debugData)
    {
        if ($this->isDebug()) {
            Mage::log($debugData, null, 'varnish_cache.log');
        }
    }

    /**
     * Get Varnish control model
     *
     * @return Phoenix_VarnishCache_Model_Control
     */
    public function getCacheControl()
    {
        return Mage::getSingleton('varnish/control');
    }
}
