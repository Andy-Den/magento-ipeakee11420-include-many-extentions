<?php

/**
 * Rewrite for add customer email
 *
 * @category  Milandirect
 * @package   Milandirect_Criteotags
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2016 Balance
 */
class Milandirect_Criteotags_Block_Tags_Cart extends WIC_Criteotags_Block_Tags_Cart
{
    /**
     * @return string
     */
    protected function _toHtml()
    {
        $html = '';
        if (Mage::helper('criteotags')->isEnabled()) {
            /*
             * MILCRIT-3: Turn on or off Criteo on cart page.
             * */
            if (Mage::helper('criteotags')->enableInCartpage()) {
                $html .= '<script type="text/javascript">';
                $html .= 'window.criteo_q = window.criteo_q || [];';
                $html .= 'window.criteo_q.push(';
                $html .= '{ event: "setAccount", account: '. Mage::helper('criteotags')->getAccountId() .'},';
                if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                    $email = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
                    $html .= '{ event: "setEmail", email: ["'.$email.'"] },';
                } else {
                    $html .= '{ event: "setEmail", email: [""] },';
                }
                $html .= '{ event: "setSiteType", type: "' . Mage::helper('criteotags')->getSitetype() . '"},';

                if (Mage::helper('criteotags')->getCustomerId()) {
                    $html .= '{ event: "setCustomerId", id: ' . Mage::helper('criteotags')->getCustomerId() . '},';
                }

                $html .= '{event: "viewBasket", ';
                $html .= 'product: [ ';

                foreach ($this->getCartItems() as $_item) {
                    $html .= '{ id: "' . $_item->getProductId() .
                            '", price: ' . $_item->getPrice() . ', quantity: ' . (int)$_item->getQty() . ' },';
                }

                $html .= ']}';
                $html .= ');';
                $html .='</script>';
            }
        }
        return $html;
    }
}
 