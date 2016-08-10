<?php

class Balance_Dynamicremarketing_Block_Remarketing extends Mage_Core_Block_Template
{
    public function isActive()
    {
        return Mage::getStoreConfig('google/dynamic_remarketing/active', Mage::app()->getStore())
            ? Mage::getStoreConfig('google/dynamic_remarketing/active', Mage::app()->getStore()) : 0;
    }

    public function getConversionId()
    {
        return Mage::getStoreConfig('google/dynamic_remarketing/conversion_id', Mage::app()->getStore())
            ? Mage::getStoreConfig('google/dynamic_remarketing/conversion_id', Mage::app()->getStore()) : '';
    }

    public function getConversionLabel()
    {
        return Mage::getStoreConfig('google/dynamic_remarketing/conversion_label', Mage::app()->getStore())
            ? Mage::getStoreConfig('google/dynamic_remarketing/conversion_label', Mage::app()->getStore()) : '';
    }

    public function getCartProdid()
    {
        $prodid = '';
        $items = $this->getQuote()->getAllVisibleItems();

        if (count($items) > 0) {
            $prodid .= '[';
            $productIds = array();
            foreach ($items as $item) {
                $productIds[] .= '\'' . $item->getProductId() . '\'';
            }
            $prodid .= implode(',', array_unique($productIds));
            $prodid .= ']';
        } else {
            $prodid = '\'\'';
        }

        return $prodid;
    }

    public function getTotal()
    {
        $total = $this->getQuote()->getGrandTotal();

        return number_format($total, 2);
    }

    public function getQuote()
    {
        return $this->helper('checkout/cart')->getQuote();
    }

    public function getPageType()
    {
        $req = Mage::app()->getRequest();
        if ($req->getRequestedRouteName() . '_' . $req->getRequestedControllerName() . '_'
            . $req->getRequestedActionName() == 'cms_index_index'
        ) {
            return "home";
        }

        return "other";
    }


    public function getCatProducts($collection)
    {
        $products = array('id' => '', 'total' => '');
        if (count($collection) > 0) {
            $products['id'] .= '[';
            $products['total'] .= '[';
            $productIds = array();
            $productTotals = array();
            foreach ($collection as $product) {
                $productIds[] = '\'' . $product->getId() . '\'';
                $productTotals[] = '\'' . $product->getPrice() . '\'';
            }
            $products['id'] .= implode(',', $productIds);
            $products['id'] .= ']';
            $products['total'] .= implode(',', $productTotals);
            $products['total'] .= ']';
        } else {
            $products['total'] = '\'\'';
            $products['id'] = '\'\'';
        }

        return $products;
    }
}