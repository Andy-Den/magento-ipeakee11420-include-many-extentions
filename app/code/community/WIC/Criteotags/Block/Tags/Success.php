<?php

/**
* Web In Color
*
* NOTICE OF LICENSE
*
* This source file is subject to the EULA
* that is bundled with this package in the file WIC-LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://store.webincolor.fr/WIC-LICENSE.txt
* 
* @package		WIC_Criteotags
* @copyright   Copyright (c) 2010-2014 Web In Color (http://www.webincolor.fr)
* @author		Web In Color <contact@webincolor.fr>
**/

class WIC_Criteotags_Block_Tags_Success extends Mage_Core_Block_Abstract
{
	protected function _toHtml()
	{
		$html = '';
		
		if (Mage::helper('criteotags')->isEnabled())
		{
            /*
             * MILCRIT-3: Turn on or off Criteo tracking Tracsaction.
             * */
            if (Mage::helper('criteotags')->enableInTransaction()) {
                $html .= '<script type="text/javascript">';
                $html .= 'window.criteo_q = window.criteo_q || [];';
                $html .= 'window.criteo_q.push(';
                $html .= '{ event: "setAccount", account: '. Mage::helper('criteotags')->getAccountId() .'},';
                $html .= '{ event: "setSiteType", type: "' . Mage::helper('criteotags')->getSitetype() . '"},';

                if (Mage::helper('criteotags')->getCustomerId())
                {
                    $html .= '{ event: "setCustomerId", id: ' . Mage::helper('criteotags')->getCustomerId() . '},';
                }

                $html .= '{event: "trackTransaction" , id: "' . $this->getTransactionId() . '", new_customer: ' . (int)$this->isFirstPurchase() . ' ,';
                $html .= 'product: [ ';

                foreach ($this->getOrderItems() as $_item)
                {
                    $html .= '{ id: "' . $_item->getProductId() . '", price: ' . $_item->getPrice() .  ', quantity: ' . (int)$_item->getQtyOrdered() . ' },';
                }

                $html .= ']}';
                $html .= ');';
                $html .='</script>';
            }
		}
		
		return $html;
	}
	
	protected function isFirstPurchase()
	{
		$orderCollection = Mage::getModel('sales/order')->getCollection(); 

		$orders = 	$orderCollection
					->addAttributeToFilter("customer_id", array(Mage::helper('criteotags')->getCustomerId()))
					->addAttributeToFilter('state', array('complete'));
					
		if ( $orders->getSize() > 0) { return false; } 
		else { return true;}
		
	}
	
	protected function getTransactionId()
	{
		return Mage::getSingleton('checkout/session')->getLastRealOrderId();
	}
	
	protected function getOrderItems()
	{
		$order = Mage::getSingleton('sales/order')->loadByIncrementId($this->getTransactionId());		
		return $order->getItemsCollection();
	}				
}
 