<?php

/**
 *  extension for Magento
 *
 * Long description of this file (if any...)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Balance Ajax module to newer versions in the future.
 * If you wish to customize the Balance Ajax module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Balance
 * @package    Balance_Ajax
 * @copyright  Copyright (C) 2012
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * transport response layer
 *
 * Long description of the class (if any...)
 *
 * @category   Balance
 * @package    Balance_Ajax
 * @subpackage Model
 * @author     Richard Cai <richard@balanceinternet.com.au>
 */
class Balance_Ajax_Controller_Request_Http extends Varien_Object
{

    /**
     * set response to magento response
     *
     * @param Mage_Core_Controller_Response_Http $response
     */
    public function transport($response)
    {
        return $response->setBody($this->toJson());
    }

}