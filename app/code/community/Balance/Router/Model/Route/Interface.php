<?php

/**
 * Interface Balance_Router_Model_Route_Interface
 *
 * @author Derek Li
 */
interface Balance_Router_Model_Route_Interface
{
    /**
     * Match the http request.
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @return Balance_Router_Model_Route_Interface
     */
    public function match(Mage_Core_Controller_Request_Http $request);
}