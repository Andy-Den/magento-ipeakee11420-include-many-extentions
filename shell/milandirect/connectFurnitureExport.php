<?php

/**
 * Milandirect shell script export xml
 *
 * @category  Milandirect
 * @package   Milandirect_Adminhtml
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */
require_once '../abstract.php';
class Mage_Shell_Milandirect_connectFurnitureExport extends Mage_Shell_Abstract
{
    /**
     * Run export function
     * @return void
     */
    public function run()
    {
        try {
            Mage::helper('connectfurniture')->exportFeed();
            $message = 'Export successfully.';

        } catch (Exception $e) {

            $message = 'Export failed.';

        }
        echo $message.PHP_EOL;
    }
}

$shell = new Mage_Shell_Milandirect_connectFurnitureExport();
$shell->run();
