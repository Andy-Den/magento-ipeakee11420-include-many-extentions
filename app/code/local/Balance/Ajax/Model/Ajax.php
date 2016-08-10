<?php

/**
 * Balance ajax price extension for Magento
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
 * @copyright  Copyright (C) 2013 Balance Internet (http://www.balanceinternet.com.au)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Short description of the class
 *
 * Long description of the class (if any...)
 *
 * @category   Balance
 * @package    Balance_Ajax
 * @subpackage Model
 * @author     Richard Cai <richard@balanceinternet.com.au>
 */
class Balance_Ajax_Model_Ajax
{
    /**
     * magic method
     *
     * @param unknown_type $method
     * @param unknown_type $args
     */
    public function __call($method, $args)
    {
        $param = $args[0];
        if (method_exists($this, $method)) {
            return $this->$method($param);
        } else {
            return $this->_getDefault($param);
        }
    }

    /**
     * get global message
     *
     * @param array $param
     *
     * @return Mage_Core_Block_Abstract $msg_block
     */
    public function getGlobalMessages($param)
    {
        $msg_block = Mage::app()->getLayout()->getMessagesBlock();

        $msg_block->addMessages(Mage::getSingleton('core/session')->getMessages(true));
        $msg_block->addMessages(Mage::getSingleton('checkout/session')->getMessages(true));
        $msg_block->addMessages(Mage::getSingleton('catalog/session')->getMessages(true));
        $msg_block->addMessages(Mage::getSingleton('customer/session')->getMessages(true));
        $msg_block->addMessages(Mage::getSingleton('wishlist/session')->getMessages(true));

        return $msg_block;
    }

    protected function _getDefault($param)
    {
        if (!isset($param['type']) || empty($param['type'])) {
            Mage::logException(new Exception('Balance Ajax Extension: No type specified.'));

            return false;
        }

        if (!empty($param['name'])) {
            $block = Mage::app()->getLayout()->getBlock($param['name']);
        }

        //generate default template
        if (empty($block)) {
            $block = Mage::app()->getLayout()->createBlock($param['type'], isset($param['name']) ? $param['name'] : '');
        }

        if (isset($param['template']) && !empty($param['template'])) {
            $block->setTemplate($param['template']);
        }

        return $block;
    }

}
