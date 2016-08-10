<?php
/**
 * @category    Exceedz
 * @package     Exceedz_Checkout
 */
class Exceedz_Checkout_Block_Cart_Sidebar extends Mage_Checkout_Block_Cart_Sidebar
{
    /**
     * Retrieve is allow and show block
     *
     * @return bool
     */
    public function isShow()
    {
        return true;
    }
}