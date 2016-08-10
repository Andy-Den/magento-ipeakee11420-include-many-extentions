<?php

require_once __DIR__.'/../abstract.php';
require_once __DIR__.'/../../app/Mage.php';
umask(0);
//Mage::app('admin');

if (isset($_SERVER['MAGE_IS_DEVELOPER_MODE'])) {
    Mage::setIsDeveloperMode(true);
}
ini_set('memory_limit','2048M');
ini_set('display_errors', 1);
set_time_limit(0);
function assist($e)
{
    print_r($e);
    echo PHP_EOL;
}

class Milandirect_Shell_TestPaymentMethod extends Mage_Shell_Abstract
{

    public function run()
    {
        $orderId = $this->getArg('id');
        if (!empty($orderId)) {
            $payment =  Mage::getModel('sales/order')->load($orderId)->getPayment();
            assist($payment->getData());
            assist(get_class($payment));
            assist($payment->getMethod());
            $method = $payment->getMethodInstance();
            assist(get_class($method));
        }
    }
}

try {
    $shell = new Milandirect_Shell_TestPaymentMethod();
    $shell->run();
} catch (Exception $e) {
    echo $e->getMessage().PHP_EOL;
}


