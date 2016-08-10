<?php

require_once('app/code/community/Exceedz/Newsletter/controllers/SubscriberController.php');

class Balance_Exclusion_SubscriberController extends Exceedz_Newsletter_SubscriberController
{

    /**
     * Check email for terms from the exclusion list
     *
     * @param str $name
     * @param str $email
     */
    private function sanatiseEmail($name, $email)
    {
        $termsList = Mage::getModel('exclusion/exclusion')->getResource()->getTermsList();
        foreach ($termsList as $term) {
            if (strstr($email, $term['term'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * New subscription action
     */
    public function sendAction()
    {
        $result = array();
        if ($this->getRequest()->getParam('cm-name') && $this->getRequest()->getParam('cm-nkrlkr-nkrlkr')) {

            $customerSession = Mage::getSingleton('customer/session');
            $email = (string)$this->getRequest()->getPost('cm-nkrlkr-nkrlkr');
            $name = (string)$this->getRequest()->getPost('cm-name');

            try {
                if (!Zend_Validate::is($name, 'NotEmpty')) {
                    $result['error'] = $this->__('Please enter name.');
                } else {
                    if (!Zend_Validate::is($email, 'EmailAddress')) {
                        $result['error'] = $this->__('Please enter a valid email address.');
                    }
                }

                if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 &&
                    !$customerSession->isLoggedIn()) {
                    Mage::throwException($this->__('Sorry, but administrator denied subscription for guests. Please <a href="%s">register</a>.', Mage::helper('customer')->getRegisterUrl()));
                }

                $ownerId = Mage::getModel('customer/customer')
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                    ->loadByEmail($email)
                    ->getId();
                if ($ownerId !== null && $ownerId != $customerSession->getId()) {
                    Mage::throwException($this->__('This email address is already assigned to another user.'));
                }

                if ($this->sanatiseEmail($name, $email)) {
                    $status = Mage::getModel('newsletter/subscriber')->subscribe($email, $name);
                    Mage::dispatchEvent(
                        'balance_newsletter_subscriber_added',
                        array('email' => $email, 'customer_name' => $name)
                    );
                    if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                        $result['message'] = $this->__('You have successfully subscribed to this list');
                    } else {
                        $result['message'] = $this->__('You have successfully subscribed to this list');
                    }
                } else {
                    $result['message'] = $this->__('You have successfully subscribed to this list');
                }
            } catch (Mage_Core_Exception $e) {
                $result['error'] = $this->__('This email address is already assigned to another user.');
                Mage::log($e->getMessage());

            } catch (Exception $e) {
                $result['error'] = $this->__('There was a problem with the subscription.');
                Mage::log($e->getMessage());
            }
        }
        Mage::log($result);
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }
}