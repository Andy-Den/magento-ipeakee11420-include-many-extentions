<?php

class Balance_Exclusion_Model_Subscriber extends Dotdigitalgroup_Email_Model_Newsletter_Sub
{
    private $_cookieName = 'MilanNewsletter';

    /**
     * set the newsletter popup status in a cookie
     *
     * @return boolean true on success.
     */
    public function setNewsletterCookie()
    {
        $cookiePeriod = 2592000; // 1 month approx., around 29 days.
        // set cookie for country
        $websites = Mage::app()->getWebsites();
        foreach ($websites as $website) {
            $urls = parse_url($website->getDefaultStore()->getBaseUrl());

            $now = Mage::getModel('core/date')->timestamp(time());
            $dateTime = date('Y-m-d H:i:s', $now);

            $values = array('Shown' => true, 'Timestamp' => $dateTime);
            $json = json_encode($values);

            Mage::getModel('core/cookie')->set($this->_cookieName, $json, $cookiePeriod, '/', $urls['host']);
        }
        return true;
    }

    /**
     * check for the newsletter cookie
     *
     * @return boolean true on success.
     */
    public function isNewsletterCookieSet()
    {
        $cookieValue = Mage::getModel('core/cookie')->get($this->_cookieName);
        /* Not Subscribe */
        if ($cookieValue) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Subscribes by email
     *
     * @param string $email
     *
     * @throws Exception
     * @return int
     */
    public function subscribe($email)
    {
        $name = Mage::registry('subscriber_name') ? Mage::registry('subscriber_name') : '';

        $this->loadByEmail($email);
        $customerSession = Mage::getSingleton('customer/session');

        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->loadByEmail($email);

        if (!$this->getId()) {
            $this->setSubscriberConfirmCode($this->randomSequence());
        }

        $isConfirmNeed = (Mage::getStoreConfig(self::XML_PATH_CONFIRMATION_FLAG) == 1) ? true : false;
        $isOwnSubscribes = false;
        $ownerId = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->loadByEmail($email)
            ->getId();
        $isSubscribeOwnEmail = $customerSession->isLoggedIn() && $ownerId == $customerSession->getId();

        if (!$this->getId() || $this->getStatus() == self::STATUS_UNSUBSCRIBED
            || $this->getStatus() == self::STATUS_NOT_ACTIVE
        ) {
            if ($isConfirmNeed === true) {
                // if user subscribes own login email - confirmation is not needed
                $isOwnSubscribes = $isSubscribeOwnEmail;
                if ($isOwnSubscribes == true) {
                    $this->setStatus(self::STATUS_SUBSCRIBED);
                } else {
                    $this->setStatus(self::STATUS_NOT_ACTIVE);
                }
            } else {
                $this->setStatus(self::STATUS_SUBSCRIBED);
            }
            $this->setSubscriberEmail($email);
            $this->setFirstname($name);
            $this->setLastname('');
        }

        if ($isSubscribeOwnEmail) {
            $this->setStoreId($customerSession->getCustomer()->getStoreId());
            $this->setCustomerId($customerSession->getCustomerId());
        } else {
            $this->setStoreId(Mage::app()->getStore()->getId());
            $this->setCustomerId(0);
        }

        // Customer Logged In & Exists
        if ($customerSession->isLoggedIn()) {
            $this->setStoreId($customerSession->getCustomer()->getStoreId());
            $this->setStatus(self::STATUS_SUBSCRIBED);
            $this->setCustomerId($customerSession->getCustomerId());
            $this->setCustomerFirstName($customerSession->getCustomer()->getData('firstname'));
            $this->setCustomerLastName($customerSession->getCustomer()->getData('lastname'));
            $this->setFirstname($customerSession->getCustomer()->getData('firstname'));
            $this->setLastname($customerSession->getCustomer()->getData('lastname'));
        } else {
            if ($customer->getId()) {
                $this->setStoreId($customer->getStoreId());
                $this->setSubscriberStatus(self::STATUS_SUBSCRIBED);
                $this->setCustomerId($customer->getId());
                $this->setCustomerFirstName($customer->getCustomerFirstName());
                $this->setCustomerLastName($customer->getCustomerLastName());
                $this->setFirstname($customer->getCustomerFirstName());
                $this->setLastname($customer->getCustomerLastName());
            } // Guest Customer
            else {
                $this->setStoreId(Mage::app()->getStore()->getId());
                $this->setCustomerId(0);
                $this->setCustomerFirstName($name);
                $this->setCustomerLastName('');
                $this->setFirstname($name);
                $this->setSubscriberFirstname($name);
                $this->setLastname('');
                $this->setSubscriberLastname('');
            }
        }
        $this->setDateRegister(now());
        $this->setIpAddress(Mage::app()->getRequest()->getServer('REMOTE_ADDR'));

        $url = '';
        if (isset($_SERVER['HTTP_REFERER'])) {
            $url = trim($_SERVER['HTTP_REFERER']);
        }
        $this->setFormUrl($url);
        $this->setIsStatusChanged(true);

        try {
            $this->save();
            if ($isConfirmNeed === true
                && $isOwnSubscribes === false
            ) {
                $this->sendConfirmationRequestEmail();
            } else {
                $this->sendConfirmationSuccessEmail();
            }

            return $this->getStatus();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

}

