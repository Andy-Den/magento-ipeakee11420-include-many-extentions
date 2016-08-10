<?php

/**
 * Rewrite Order model from local Mage
 *
 * @category  Milandirect
 * @package   Milandirect_Sales
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2016 Balance
 */
class Milandirect_Sales_Model_Order extends Inecom_Sap_Model_Order
{
    /**
     * Calculate tax amount if it is empty
     * @return float value
     */
    public function getTaxAmountForOrder()
    {
        $tax = 0;
        $store = $this->getStore();
        $config = Mage::getSingleton('tax/config');
        $allowTax = ($this->getTaxAmount() > 0) || ($config->displaySalesZeroTax($store));
        $grandTotal = (float) $this->getGrandTotal();

        if (($this->getTaxAmount() == 0) && ($config->displaySalesZeroTax($store))) {
            $subtotal = (float) $this->getSubtotal();
            $taxTitle = 'GST';
            $taxPercent = 11;
            if ($store->getCode() != 'australia') {
                $taxTitle = 'TAX';
                $taxPercent = 6;
            }
            $tax = $grandTotal / $taxPercent;
        }

        return $tax;
    }

    /**
     * Add this function to call rewrite function
     * @return bool
     */
    public function canReorderIgnoreSalable()
    {
        return $this->_canReorder(true);
    }

    /**
     * @param bool|false $ignoreSalable ignore salable
     * @return bool
     */
    protected function _canReorder($ignoreSalable = false)
    {
        if ($this->canUnhold() || $this->isPaymentReview() || !$this->getCustomerId()) {
            return false;
        }

        if ($this->getActionFlag(self::ACTION_FLAG_REORDER) === false) {
            return false;
        }

        $products = array();
        foreach ($this->getItemsCollection() as $item) {
            $products[] = $item->getProductId();
        }

        if (!empty($products)) {
            foreach ($products as $productId) {
                $product = Mage::getModel('catalog/product')
                    ->setStoreId($this->getStoreId())
                    ->load($productId);
            }
            if (!$product->getId() || (!$ignoreSalable && !$product->isSalable())) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get order comment
     * @return string
     */
    public function getBiebersdorfOrdercomment()
    {
        if ($this->getData('biebersdorf_ordercomment')) {
            return $this->getData('biebersdorf_ordercomment');
        } else {
            return $this->getData('onestepcheckout_customercomment');
        }
    }
}