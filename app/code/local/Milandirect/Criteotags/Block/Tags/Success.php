<?php

/**
 * Rewrite for add customer email
 *
 * @category  Milandirect
 * @package   Milandirect_Criteotags
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2016 Balance
 */
class Milandirect_Criteotags_Block_Tags_Success extends WIC_Criteotags_Block_Tags_Success
{
    /**
     * @return string
     */
    protected function _toHtml()
    {
        $html = '';

        if (Mage::helper('criteotags')->isEnabled()) {
            /*
             * MILCRIT-3: Turn on or off Criteo tracking Tracsaction.
             * */
            if (Mage::helper('criteotags')->enableInTransaction()) {
                $html .= '<script type="text/javascript">';
                $html .= 'window.criteo_q = window.criteo_q || [];';
                $html .= 'window.criteo_q.push(';
                $html .= '{ event: "setAccount", account: '. Mage::helper('criteotags')->getAccountId() .'},';
                if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                    $email = array_unique(
                        array(
                            Mage::getSingleton('customer/session')->getCustomer()->getEmail(),
                            $this->getOrderEmail()
                        )
                    );
                    $emails = implode('","', $email);
                    $html .= '{ event: "setEmail", email: ["'.$emails.'"] },';
                } else {
                    $html .= '{ event: "setEmail", email: ["'.$this->getOrderEmail().'"] },';
                }
                $html .= '{ event: "setSiteType", type: "' . Mage::helper('criteotags')->getSitetype() . '"},';

                if (Mage::helper('criteotags')->getCustomerId()) {
                    $html .= '{ event: "setCustomerId", id: ' . Mage::helper('criteotags')->getCustomerId() . '},';
                }

                $html .= '{event: "trackTransaction" , id: "' .
                         $this->getTransactionId() . '", new_customer: ' . (int)$this->isFirstPurchase() . ' ,';
                $html .= 'product: [ ';

                foreach ($this->getOrderItems() as $_item) {
                    $html .= '{ id: "' . $_item->getProductId() . '", price: ' .
                             $_item->getPrice() .  ', quantity: ' . (int)$_item->getQtyOrdered() . ' },';
                }

                $html .= ']}';
                $html .= ');';
                $html .='</script>';
            }
        }

        return $html;
    }
}
 