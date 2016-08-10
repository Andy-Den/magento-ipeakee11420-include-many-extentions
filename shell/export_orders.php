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

class Balance_Shell_Export_Orders extends Mage_Shell_Abstract
{
	
	public function run()
	{
		if ($this->getArg('from') && $this->getArg('to')) {
			$start = 1;
			$end = 0;
			$type = 'csv';
			$datefrom = $this->getArg('from');
			$dateto = $this->getArg('to');
			try {
				$exportid = Mage::getModel('export/export')->export($type, (int)$start, (int)$end, $datefrom, $dateto, true, false);
			} catch (Exception $e) {
				Mage::logException($e);
				echo $e->getMessage()."\n";
			}
		}
		else{
			echo $this->usageHelp();
		}
	}

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php export_orders.php -- [options]

  --from      Date Range from
  --to        Date Range to

USAGE;
    }
}

$shell = new Balance_Shell_Export_Orders();
$shell->run();

