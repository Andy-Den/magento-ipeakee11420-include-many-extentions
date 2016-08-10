<?php
/**
 * Zeon Solutions
 * Email Catalog Module
 *
 * @category   Zeon
 * @package    Zeon_Emailcatalog
 * @copyright  Copyright (c) 2009 Zeon Solutions (http://www.zeonsolutions.com/)
 */

class Exceedz_Emailcatalog_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		$this->loadLayout();
		$this->renderLayout();
    }

	public function sendAction()
    {
    	$receiverName = $this->getRequest()->getPost('senderName');
    	$senderName = $this->getRequest()->getPost('receiverName');
		$link		= $this->getRequest()->getPost('link');
        $emails = explode(',', $this->getRequest()->getPost('receiveremails'));
        $message= nl2br(htmlspecialchars((string) $this->getRequest()->getPost('message')));
        $error  = false;
        if (empty($emails)) {
            $error = $this->__('Email address can\'t be empty.');
        }
        else {
            foreach ($emails as $index => $email) {
                $email = trim($email);
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    $error = $this->__('Invalid email address.');
                    break;
                }
                $emails[$index] = $email;
            }
        }
        if ($error) {
        	Mage::getSingleton('core/session')->addError($error);
            $this->_redirect('*/*/index');
            return;
        }

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        try {
            $customer = Mage::getSingleton('customer/session')->getCustomer();

            $emails = array_unique($emails);
            $emailModel = Mage::getModel('core/email_template');

            foreach($emails as $email) {
                $emailModel->sendTransactional(
                    Mage::getStoreConfig('emailcatalog/email/email_template'),
                    Mage::getStoreConfig('emailcatalog/email/email_identity'),
                    $email,
                    null,
                    array(
                        'customer'      => $customer,
                        'link'          =>  $link,
                        'message'       => $message,
                    	'receiver'		=> $receiverName,
                    	'sender'        => $senderName
                    ));
            }

            $translate->setTranslateInline(true);

            Mage::getSingleton('core/session')->addSuccess(
            	$this->__('Your Catalog was successfully shared'));
            $this->_redirectReferer();
        }
        catch (Exception $e) {
            $translate->setTranslateInline(true);

            Mage::getSingleton('core/session')->addError($e->getMessage());
            Mage::getSingleton('core/session')->setSharingForm($this->getRequest()->getPost());
            $this->_redirectReferer();
        }
    }

}