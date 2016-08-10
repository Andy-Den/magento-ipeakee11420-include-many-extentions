<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Balance
 * @package    ConfigurableSimplePriceOverride
 * @copyright  Copyright (c) 2011 Balance
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

class Balance_ConfigurableSimplePriceOverride_Model_Sales_Quote extends Mage_Sales_Model_Quote
{

    
    public function updateItem($itemId, $buyRequest, $params = null)
    {
        $item = $this->getItemById($itemId);
        if (!$item) {
            Mage::throwException(Mage::helper('sales')->__('Wrong quote item id to update configuration.'));
        }
        $productId = $item->getProduct()->getId();
 
        if ($buyRequest->getProduct() != $productId) {               
            $productId = $buyRequest->getProduct();
        }

        $product = Mage::getModel('catalog/product')
            ->setStoreId($this->getStore()->getId())
            ->load($productId);
 
        if (!$params) {
            $params = new Varien_Object();
        } else if (is_array($params)) {
            $params = new Varien_Object($params);
        }
        $params->setCurrentConfig($item->getBuyRequest());
        $buyRequest = Mage::helper('catalog/product')->addParamsToBuyRequest($buyRequest, $params);
 
        $buyRequest->setResetCount(true);
        $resultItem = $this->addProduct($product, $buyRequest);
 
        if (is_string($resultItem)) {
            Mage::throwException($resultItem);
        }
 
        if ($resultItem->getParentItem()) {
            $resultItem = $resultItem->getParentItem();
        }
 
        if ($resultItem->getId() != $itemId) {
            $this->removeItem($itemId);
 
            $items = $this->getAllItems();
            foreach ($items as $item) {
                if (($item->getProductId() == $productId) && ($item->getId() != $resultItem->getId())) {
                    if ($resultItem->compare($item)) {
                        $resultItem->setQty($resultItem->getQty() + $item->getQty());
                        $this->removeItem($item->getId());
                        break;
                    }
                }
            }
        } else {
            $resultItem->setQty($buyRequest->getQty());
        }
 
        return $resultItem;
    }

}
		