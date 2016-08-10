<?php

/**
 * Override Milandirect_Sociallogin to change message send pass
 *
 * @category  Milandirect
 * @package   Milandirect_Sociallogin
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */
class Milandirect_Sociallogin_Model_Observer extends Magestore_Sociallogin_Model_Observer
{
    /**
     * Event when customer edit account info
     * @param Varien_Object $observer observer event
     * @return void
     */
    public function customer_edit($observer){
        try {
            Mage::getSingleton('core/session')->setCustomerIdSocialLogin();
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }
}
?>