<?php

/**
 * Override Milandirect_OneStepCheckout to change response message
 *
 * @category  Milandirect
 * @package   Milandirect_OneStepCheckout
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */
require_once "Idev/OneStepCheckout/controllers/AjaxController.php";
class Milandirect_OneStepCheckout_AjaxController extends Idev_OneStepCheckout_AjaxController
{
    /**
     * Guest login with Subscribe
     *
     * @return Mage_Core_Controller_Varien_Action|void
     */
    public function guestCheckoutAction()
    {
        if ($this->getRequest()->isPost()) {
            $customerSession    = Mage::getSingleton('customer/session');
            $quote = $this->_getOnepage()->getQuote();
            $email = $this->getRequest()->getPost('onestepcheckout_guestemail');
            $quote->getBillingAddress()->setEmail($email);
            $quote->save();
            $isSubscribe = $this->getRequest()->getPost('is_subscribed');
            if ($isSubscribe == 1) {
                $ownerId = Mage::getModel('customer/customer')
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                    ->loadByEmail($email)
                    ->getId();
                if ($ownerId !== null && $ownerId != $customerSession->getId()) {
                    // Mage::throwException($this->__('This email address is already assigned to another user.'));
                } else {
                    $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
                }
            }
            $result = array('success' => true);
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }

    /**
     * Override to add more json response
     *
     * @return Mage_Core_Controller_Varien_Action|void
     */
    public function loginAction()
    {
        $username = $this->getRequest()->getPost('onestepcheckout_username', false);
        $password = $this->getRequest()->getPost('onestepcheckout_password', false);
        $session = Mage::getSingleton('customer/session');

        $result = array('success' => false);

        if ($username && $password) {
            try {
                $session->login($username, $password);
            } catch (Exception $e) {
                $result['error'] = $e->getMessage();
            }
            if (! isset($result['error'])) {
                $result['success'] = true;
            }
        } else {
            $result['error'] = $this->__('Please enter a username and password.');
        }
        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();
        $update->addHandle('onestepcheckout_index_ajax');
        $this->loadLayoutUpdates();
        $this->generateLayoutXml();
        $this->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;
        $block = $this->getLayout()->getBlock('content');
        if (is_object($block)) {
            $onestepBlock = $this->getLayout()->getBlock('onestepcheckout.checkout');
            if (is_object($onestepBlock)) {
                $onestepBlock->setTemplate('onestepcheckout/checkoutajax.phtml')->toHtml();
            }
            $result['htmlupdate'] = $block->toHtml();
        }
        $result['logged']=$this->getLayout()->createBlock('page/html_header')->setTemplate('page/html/link-languages-ajax.phtml')->toHtml();

        $this->getResponse()->setBody(Zend_Json::encode($result));

    }
}
