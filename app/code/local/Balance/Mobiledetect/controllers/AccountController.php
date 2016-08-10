<?php
/**
 * Project: Milandirect
 * File   : AccountController.php
 *
 * @author  Balance Internet
 */


require_once 'Mage/Customer/controllers/AccountController.php';

/**
 * Invitation customer account frontend controller
 *
 * @category   Enterprise
 * @package    Enterprise_Invitation
 */
class Balance_Mobiledetect_AccountController extends Mage_Customer_AccountController
{

    /**
     * Customer login form page
     */
    public function loginAction()
    {
        /* mobile detect*/
        $detect = Mage::helper('mobiledetect');
        $isModelTablet = ($detect->isMobile() ? ($detect->isTablet() ? true : true) : false);

        if (!$isModelTablet) {
            if ($this->_getSession()->isLoggedIn()) {
                $this->_redirect('*/*/');

                return;
            }
            $this->getResponse()->setHeader('Login-Required', 'true');
            $this->loadLayout();
            $this->_initLayoutMessages('customer/session');
            $this->_initLayoutMessages('catalog/session');
            $this->renderLayout();
        } else {
            $this->_redirect('home');

            return;
        }
    }

    /**
     * Customer register form page
     */
    public function createAction()
    {
        /* mobile detect*/
        $detect = Mage::helper('mobiledetect');
        $isModelTablet = ($detect->isMobile() ? ($detect->isTablet() ? true : true) : false);

        if (!$isModelTablet) {
            try {
                //$invitation = $this->_initInvitation();
                $this->loadLayout();
                $this->_initLayoutMessages('customer/session');
                $this->renderLayout();

                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            $this->_redirect('customer/account/login');
        } else {
            $this->_redirect('home');

            return;
        }
    }
}