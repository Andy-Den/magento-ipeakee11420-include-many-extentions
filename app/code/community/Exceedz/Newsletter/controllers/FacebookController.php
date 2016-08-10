<?php

class Exceedz_Newsletter_FacebookController extends Mage_Core_Controller_Front_Action {

    /**
     * Facebook Like Popup
     */
    public function likeAction() {

        $block = $this->getLayout()->createBlock('core/template')->setTemplate('facebookpopup/like.phtml');
        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * Facebook Like Check
     */
    public function likecheckAction() {
        $cookieName = 'MilanNewsletter';
        $cookieValue = Mage::getModel('core/cookie')->get($cookieName)->getLifetime();
        Mage::log('cookievalue: ' . $cookieValue);

        $debug = true;
        $cook = Mage::getModel('core/cookie');
        if ($cookieValue == 'Not Subscribe') {

        } else {

            return false;
        }
    }

    /**
     * New subscription action
     */
    public function addAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function checkAction() {
        $subscriberObj = Mage::getModel('newsletter/subscriber');

        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('cookie') == 'set') {
            $subscriberObj->setNewsletterCookie();
            $result['message'] = $this->__('Cookie is set.');
        } else if ($this->getRequest()->isPost() &&
            $this->getRequest()->getPost('cookie') == 'check') {
            $result['message'] = $subscriberObj->isNewsletterCookieSet() ? $this->__('Cookie is set.') : $this->__('Cookie is not set.');
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

}
