<?php

/**
 * Install script to add cms blocks and cms pages
 *
 * @category  Themesetup
 * @package   Milandirect_Themesetup
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2016 Balance
 */

// update Tax
$installer = $this;
$installer->startSetup();

$dataPath = Mage::getModuleDir('data', 'Milandirect_Themesetup');
$dataPath .= DS . 'milandirect_themesetup_setup' . DS . 'data';


// Update Tax config
$encoded    = file_get_contents($dataPath . DS . 'taxconfig02062016.json');
$taxCf   = json_decode($encoded, true);
foreach ($taxCf as $config) {
    $installer->setConfigData($config['path'], $config['value'], $config['scope'], $config['scope_id']);
}
$installer->endSetup();
