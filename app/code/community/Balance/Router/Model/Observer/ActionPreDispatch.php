<?php

/**
 * Class Balance_Router_Model_Observer_ActionPreDispatch
 *
 * @author Derek Li
 */
class Balance_Router_Model_Observer_ActionPreDispatch
{
    /**
     * @param $observer
     */
    public function redirectSimpleToConfigurable($observer)
    {
        $request = $observer->getEvent()->getControllerAction()->getRequest();
        /**
         * @var $routeTable Balance_Router_Model_RouteTable_Standard
         * @var $route Balance_Router_Model_Route_SimpleToConfigurable
         */
        $routeTable = Mage::getModel('balance_router/routeTable_standard');
        $route = Mage::getModel('balance_router/route_simpleToConfigurable');
        $routeTable
            ->add($route)
            ->route($request);
    }
}