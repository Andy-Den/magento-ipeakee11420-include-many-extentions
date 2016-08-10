<?php

/**
 * Override Milandirect_Sociallogin to change message send pass
 *
 * @category  Milandirect
 * @package   Milandirect_Sociallogin
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */
require_once "Magestore/Sociallogin/controllers/PopupController.php";

class Milandirect_Sociallogin_PopupController extends Magestore_Sociallogin_PopupController {

    /**
     * Override to change send pass method default
     *
     * @return void
     */
    public function sendPassAction() {
        //$sessionId = session_id();
        $email = $this->getRequest()->getPost('socialogin_email_forgot', false);
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->loadByEmail($email);

        if ($customer->getId()) {
            try {
                $newPassword = $customer->generatePassword();
                $customer->changePassword($newPassword, false);
                $customer->sendPasswordReminderEmail();
                Mage::getSingleton('core/session')->addNotice($this->__('If there is an account associated with ').$email.$this->__(' you will receive an email with a link to reset your password.'));
                $result = array('success'=>true, 'message'=>"If there is an account associated with ".$email." you will receive an email with a link to reset your password.");
            }
            catch (Exception $e){
                $result = array('success'=>false, 'error'=>"Request Time out! Please try again.");
            }
        }
        else {
            $result = array('success'=>false, 'error'=>'Your email address '.$email.' does not exist!');
        }

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    /**
     * Create account action
     * @return void
     */
    public function createAccAction()
    {
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $result = array('success'=>false, 'Can Not Login!');
        } else {
            $firstName =  $this->getRequest()->getPost('firstname', false);
            $lastName =  $this->getRequest()->getPost('lastname', false);
            $pass =  $this->getRequest()->getPost('pass', false);
            $passConfirm =  $this->getRequest()->getPost('passConfirm', false);
            $email = $this->getRequest()->getPost('email', false);
            $customer = Mage::getModel('customer/customer')
                ->setFirstname($firstName)
                ->setLastname($lastName)
                ->setEmail($email)
                ->setPassword($pass)
                ->setConfirmation($passConfirm);

            try {
                $customer->save();
                $subscribeNewsletter = $this->getRequest()->getPost('is_subscribed');
                if (!empty($subscribeNewsletter)) {
                    $model = Mage::getModel('newsletter/subscriber');
                    $model->loadByEmail($customer->getEmail());
                    if (!$model->isSubscribed()) {
                        $model->subscribe($customer->getEmail());
                    }
                }
                Mage::dispatchEvent(
                    'customer_register_success',
                    array('customer' => $customer)
                );
                if ($customer->isConfirmationRequired()) {
                    /** @var $app Mage_Core_Model_App */
                    $app =  Mage::app();
                    /** @var $store  Mage_Core_Model_Store*/
                    $store = $app->getStore();
                    $customer->sendNewAccountEmail(
                        'confirmation',
                        $session->getBeforeAuthUrl(),
                        $store->getId()
                    );
                    $customerHelper = Mage::helper('customer');
                    $result = array(
                        'success'=>false,
                        'error'=>'Account confirmation is required. Please, check your email for the confirmation link.'
                    );
                } else {
                    $result = array('success'=>true);
                    $customer->sendNewAccountEmail('registered', '', Mage::app()->getStore()->getId());
                    $session->setCustomerAsLoggedIn($customer);
                }
                //$url = $this->_welcomeCustomer($customer);
                // $this->_redirectSuccess($url);
            } catch (Exception $e) {
                $result = array('success'=>false, 'error'=>$e->getMessage());
            }
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }
}