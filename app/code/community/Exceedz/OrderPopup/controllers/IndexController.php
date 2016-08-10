<?php
class Exceedz_OrderPopup_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * index action
     *
     * @return string
     */
    public function indexAction()
    {
        try {
            $params = $this->getRequest()->getParams();
            $orderDetails = Mage::getSingleton('core/session')->getOrderViewDetails();
            $orderDetails[] = $params['id'];
            Mage::getSingleton('core/session')->setOrderViewDetails($orderDetails);
            $result['message'] = $this->__('Cookie is set.');
        } catch (Mage_Core_Exception $e) {
             $result['message'] = $this->__('Cookie is not set.');
        }

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    /**
     * Display popup
     *
     * @return string
     */
    public function showAction()
    {
        $this->loadLayout();
        $this->getLayout()->createBlock(
            'orderpopup/view',
            'orderpopup_block',
            array('template' => 'orderpopup/order-popup.phtml')
        );
        $result['order_content'] = $this->getLayout()->getBlock('orderpopup_block')->toHtml();

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }
}