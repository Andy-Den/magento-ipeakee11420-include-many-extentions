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
* @package		WIC_Luxroutage
* @copyright   Copyright (c) 2010-2014 Web In Color (http://www.webincolor.fr)
* @author		Web In Color <contact@webincolor.fr>
**/

class WIC_Criteotags_Block_Tags_Cart extends Mage_Core_Block_Abstract
{
	protected function _toHtml()
	{
		$html = '';
		
		if (Mage::helper('criteotags')->isEnabled())
		{
            /*
             * MILCRIT-3: Turn on or off Criteo on cart page.
             * */
            if (Mage::helper('criteotags')->enableInCartpage()) {
                $html .= '<script type="text/javascript">';
                $html .= 'window.criteo_q = window.criteo_q || [];';
                $html .= 'window.criteo_q.push(';
                $html .= '{ event: "setAccount", account: '. Mage::helper('criteotags')->getAccountId() .'},';
                $html .= '{ event: "setSiteType", type: "' . Mage::helper('criteotags')->getSitetype() . '"},';

                if (Mage::helper('criteotags')->getCustomerId())
                {
                    $html .= '{ event: "setCustomerId", id: ' . Mage::helper('criteotags')->getCustomerId() . '},';
                }

                $html .= '{event: "viewBasket", ';
                $html .= 'product: [ ';

                foreach ($this->getCartItems() as $_item)
                {
                    $html .= '{ id: "' . $_item->getProductId() . '", price: ' . $_item->getPrice() .  ', quantity: ' . (int)$_item->getQty() . ' },';
                }

                $html .= ']}';
                $html .= ');';
                $html .='</script>';
            }
		}
		
		return $html;
	}
	
	protected function getCartItems()
	{
		return Mage::getSingleton('checkout/session')->getQuote()->getAllItems();		
	}	
}
 