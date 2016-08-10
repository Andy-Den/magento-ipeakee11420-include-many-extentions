<?php

/**
 * Milandirect dotmailer cron
 *
 * @category  Milandirect
 * @package   Milandirect_Adminhtml
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2016 Balance
 */
require_once __DIR__.'/../abstract.php';
class Mage_Shell_Milandirect_dotmailerCron extends Mage_Shell_Abstract
{
    /**
     * Run shell
     * @return void
     */
    public function run()
    {
        if (!$this->getArg('autostatus')) {
            Mage::getModel('ddg_automation/cron')->abandonedCarts();
            Mage::getModel('ddg_automation/cron')->sendEmails();
        } else {
            Mage::getModel('ddg_automation/cron')->automationStatus();
        }
    }
}

$shell = new Mage_Shell_Milandirect_dotmailerCron();
$shell->run();
