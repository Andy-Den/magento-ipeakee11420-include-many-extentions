<?php
$base = dirname(dirname(__FILE__));

require_once $base.'/shell/abstract.php';
require_once $base.'/app/Mage.php';
umask(0);
Mage::app('admin');

if (isset($_SERVER['MAGE_IS_DEVELOPER_MODE'])) {
	Mage::setIsDeveloperMode(true);
}
ini_set('memory_limit','2048M');
ini_set('display_errors', 1);
set_time_limit(0);

class Balance_Shell_Beacon_Cron_Crawler extends Mage_Shell_Abstract
{
	
	public function run()
	{	
//		Mage::helper('deployment/varnish')->purgeAll();
		$crawler = Mage::getModel('varnish/crawler');


        if($this->getArg('no-home')){
            $crawler->setData('crawl_homepage', false);
        }

        if($this->getArg('no-cat')){
            $crawler->setData('crawl_categories', false);
        }

        if($this->getArg('no-prod')){
            $crawler->setData('crawl_products', false);
        }

        if($this->getArg('no-cms')){
            $crawler->setData('crawl_cms', false);
        }

		$crawler->run();
	}

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php crawler.php -- [options]

  --no-cat      Do not crawl category pages
  --no-prod     Do not crawl product pages
  --no-cms      Do not crawl cms pages
  --no-home     Do not crawl home page

USAGE;
    }
}

$shell = new Balance_Shell_Beacon_Cron_Crawler();
$shell->run();

