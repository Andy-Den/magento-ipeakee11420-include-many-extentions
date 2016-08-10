<?php

/**
 * Override Milandirect_Checkout to change response message
 *
 * @category  Milandirect
 * @package   Milandirect_Checkout
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */
class Milandirect_Checkout_Model_Session extends Balance_Ajax_Model_Checkout_Session
{
    /**
     * Load data for customer quote and merge with current quote
     *
     * @return Mage_Checkout_Model_Session
     */
    public function loadCustomerQuote()
    {
        if (!Mage::getSingleton('customer/session')->getCustomerId()) {
            return $this;
        }

        Mage::dispatchEvent('load_customer_quote_before', array('checkout_session' => $this));

        $customerQuote = Mage::getModel('sales/quote')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomerId());

        if ($customerQuote->getId() && $this->getQuoteId() != $customerQuote->getId()) {
            if ($this->getQuoteId()) {
                if (!$customerQuote->getShippingAddress()->getPostcode()
                    || $customerQuote->getShippingAddress()->getPostcode() == '-'
                    || $customerQuote->getShippingAddress()->getPostcode() == ' '
                ) {
                    $code =  'tablerate_bestway';

                    $customerQuote->getShippingAddress()->setPostcode(
                        $this->getQuote()->getShippingAddress()->getPostcode()
                    )
                        ->setCollectShippingRates(true)
                        ->setShippingMethod($code)
                        ->collectTotals();
                    $customerQuote->getShippingAddress()->collectTotals()->getShippingAmount();
                } else {
                    $code =  'tablerate_bestway';
                    $customerQuote->getShippingAddress()
                        ->setCollectShippingRates(true)
                        ->setShippingMethod($code)
                        ->collectTotals();
                    $customerQuote->getShippingAddress()->collectTotals()->getShippingAmount();
                }
                if ($this->getQuote()->getBiIsProtection() ==1 || $this->getQuote()->getBiIsProtection() == 2) {
                    $customerQuote->merge($this->getQuote())
                        ->setBiIsProtection($this->getQuote()->getBiIsProtection())
                        ->collectTotals()
                        ->save();
                } else {
                    $customerQuote->merge($this->getQuote())
                        ->collectTotals()
                        ->save();
                }
            }
            $this->setQuoteId($customerQuote->getId());

            if ($this->_quote) {
                $this->_quote->delete();
            }
            $this->_quote = $customerQuote;

        } else {
            $this->getQuote()->getBillingAddress();
            $this->getQuote()->getShippingAddress();
            $this->getQuote()->setCustomer(Mage::getSingleton('customer/session')->getCustomer())
                ->setTotalsCollectedFlag(false)
                ->collectTotals()
                ->save();
        }
        return $this;
    }
}
