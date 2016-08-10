<?php

/**
 * Rewrite customer block account navigation to add more function
 *
 * @category  Milandirect
 * @package   Milandirect_Customer
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */
class Milandirect_Customer_Block_Account_Navigation extends Mage_Customer_Block_Account_Navigation
{
    /**
     * Remove a link from the customer account navigation menu
     *
     * @param string $name Name of the link
     * @return Balance_Customer_Block_Account_Navigation
     */
    function removeLink($name)
    {
        unset($this->_links[$name]);
        return $this;
    }
}
