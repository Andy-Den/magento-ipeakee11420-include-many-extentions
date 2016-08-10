<?php

/**
 * Override Milandirect_OneStepCheckout to change response message
 *
 * @category  Milandirect
 * @package   Milandirect_OneStepCheckout
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */
class Milandirect_OneStepCheckout_Block_Fields extends Milandirect_OneStepCheckout_Block_Checkout
{
    /**
     * Construct function
     * @return void
     */
    public function _construct(){
        $this->setSubTemplate(true);
        parent::_construct();
    }
}