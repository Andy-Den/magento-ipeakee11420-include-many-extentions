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
 * @subpackage Model
 * @author     Richard Cai <richard@balanceinternet.com.au>
 */
class Balance_Ajax_Model_Checkout_Session extends Mage_Checkout_Model_Session
{
    /**
     * Retrieve messages from session
     *
     * @param   bool $clear
     *
     * @return  Mage_Core_Model_Message_Collection
     */
    public function getMessages($clear = false)
    {
        if (!$this->getData('messages')) {
            $this->setMessages(Mage::getModel('core/message_collection'));
        }

        $object = new Varien_Object();
        $object->setData('flag', $clear);
        Mage::dispatchEvent('session_clear_messages_before', array('object' => $object));

        if ($object->getData('flag')) {
            $messages = clone $this->getData('messages');
            $this->getData('messages')->clear();
            Mage::dispatchEvent('core_session_abstract_clear_messages');

            return $messages;
        }

        return $this->getData('messages');
    }
}
