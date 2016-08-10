<?php

/**
 * Override Milandirect_Sociallogin to change redirect
 *
 * @category  Milandirect
 * @package   Milandirect_Sociallogin
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2016 Balance
 */
require_once "Magestore/Sociallogin/controllers/TwloginController.php";
class Milandirect_Sociallogin_TwloginController extends Magestore_Sociallogin_TwloginController
{
    /**
	 * Create user login/create action
	 * @throws	Mage_Core_Exception
	 * @return	void
	 */
    public function userAction()
    {
        $otwitter = Mage::getModel('sociallogin/twlogin');
        $requestToken = Mage::getSingleton('core/session')->getRequestToken();

        $oauth_data = array(
                'oauth_token' => $this->getRequest()->getParam('oauth_token'),
                'oauth_verifier' => $this->getRequest()->getParam('oauth_verifier')
         );
        try {
            $token = $otwitter->getAccessToken($oauth_data, unserialize($requestToken));
        } catch (Exception $e) {
			Mage::getSingleton('core/session')->addError(
				$this->__('Login failed as you have not granted access.')
			);
			die("<script type=\"text/javascript\">try{window.opener.location.reload(true);}catch(e){window.opener.location.href=\"".Mage::app()->getStore()->getBaseUrl()."\"} window.close();</script>");
		}
		//end fixed
		$params = array(
			'consumerKey'=> Mage::helper('sociallogin')->getTwConsumerKey(), 
			'consumerSecret'=>Mage::helper('sociallogin')->getTwConsumerSecret(), 
			'accessToken'=>$token,
		);
		$twitterId = $token->user_id;// get twitter account ID				
		$customerId = $this->getCustomerId($twitterId);
		
		if ($customerId) { //login
			$customer = Mage::getModel('customer/customer')->load($customerId);
				// fix confirmation
			if ($customer->getConfirmation()) {
				try {
					$customer->setConfirmation(null);
					$customer->save();
				} catch (Exception $e) {
				}
	  		}
			Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
			die("<script type=\"text/javascript\">if(navigator.userAgent.match('CriOS')){window.location.href=\"".$this->_loginPostRedirect()."\";}else{try{window.opener.location.href=\"".$this->_loginPostRedirect()."\";}catch(e){window.opener.location.reload(true);} window.close();}</script>");

        } else {
            // redirect to login page
            $name = (string)$token->screen_name;
            $email = $name . '@twitter.com';
            $user['firstname'] = $name;
            $user['lastname'] = $name;
            $user['email'] = $email;
            //get website_id and sote_id of each stores
            $store_id = Mage::app()->getStore()->getStoreId();
            $website_id = Mage::app()->getStore()->getWebsiteId();
			$customer = Mage::helper('sociallogin')->getCustomerByEmail($user['email'], $website_id);//add edtition	
			if(!$customer || !$customer->getId()){
				//Login multisite
				$customer = Mage::helper('sociallogin')->createCustomerMultiWebsite($user, $website_id, $store_id );
			}	
				// fix confirmation
			if ($customer->getConfirmation())
			{
				try {
					$customer->setConfirmation(null);
					$customer->save();
				}catch (Exception $e) {
				}
	  		}	
			Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);							
			$this->setAuthorCustomer($twitterId, $customer->getId());	
			Mage::getSingleton('core/session')->setCustomerIdSocialLogin($twitterId);						
			if (Mage::getStoreConfig('sociallogin/mplogin/is_send_password_to_customer')){
				$customer->sendPasswordReminderEmail();
			}
			die("<script type=\"text/javascript\">if(navigator.userAgent.match('CriOS')){window.location.href=\"".$this->_loginPostRedirect()."\";}else{try{window.opener.location.href=\"".$this->_loginPostRedirect()."\";}catch(e){window.opener.location.reload(true);} window.close();}</script>");
		}
			
    }

	/**
	 * Get login redirect url
	 * @return mixed|string
	 */
	protected function _loginPostRedirect()
    {
		$selecturl= Mage::getStoreConfig(('sociallogin/general/select_url'),Mage::app()->getStore()->getId());
		if($selecturl == 0) return Mage::getUrl('customer/account');
		if($selecturl == 2) return Mage::getUrl();
		if($selecturl == 3) return Mage::getSingleton('core/session')->getSocialCurrentpage();
		if($selecturl == 4) return Mage::getStoreConfig(('sociallogin/general/custom_page'),Mage::app()->getStore()->getId());
		if($selecturl == 1 && Mage::helper('checkout/cart')->getItemsCount()!=0) return Mage::getUrl('checkout/cart');else return Mage::getUrl();
    }
}