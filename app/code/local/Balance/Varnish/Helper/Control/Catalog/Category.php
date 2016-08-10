<?php

class Balance_Varnish_Helper_Control_Catalog_Category extends Balance_Varnish_Helper_Data
{
    const XML_PATH_VARNISH_CACHE_PURGE = 'system/varnish/purge_catalog_category';

    /**
     * Returns true if Varnish cache is enabled and category should be purged on save
     *
     * @return boolean
     */
    public function canPurge()
    {
        return $this->isEnabled() && $this->isPurge();
    }

    /**
     * Returns true if category should be purged on save
     *
     * @return boolean
     */
    public function isPurge()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_VARNISH_CACHE_PURGE);
    }
}
