<?php

/**
 * Rewrite Tax from local Mage
 *
 * @category  Milandirect
 * @package   Milandirect_Tax
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2016 Balance
 */
class Milandirect_Tax_Model_Sales_Total_Quote_Tax extends Mage_Tax_Model_Sales_Total_Quote_Tax
{
    /**
     * Add tax totals information to address object
     *
     * @param   Mage_Sales_Model_Quote_Address $address address object
     * @return  Mage_Tax_Model_Sales_Total_Quote
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $applied    = $address->getAppliedTaxes();
        $store      = $address->getQuote()->getStore();
        $amount     = $address->getTaxAmount();
        $area       = null;
        if ($this->_config->displayCartTaxWithGrandTotal($store) && $address->getGrandTotal()) {
            $area   = 'taxes';
        }

        if (($amount == 0) && ($this->_config->displayCartZeroTax($store))) {
            $taxTitle = 'GST';
            $taxPercent = 11;
            /**
             * Adding finishings to this terrible core hack because this version
             * of the site is only up for another 3 months, please remove in upgrade
             */
            if ($store->getCode() != 'australia' && $store->getCode() != 'md_finishings') {
                $taxTitle = 'TAX';
                $taxPercent = 6;
            }
            $amount = $address->getGrandTotal() / $taxPercent;
            $address->addTotal(array(
                'code'      => $this->getCode(),
                'title'     => Mage::helper('tax')->__('Includes: %s of', $taxTitle),
                'full_info' => $applied ? $applied : array(),
                'value'     => $amount,
                'area'      => $area
            ));
        } elseif (($amount!=0) || ($this->_config->displayCartZeroTax($store))) {
            $address->addTotal(array(
                'code'      => $this->getCode(),
                'title'     => Mage::helper('tax')->__('Tax'),
                'full_info' => $applied ? $applied : array(),
                'value'     => $amount,
                'area'      => $area
            ));
        }

        $store = $address->getQuote()->getStore();
        /**
         * Modify subtotal
         */
        if ($this->_config->displayCartSubtotalBoth($store) || $this->_config->displayCartSubtotalInclTax($store)) {
            if ($address->getSubtotalInclTax() > 0) {
                $subtotalInclTax = $address->getSubtotalInclTax();
            } else {
                $subtotalInclTax = $address->getSubtotal()+$address->getTaxAmount()-$address->getShippingTaxAmount();
            }

            $address->addTotal(array(
                'code'      => 'subtotal',
                'title'     => Mage::helper('sales')->__('Subtotal'),
                'value'     => $subtotalInclTax,
                'value_incl_tax' => $subtotalInclTax,
                'value_excl_tax' => $address->getSubtotal(),
            ));
        }

        return $this;
    }
}