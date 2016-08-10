<?php

class Exceedz_Newsletter_Model_Subscriber extends Mage_Newsletter_Model_Subscriber {

    private $_cookieName = 'MilanNewsletter';

    /**
     * set the newsletter popup status in a cookie
     *
     * @return boolean true on success.
     */
    public function setNewsletterCookie() {
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
    public function isNewsletterCookieSet() {
        $cookieValue = Mage::getModel('core/cookie')->get($this->_cookieName);
        if ($cookieValue /*== 'Not Subscribe' */)
            return true;
        else
            return false;
    }

}