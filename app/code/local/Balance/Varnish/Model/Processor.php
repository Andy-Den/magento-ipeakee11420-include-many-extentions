<?php

class Balance_Varnish_Model_Processor
{
    /**
     * This is only a dummy function at the moment to  sanitize Cache-
     * Control headers on FPC hits. It doesn't do what might be expected
     * (retrieve cached content without ramping up the whole application
     * stack), but it is the only way to hook in our logic.
     *
     * This method is called at the very beginning of Magento from
     * Mage_Corel_Model_App::run() ->
     * Mage_Core_Model_Cache::processRequest().
     *
     * @param string $content
     *
     * @return string | false
     */
    public function extractContent($content)
    {
        /**
         * if content has been fetched from cache (FPC had a cache hit) the
         * HTTP headers have been already set by the FPC. However if a
         * NO_CACHE cookie is present we need to make sure the TTL is 0 as
         * it might cache with a TTL > 0 which is a logical constraint.
         */
        if (!empty($content)) {
            Balance_Varnish_Helper_Cache::sanitizeCacheControlHeader();
        }

        return $content;
    }
}