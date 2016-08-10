<?php

require_once Mage::getModuleDir('controllers', 'Balance_Ajax') . DS . 'IndexController.php';

class Balance_Ajax_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard
{

    protected $_request;

    /**
     * init ajax rounter
     *
     * @param Varien_Event_Observer $observer
     */
    public function initControllerRouter($observer)
    {
        if (Mage::helper('ajax')->canDo()) {
            $front = $observer->getEvent()->getFront();
            $front->addRouter('ajax', new self());
        }
    }

    public function match(Zend_Controller_Request_Http $request)
    {
        if ($request->getPathInfo() == '/ajax/index/fetchview/') {
            $this->_request = $request;
            $data = $request->getParam('data');
            $data = Mage::helper('core')->jsonDecode($data);
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    parse_str($value, $arr);
                    $data[$key] = $arr;
                }
                $this->_request->setParams($data);
            } else {
                return true;
            }
            parent::collectRoutes('frontend', 'standard');
            $response = Mage::app()->getFrontController()->getResponse();
            $controllerInstance = Mage::getControllerInstance(
                'Balance_Ajax_IndexController',
                $this->_request,
                $response
            );
            $request->setModuleName('ajax');
            $request->setControllerName('index');
            $request->setActionName('fetchview');
            $request->setDispatched(true);
            $controllerInstance->dispatch('fetchview');

            return true;
        }

        return false;
    }
}