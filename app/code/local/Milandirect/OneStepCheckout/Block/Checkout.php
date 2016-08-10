<?php

/**
 * Override Milandirect_OneStepCheckout
 *
 * @category  Milandirect
 * @package   Milandirect_OneStepCheckout
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */
class Milandirect_OneStepCheckout_Block_Checkout extends Idev_OneStepCheckout_Block_Checkout
{
    /**
     * Override to fix problem on coutry
     * @param $type string billing/shipping
     * @return void
     */
    public function getCountryHtmlSelect($type)
    {
        if($type == 'billing')  {
            $address = $this->getQuote()->getBillingAddress();
            /*
             $address = $this->getQuote()->getCustomer()->getPrimaryBillingAddress();
             if (!$this->isCustomerLoggedIn() || $address == null)
             $address = $this->getQuote()->getBillingAddress();
             */
        }
        else    {
            $address = $this->getQuote()->getShippingAddress();

            /*
             $address = $this->getQuote()->getCustomer()->getPrimaryShippingAddress();
             if (!$this->isCustomerLoggedIn() || $address == null)
             $address = $this->getQuote()->getShippingAddress();
             */
        }

        $countryId = $address->getCountryId();
        if (is_null($countryId) || $countryId == '' || $countryId == ' ') {
            $countryId = Mage::getStoreConfig('general/country/default');
        }
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'[country_id]')
            ->setId($type.':country_id')
            ->setTitle(Mage::helper('checkout')->__('Country'))
            ->setClass('validate-select')
            ->setValue($countryId)
            ->setOptions($this->getCountryOptions());
        if ($type === 'shipping') {
            $select->setExtraParams('onchange="shipping.setSameAsBilling(false);"');
        }

        return $select->getHtml();
    }
}
