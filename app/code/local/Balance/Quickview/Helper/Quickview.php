<?php

/**
 * Balance_Quickview extension
 *
 * @category  Balance
 * @package   Balance_Quickview
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */
class Balance_Quickview_Helper_Quickview extends Mage_Core_Helper_Abstract
{
    /**
     * Get Title config
     * @return string
     */
    public function getTitle(){
		$_title = Mage::getStoreConfig('quickview/setting/title', Mage::app()->getStore());
        if (!$_title) $_title = 'View';
        return $_title;
	}
}