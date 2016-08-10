<?php

require_once('Mage/Newsletter/controllers/SubscriberController.php');

class Exceedz_Newsletter_SubscriberController extends Mage_Newsletter_SubscriberController {

    private function getSubscribeUrl($name, $email) {
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        switch ($websiteId) {
            case 2:
                $url = "http://exceedmail.createsend.com/t/y/s/puhkru/?cm-name=" . urlencode($name) . "&cm-puhkru-puhkru=" . urlencode($email);
                break;
            default:
                $url = "http://exceedmail.createsend.com/t/y/s/nkrlkr/?cm-name=" . urlencode($name) . "&cm-nkrlkr-nkrlkr=" . urlencode($email);
                break;
        }
        return $url;
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

            $response = $subscriberObj->isNewsletterCookieSet();

            $result['message'] = $subscriberObj->isNewsletterCookieSet() ? $this->__('Cookie is set.') : $this->__('Cookie is not set.');
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    /**
     * New subscription action
     */
    public function getAction() {
        /**
         * disabled temp facebookpopup
         * Milan refer : MDSLA-89
         */
        $enableMilanFacebook = true;
        // If the Facebook Cookie is already set return false
        if ( Mage::getModel('core/cookie')->get('MilanFacebook')) {
            echo 0;
        } else {

            $cookieValue = Mage::getModel('core/cookie')->get('MilanNewsletter');
            $JSON = json_decode($cookieValue);
            $dateTime = $JSON->{'Timestamp'};
            date_default_timezone_set('Australia/Melbourne');
            $timeDelay = 300; // 86400

            //$dateTime = '2013-05-30 08:16:26'; (for testing) //1369847186
            //$cookieSet = strtotime($dateTime) + 86400;
            //Mage::log('time(): ' . time(), $level = null, 'debugfb.log');
            //Mage::log("future time: ' . $cookieSet", $level = null, 'debugfb.log');


            if ((time() > strtotime($dateTime) + $timeDelay ) && (Mage::getModel('core/cookie')->get('MilanNewsletter')) && !$enableMilanFacebook) {  // + 24 hours 3600 * 24
                // Set Cookie
                $websites = Mage::app()->getWebsites();
                foreach ($websites as $website) {
                    $urls = parse_url($website->getDefaultStore()->getBaseUrl());
                    Mage::getModel('core/cookie')->set('MilanFacebook', 1, $cookiePeriod, '/', $urls['host']);
                }
                echo 1;
            } else {
                echo 0;
            }
        }
        die();
    }

    /**
     * New subscription action
     */
//  public function newAction() {
//    die();
//
//    if ($this->getRequest()->getParam('checked') == '1' && $this->getRequest()->getParam('email-address') && $this->getRequest()->getParam('name')) {
//      $session = Mage::getSingleton('core/session');
//      $customerSession = Mage::getSingleton('customer/session');
//      $email = (string) $this->getRequest()->getParam('email-address');
//      $name = (string) $this->getRequest()->getParam('name');
//
//      try {
//        if (!Zend_Validate::is($email, 'EmailAddress')) {
//          Mage::throwException($this->__('Please enter a valid email address.'));
//        }
//
//        if (!Zend_Validate::is($name, 'NotEmpty')) {
//          Mage::throwException($this->__('Please enter name.'));
//        }
//
//        if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 &&
//                !$customerSession->isLoggedIn()) {
//          Mage::throwException($this->__('Sorry, but administrator denied subscription for guests.
//                    Please <a href="%s">register</a>.', Mage::helper('customer')->getRegisterUrl()));
//        }
//
//        $ownerId = Mage::getModel('customer/customer')
//                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
//                ->loadByEmail($email)
//                ->getId();
//        if ($ownerId !== null && $ownerId != $customerSession->getId()) {
//          Mage::throwException($this->__('This email address is already assigned to another user.'));
//        }
//
//        //$url = "http://exceedmail.createsend.com/t/y/s/nkrlkr/?email=$email&name=$name";
//        $url = $this->getSubscribeUrl($name, $email);
//
//        $ch = curl_init($url);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($ch, CURLOPT_POST, true);
//        $result = curl_exec($ch);
//        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//
//        //parsing begins here:
//        $doc = new DOMDocument();
//        @$doc->loadHTML($result);
//        $nodes = $doc->getElementsByTagName('title');
//        //get and display what you need:
//        echo $title = $nodes->item(0)->nodeValue;
//        //$session->addSuccess($title);
//      } catch (Mage_Core_Exception $e) {
//        echo $e;
//        $session->addException($e, $this->__('There was a problem with the subscription: %s', $e->getMessage()));
//      } catch (Exception $e) {
//        echo $e;
//        $session->addException($e, $this->__('There was a problem with the subscription.'));
//      }
//    }
//    //$this->_redirectReferer();
//  }

    /**
     * Subscribe to newsletter - Using send action extended and consolidated in in Balance/Exclusion
    public function sendAction() {
    $result = array();
    if ($this->getRequest()->getParam('cm-name') && $this->getRequest()->getParam('cm-nkrlkr-nkrlkr')) {
    $session = Mage::getSingleton('core/session');
    $customerSession = Mage::getSingleton('customer/session');
    $name = (string) $this->getRequest()->getParam('cm-name');
    $email = (string) $this->getRequest()->getParam('cm-nkrlkr-nkrlkr');

    try {

    if (!Zend_Validate::is($name, 'NotEmpty')) {
    $result['error'] = $this->__('Please enter name.');
    } else if (!Zend_Validate::is($email, 'EmailAddress')) {
    $result['error'] = $this->__('Please enter a valid email address.');
    }

    $url = $this->getSubscribeUrl($name, $email);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, true);
    $resultCurl = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    //parsing begins here:
    $doc = new DOMDocument();
    @$doc->loadHTML($resultCurl);
    $nodes = $doc->getElementsByTagName('title');
    //get and display what you need:
    $title = $nodes->item(0)->nodeValue;
    $result['message'] = $title;
    } catch (Mage_Core_Exception $e) {
    $result['error'] = $this->__('There was a problem with the subscription.');
    } catch (Exception $e) {
    $result['error'] = $this->__('There was a problem with the subscription.');
    }
    }

    $this->getResponse()->setBody(Zend_Json::encode($result));
    }
     *
     */

}
