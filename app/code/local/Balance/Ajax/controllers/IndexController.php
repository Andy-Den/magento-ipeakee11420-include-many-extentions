<?php

class Balance_Ajax_IndexController
    extends Mage_Core_Controller_Front_Action
{
    protected $_eventObject = 'balance_ajax';

    public function fetchviewAction()
    {
        $response = $this->getResponse();
        $transportResponse = new Balance_Ajax_Controller_Request_Http();

        $ref_url = $this->_getRefererUrl();
        if ($this->_isUrlInternal($ref_url)) {
            $host = parse_url($ref_url, PHP_URL_HOST);
            $start = stripos($ref_url, $host) + strlen($host);
            $ref_url = substr($ref_url, $start);
            $ref_url = (substr($ref_url, 0, 1) == '/' ? '' : '/') . $ref_url;

            $_SERVER['REQUEST_URI'] = $ref_url;
        } else {
            $_SERVER['REQUEST_URI'] = '/';
        }

        //added handle so we can use 'ajax="true"' within these handles
        $p = $this->getRequest()->getParams();
        $p = is_array($p) ? array_shift($p) : array('module'=>'','controller'=>'','action'=>'');
        $key = $p['module'] .'_' . $p['controller'] . '_' . $p['action'];

        //using ajax block on product detail page
        if (in_array($key, array('catalog_product_view'))) {
            if (isset($p['id'])) {
                $id = $p['id'];
            } else {
                $id = $p['params']['id'];
            }
            if (!is_object(Mage::registry('current_product'))) {
                $product = Mage::getModel('catalog/product')->load($id);
                Mage::register('current_product', $product);
                Mage::register('product', $product);
            }
            $update = $this->getLayout()->getUpdate();
            $update->addHandle($key);
        }

        $this->loadLayout();

        Mage::dispatchEvent(
            $this->_eventObject . '_dispatch_before',
            array('request' => $this->getRequest(), 'response' => $transportResponse)
        );
        $transportResponse->transport($response);
        Mage::dispatchEvent(
            $this->_eventObject . '_dispatch_after',
            array('request' => $this->getRequest(), 'response' => $response)
        );
    }


    public function cookieCompatitionAction(){
            $cookieCompatitionTime = 3 * 24 * 60 *60;
            Mage::app()->getCookie()->set('banneroffp','1',$cookieCompatitionTime);
            echo Mage::app()->getCookie()->get('banneroffp');
            return;
    }

}
