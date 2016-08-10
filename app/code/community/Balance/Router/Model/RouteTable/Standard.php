<?php

/**
 * Class Balance_Router_Model_RouteTable_Standard
 *
 * @author Derek Li
 */
class Balance_Router_Model_RouteTable_Standard extends Balance_Router_Model_RouteTable_Abstract
{
    /**
     * Routes in the table.
     *
     * @var array
     */
    protected $_routes = array();

    /**
     * Add the route.
     *
     * @param Balance_Router_Model_Route_Interface $route
     * @return $this
     */
    public function add(Balance_Router_Model_Route_Interface $route)
    {
        $this->_routes[spl_object_hash($route)] = $route;
        return $this;
    }

    /**
     * Remove the route.
     *
     * @param mixed $route Id of the route or the route itself.
     * @return $this
     */
    public function remove($route)
    {
        $id = $route;
        if ($route instanceof Balance_Router_Model_Route_Interface) {
            $id = spl_object_hash($route);
        }
        if (array_key_exists($id, $this->_routes)) {
            unset($this->_routes[$id]);
        }
        return $this;
    }

    /**
     * Get all the routes.
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->_routes;
    }

    /**
     * Route the request.
     *
     * @param mixed $request
     * @return mixed
     */
    public function route(Mage_Core_Controller_Request_Http $request)
    {
        // Do nothing if the router is not enabled.
        if (!$this->isEnabled()) {
            return;
        }
        foreach ($this->getRoutes() as $route) {
            if ($route instanceof Balance_Router_Model_Route_Interface) {
                /**
                 * @var $result Balance_Router_Model_MatchResult_Interface
                 */
                $result = $route->match($request);
                if ($result->isMatched()) {
                    $this->_handle($result);
                    break;
                }
            }
        }
    }

    /**
     * Handle the match result.
     *
     * @param Balance_Router_Model_MatchResult_Interface $matchResult
     */
    protected function _handle(Balance_Router_Model_MatchResult_Interface $matchResult)
    {
        if ($matchResult instanceof Balance_Router_Model_MatchResult_Redirect) {
            $handler = Mage::getModel('balance_router/handler_redirect');
            $handler->handle($matchResult);
        }
    }
}