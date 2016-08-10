<?php

/**
* Web In Color
*
* NOTICE OF LICENSE
*
* This source file is subject to the EULA
* that is bundled with this package in the file WIC-LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://store.webincolor.fr/WIC-LICENSE.txt
* 
* @package		WIC_Criteotags
* @copyright   Copyright (c) 2010-2014 Web In Color (http://www.webincolor.fr)
* @author		Web In Color <contact@webincolor.fr>
**/


class WIC_Criteotags_Helper_Data extends Mage_Core_Helper_Abstract
{
	const XML_CONFIG_ENABLE     = 'criteotags/general/enable';
    const XML_CONFIG_ACCOUNT    = 'criteotags/general/account';
    const XML_CONFIG_SITETYPE   = 'criteotags/general/sitetype';
    const XML_CONFIG_HOMEPAGE   = 'criteotags/general/enable_homepage';
    const XML_CONFIG_CATEGORY   = 'criteotags/general/enable_categorypage';
    const XML_CONFIG_PRODUCT    = 'criteotags/general/enable_productpage';
    const XML_CONFIG_CART       = 'criteotags/general/enable_cart';
    const XML_CONFIG_TRANSACTION = 'criteotags/general/enable_transaction';

    public function isEnabled()
	{
		return Mage::getStoreConfig(self::XML_CONFIG_ENABLE, Mage::app()->getStore()->getStoreId());
	}
	
	public function getAccountId()
	{
		return Mage::getStoreConfig(self::XML_CONFIG_ACCOUNT, Mage::app()->getStore()->getStoreId());
	}
	
	public function getSitetype()
	{
		//return Mage::getStoreConfig(self::XML_CONFIG_SITETYPE, Mage::app()->getStore()->getStoreId());
        // MILCRIT-3
        /* mobile detect*/
        $detect = Mage::helper('mobiledetect');
        if ($detect->isMobile()) {
            $type = 'm';
        } else if ($detect->isTablet()) {
            $type = 't';
        } else {
            $type = 'd';
        }
        return $type;
	}
	
	public function getCustomerId()
	{
		if(Mage::getSingleton('customer/session')->isLoggedIn()) {
			$customerData = Mage::getSingleton('customer/session')->getCustomer();
			return $customerData->getId();
		}
		return;
	}

    /*
     * MILCRIT-3: Turn on or off on each pages.
     * */
    public function enableInHomepage()
    {
        $enable = Mage::getStoreConfig(self::XML_CONFIG_HOMEPAGE, Mage::app()->getStore()->getStoreId());
        if ($enable == NULL) {
            return true;
        } else {
            return $enable;
        }
    }

    public function enableInCategorypage()
    {
        $enable = Mage::getStoreConfig(self::XML_CONFIG_CATEGORY, Mage::app()->getStore()->getStoreId());
        if ($enable == NULL) {
            return true;
        } else {
            return $enable;
        }
    }

    public function enableInProductpage()
    {
        $enable = Mage::getStoreConfig(self::XML_CONFIG_PRODUCT, Mage::app()->getStore()->getStoreId());
        if ($enable == NULL) {
            return true;
        } else {
            return $enable;
        }
    }

    public function enableInCartpage()
    {
        $enable = Mage::getStoreConfig(self::XML_CONFIG_CART, Mage::app()->getStore()->getStoreId());
        if ($enable == NULL) {
            return true;
        } else {
            return $enable;
        }
    }

    public function enableInTransaction()
    {
        $enable = Mage::getStoreConfig(self::XML_CONFIG_TRANSACTION, Mage::app()->getStore()->getStoreId());
        if ($enable == NULL) {
            return false;
        } else {
            return $enable;
        }
    }
}