<?php
/**
 * Short description for file
 * 		This files is used to Refresh_Statistics.
 *
 * PHP versions All
 *
 * @category   PHP Coding File
 * @package
 */

/*********************************************************
* Licence:
* This file is sole property of the installer.
* Any type of copy or reproduction without the consent
* of owner is prohibited.
* If in any case used leave this part intact without
* any modification.
* All Rights Reserved
* Copyright 2009 Owner
*******************************************************/

/*******************************************
* FOLLOWING PARAMETER NEED TO BE CHANGED *
*******************************************/

require_once 'abstract.php';

class Mage_Shell_Refresh_Statistics extends Mage_Shell_Abstract
{

    protected function _getCodes()
    {
        $allcodes = array(
            '0'  => 'sales/report_order',
            '1'  => 'tax/report_tax',
            '2'  => 'sales/report_shipping',
            '3'  => 'sales/report_invoiced',
            '4'  => 'sales/report_refunded',
            '5'  => 'salesrule/report_rule',
            '6'	 => 'sales/report_bestsellers',
        );
        return $allcodes;
    }


    /**
     * Run script
     *
     */
    public function run()
    {
        if ($this->getArg('refresh')) {
            $allCodes = $this->_getCodes();
            try {
                foreach ($allCodes as $collectionName) {
                    Mage::getResourceModel($collectionName)->aggregate();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Lifetime statistics have been updated.'));
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Unable to refresh lifetime statistics.'));
                Mage::logException($e);
            }

            echo "Statistics Refreshed!";

        } elseif ($this->getArg('recent')) {
			$currentDate = Mage::app()->getLocale()->date();
            $date = $currentDate->subHour(25);
			$allCodes = $this->_getCodes();
            try {
                foreach ($allCodes as $collectionName) {
                    Mage::getResourceModel($collectionName)->aggregate($date);
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Recent statistics have been updated.'));
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Unable to refresh recent statistics.'));
                Mage::logException($e);
            }

            echo "Recent Statistics Refreshed!";


		} else {
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
Usage:  php -f refresh_statistics.php -- [options]

  refresh           Resfresh the lifetime statistics
  recent            Resfresh the recent statistics (25 Hours)
  help              This help

USAGE;
    }
}

$shell = new Mage_Shell_Refresh_Statistics();
$shell->run();
