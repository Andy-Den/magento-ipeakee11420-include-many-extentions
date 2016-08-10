<?php

/**
 * Balance ajax extension for Magento
 *
 * Long description of this file (if any...)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Balance Ajax module to newer versions in the future.
 * If you wish to customize the Balance Ajax module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Balance
 * @package    Balance_Ajax
 * @copyright  Copyright (C) 2013 Balance Internet (http://balanceinternet.com.au)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Short description of the class
 *
 * Long description of the class (if any...)
 *
 * @category   Balance
 * @package    Balance_Ajax
 * @subpackage Block
 * @author     Richard Cai <richard@balanceinternet.com.au>
 */
class Balance_Ajax_Block_Core_Messages extends Mage_Core_Block_Messages
{
    /**
     * (non-PHPdoc)
     *
     * @see Mage_Core_Block_Messages::getGroupedHtml()
     */
    public function getGroupedHtml()
    {
        if (!Mage::helper('ajax')->isAjax() && !Mage::helper('ajax')->shouldSkip()
            && !Mage::app()->getStore()->isAdmin()
        ) {
            return '';
        }

        return parent::getGroupedHtml();
    }

    public function _prepareLayout()
    {
        if (Mage::helper('ajax')->shouldSkip() || Mage::app()->getStore()->isAdmin()) {
            parent::_prepareLayout();
        }
    }

}
