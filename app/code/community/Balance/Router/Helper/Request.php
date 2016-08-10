<?php

/**
 * Class Balance_Router_Helper_Request
 *
 * @author Derek Li
 */
class Balance_Router_Helper_Request
{
    /**
     * Check if the http request matches the module, controller and action.
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @param string $module The module name.
     * @param null|string $controller OPTIONAL The controller name.
     * @param null|string $action OPTIONAL The action name.
     * @return bool
     */
    public function matchRequest(Mage_Core_Controller_Request_Http $request, $module, $controller = null, $action = null)
    {
        if ($request->getModuleName() != $module) {
            return false;
        }
        if (isset($controller) && $request->getControllerName() != $controller) {
            return false;
        }
        if (isset($action) && $request->getActionName() != $action) {
            return false;
        }
        return true;
    }
}
