<?php

/**
 * Install script to add cms blocks and cms pages
 *
 * @category  Themesetup
 * @package   Milandirect_Themesetup
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */

// Create cms blocks by json data
$installer = $this;
$installer->startSetup();

$dataPath = Mage::getModuleDir('data', 'Milandirect_Themesetup');
$dataPath .= DS . 'milandirect_themesetup_setup' . DS . 'data';

// Update amasty config
$encoded    = file_get_contents($dataPath . DS . 'taxconfig.json');
$taxCf   = json_decode($encoded, true);
foreach ($taxCf as $config) {
    $installer->setConfigData($config['path'], $config['value'], $config['scope'], $config['scope_id']);
}


$installer->endSetup();
