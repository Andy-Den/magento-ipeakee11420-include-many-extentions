<?php
/**
 * One page checkout order review
 *
 * @category    Exceedz
 * @package     Exceedz_Checkout
 */
class Exceedz_Checkout_Block_Onepage_Review_Info extends Mage_Checkout_Block_Onepage_Review_Info
{
    /*
     * Used for accordion
     */
    public function isShow()
    {
        return true;
    }
}
