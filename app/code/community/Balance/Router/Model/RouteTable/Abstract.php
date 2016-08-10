<?php

/**
 * Class Balance_Router_Model_RouteTable_Abstract
 *
 * @author Derek Li
 */
abstract class Balance_Router_Model_RouteTable_Abstract implements Balance_Router_Model_RouteTable_Interface
{
    /**
     * If the router is enabled.
     *
     * @return mixed
     */
    public function isEnabled()
    {
        return Mage::getStoreConfig('balance_router/general/router_enabled');
    }
}