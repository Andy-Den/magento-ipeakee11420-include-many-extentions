<?php

/**
 * Rewrite for add customer email
 *
 * @category  Milandirect
 * @package   Milandirect_Criteotags
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2016 Balance
 */
class Milandirect_Criteotags_Block_Tags_Category extends Milandirect_SimpleConfigurable_Block_Product_List
{
    const PAGE_SIZE = 3;

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $html = '';

        if (Mage::helper('criteotags')->isEnabled()) {
            /*
             * MILCRIT-3: Turn on or off Criteo on category page.
             * */
            if (Mage::helper('criteotags')->enableInCategorypage()) {
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

                $html .= '{event: "viewList" ,';
                $html .= 'product: [ ';

                $html .= 'criteoProductIds';

                $html .= ']}';
                $html .= ');';
                $html .='</script>';
            }
        }

        return $html;
    }
}
