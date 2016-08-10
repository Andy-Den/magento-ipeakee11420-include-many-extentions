<?php
/**
 * Check magento status extension for Magento
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
 * the Balance Crontab module to newer versions in the future.
 * If you wish to customize the Balance Crontab module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Balance
 * @package    Balance_Crontab
 * @copyright  Copyright (C) 2013 Balance Internet (http://www.balanceinternet.com.au)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Short description of the class
 *
 * Long description of the class (if any...)
 *
 * @category   Balance
 * @package    Balance_Crontab
 * @subpackage Model
 * @author     Richard Cai <richard@balanceinternet.com.au>
 */
class Balance_Crontab_Model_Cron_Observer extends Mage_Cron_Model_Observer
{
    public function __construct()
    {
        Mage::helper('crontab')->registerShutdownEventByType(Balance_Crontab_Model_Scheduler_Shutdown::GENERAL_MODE, array($this, 'shutdown'));
    }

    /**
     * shutdown function for general magento cron issue
     * (it's not able to handle dead loop
     */
    public function shutdown()
    {
        //@todo general error handler for magento crontab problem
    }
}
