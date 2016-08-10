<?php

/**
 * Balance_Shippingprotection_Model_Observer
 *
 * @author Balance Internet
 */
class Balance_Shippingprotection_Model_Observer
{
    public function collectTotalBefore($argv)
    {
        $quote = $argv->getQuote();
        $subtotal = $quote->getBaseSubtotal();
        if ($subtotal == 0) {
            $quote->setBiIsProtection(null)->save();
        }

        $collectionVirtual = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToFilter('product_protection', array('eq' => 1))
            ->addAttributeToFilter('type_id', array('eq' => 'virtual'));

        $productId = $collectionVirtual->getFirstItem()->getId();

        if ($productId) {
            $product = Mage::getModel('catalog/product')->load($productId);
            $cartItems = $quote->getItemByProduct($product);


            if ($cartItems) {
                $cartItems->setQty(0)->save();
                $subtotal = $subtotal - $cartItems->getCustomPrice();
                if ($subtotal == 0 || $quote->getBiIsProtection() == 2) {
                    $quote->removeItem($cartItems->getId())->save();
                }
            }
            $protectionPrice = 0;
            if ($subtotal <= 399.99) {
                $protectionPrice = 3.99;
            } else {
                $protectionPrice = ceil($subtotal * 0.01) - 0.01;
            }
            $quote->setBiProtectionPrice($protectionPrice);

            if ($quote->getBiIsProtection() == 1) {
                $cartItemsNew = $quote->getItemByProduct($product);
                if ($cartItemsNew) {

                    $cartItemsNew->setQty(1)
                        ->setCustomPrice($protectionPrice)
                        ->setOriginalCustomPrice($protectionPrice)->save();
                } else {
                    $qty = 1;
                    $quoteItem = $quote->addProduct($product, $qty);
                    $quoteItem->setCustomPrice($protectionPrice);
                    $quoteItem->setOriginalCustomPrice($protectionPrice);
                    $quoteItem->getProduct()->setIsSuperMode(true);
                }

            }

            $quote->save();
        }
    }

    public function deleteProtection($argv)
    {
        $collectionVirtual = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToFilter('product_protection', array('eq' => 1))
            ->addAttributeToFilter('type_id', array('eq' => 'virtual'));

        $productId = $collectionVirtual->getFirstItem()->getId();

        if ($productId) {
            $removeProductId = $argv->getEvent()->getQuoteItem()->getProduct()->getId();
            if ($productId == $removeProductId) {
                $quote = Mage::getSingleton('checkout/session')->getQuote();
                $quote->setBiIsProtection(2)->save();
            }
        }
    }


}
