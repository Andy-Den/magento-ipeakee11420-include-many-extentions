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


class Balance_ConfigurableSimplePriceOverride_Block_Wishlist_Customer_Sidebar extends Mage_Wishlist_Block_Customer_Sidebar
{
  
    protected function _toHtml()
    {
        if ($this->getItemCount()) {
            $this->setTemplate('balance/configurable/wishlist/sidebar.phtml');
            return parent::_toHtml();
        }

        return '';
    }

    
}
