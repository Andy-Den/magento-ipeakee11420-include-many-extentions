<?php
/**
 * Render last purchased order block
 *
 * @category  Exceedz
 * @package   Exceedz_OrderPopup
 * @copyright 2014 Milandirect
 */
class Exceedz_OrderPopup_Block_View extends Mage_Core_Block_Template
{
    /**
     * The number of orders allowed to view. After user reached this limit counter will be flushed.
     */
    const MAX_VIEWED_ORDERS = 20;

    /**
     * @var null|Mage_Sales_Model_Order Last purchased order
     */
    protected $_lastPurchasedOrder = null;

    /**
     * Get last purchased order
     *
     * @return Mage_Sales_Model_Order
     */
    public function getLastPurchasedOrder()
    {
        Varien_Profiler::start('GetLastPurchaserOrder');
        if (is_null($this->_lastPurchasedOrder)) {
            $viewedOrderIds = Mage::getSingleton('core/session')->getViewedOrderIds();

            $orders = Mage::getModel('sales/order')->getCollection()
                    ->addAttributeToFilter('store_id', Mage::app()->getStore()->getStoreId())
                    ->setOrder('entity_id', 'DESC')
                    ->setPageSize(1)
                    ->setCurPage(1);

            if (!is_null($viewedOrderIds)) {
                if (count($viewedOrderIds) >= self::MAX_VIEWED_ORDERS) {
                    $viewedOrderIds = array();
                } else {
                    $orders->addAttributeToFilter('increment_id', array('nin' => $viewedOrderIds));
                }
            } else {
                $viewedOrderIds = array();
            }

            $this->_lastPurchasedOrder = $orders->getFirstItem();

            $viewedOrderIds[] = $this->_lastPurchasedOrder->getIncrementId();

            Mage::getSingleton('core/session')->setViewedOrderIds($viewedOrderIds);
        }

        Varien_Profiler::stop('GetLastPurchaserOrder');

        return $this->_lastPurchasedOrder;
    }

    /**
     * Get the most expensive product from the last order
     * Exclude products that are not visible individually
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getLastPurchasedProduct()
    {
        $order = $this->getLastPurchasedOrder();
        $orderItems = $order->getItemsCollection()->addAttributeToSort('price', 'DESC');

        foreach ($orderItems as $orderItem) {
            $product = Mage::getModel('catalog/product')->load($orderItem->getProductId());
            if (
                $product->getVisibility() != Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE &&
                !strstr($product->getSku(), 'orderpayment')
            ) {
                return $product;
            }
        }

        return false;
    }

    /**
     * Get the date of last purchased order
     *
     * @return string
     */
    public function getLastPurchasedOrderDate()
    {
        $order = $this->getLastPurchasedOrder();
        Zend_Date::setOptions(array('format_type' => 'php'));
        $date = new Zend_Date($order->getCreatedAtDate());

        return $date->toString('h:ia - dS F,Y');
    }

    /**
     * Get the region of last purchased order
     *
     * @return string
     */
    public function getLastPurchasedOrderRegion()
    {
        $order = $this->getLastPurchasedOrder();
        $billingAddress = $order->getBillingAddress();

        return $billingAddress->getRegion() ? $billingAddress->getRegion() : $billingAddress->getCity();
    }
}