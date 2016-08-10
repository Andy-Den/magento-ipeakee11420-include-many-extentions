<?php

/**
 * Balance_Shippingprotection_Model_Shippingprotection
 *
 * @author Balance Internet
 */
class Balance_Shippingprotection_Model_Shippingprotection extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('shippingprotection/shippingprotection');
    }
}
