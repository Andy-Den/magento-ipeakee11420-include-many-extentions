<?php
/**
 * Exceedz Solutions
 * Email Catalog Module
 *
 * @category   Exceedz 
 * @package    Exceedz_EmailCart
 * @copyright  Copyright (c) 2009 Exceedz Solutions
 */

class Exceedz_Emailcatalog_Block_Emailcatalog extends Mage_Checkout_Block_Cart_Abstract
{
	const PARAM_NAME_REFERER_URL        = 'referer_url';
	const PARAM_NAME_BASE64_URL         = 'r64';
    const PARAM_NAME_URL_ENCODED        = 'uenc';
	
	public function _prepareLayout()
    {
		
		return parent::_prepareLayout();
    }
	
	/**
     * Check url to be used as internal
     *
     * @param   string $url
     * @return  bool
     */
    protected function isUrlInternal($url)
    {
        if (strpos($url, 'http') !== false) {
            /**
             * Url must start from base secure or base unsecure url
             */
            if ((strpos($url, Mage::app()->getStore()->getBaseUrl()) === 0)
                || (strpos($url, Mage::app()->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true)) === 0)) {
                return true;
            }
        }
        return false;
    }
	
	/**
     * Identify referer url via all accepted methods (HTTP_REFERER, regular or base64-encoded request param)
     *
     * @return string
     */
    protected function getRefererUrl()
    {
        $refererUrl = $this->getRequest()->getServer('HTTP_REFERER');
        if ($url = $this->getRequest()->getParam(self::PARAM_NAME_REFERER_URL)) {
            $refererUrl = $url;
        }
        if ($url = $this->getRequest()->getParam(self::PARAM_NAME_BASE64_URL)) {
            $refererUrl = Mage::helper('core')->urlDecode($url);
        }
        if ($url = $this->getRequest()->getParam(self::PARAM_NAME_URL_ENCODED)) {
            $refererUrl = Mage::helper('core')->urlDecode($url);
        }

        if (!$this->isUrlInternal($refererUrl)) {
            $refererUrl = Mage::app()->getStore()->getBaseUrl();
        }
        return $refererUrl;
    }
    
    
	public function getSendUrl()
    {
        return $this->getUrl('*/*/send');
    }
    
    public function getUserName()
    {
    	return Mage::getSingleton('customer/session')->getCustomer()->getName();
    }

}