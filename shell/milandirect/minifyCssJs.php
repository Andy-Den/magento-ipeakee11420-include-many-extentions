<?php

/**
 * Milandirect shell script minify css/js
 *
 * @category  Milandirect
 * @package   Milandirect_Adminhtml
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */
require_once '../abstract.php';
class Mage_Shell_Milandirect_minifyCssJs extends Mage_Shell_Abstract
{
    public function run()
    {
        $helper = Mage::helper('apptrian_minify');

        try {
            $helper->process();

            $message = 'Minification operations completed successfully.';

        } catch (Exception $e) {

            $message = 'Minification failed.';

        }
        echo $message.PHP_EOL;
    }
}

$shell = new Mage_Shell_Milandirect_minifyCssJs();
$shell->run();
