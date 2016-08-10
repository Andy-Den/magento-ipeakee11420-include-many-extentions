<?php

/**
 * Interface Balance_Router_Model_RouteTable_Interface
 *
 * @author Derek Li
 */
interface Balance_Router_Model_RouteTable_Interface
{
    /**
     * Add the route.
     *
     * @param Balance_Router_Model_Route_Interface $route
     * @return $this
     */
    public function add(Balance_Router_Model_Route_Interface $route);

    /**
     * Route the request.
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @return mixed
     */
    public function route(Mage_Core_Controller_Request_Http $request);
}