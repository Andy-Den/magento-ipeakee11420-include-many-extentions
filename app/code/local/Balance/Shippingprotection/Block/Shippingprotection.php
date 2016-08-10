<?php

/**
 * Balance_Shippingprotection_Block_Shippingprotection
 *
 * @author Balance Internet
 */
class Balance_Shippingprotection_Block_Shippingprotection extends Mage_Core_Block_Template
{
    public function getDefaultChecked()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        return $quote->getBiIsProtection();
    }

    public function getFreightProtection()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        return Mage::helper('core')->currency($quote->getBiProtectionPrice());
    }
}
