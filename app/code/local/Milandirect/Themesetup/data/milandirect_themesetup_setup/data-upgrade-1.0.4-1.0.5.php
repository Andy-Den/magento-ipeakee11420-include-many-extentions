<?php

/**
 * Install script to add cms blocks and cms pages
 *
 * @category  Themesetup
 * @package   Milandirect_Themesetup
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2016 Balance
 */

// Create cms blocks by json data
$installer = $this;
$installer->startSetup();

$dataPath = Mage::getModuleDir('data', 'Milandirect_Themesetup');
$dataPath .= DS . 'milandirect_themesetup_setup' . DS . 'data';

// Update onestepcheckotu config
$encoded    = file_get_contents($dataPath . DS . 'onestepcheckout.json');
$oneStepCf   = json_decode($encoded, true);
foreach ($oneStepCf as $config) {
    $installer->setConfigData($config['path'], $config['value'], $config['scope'], $config['scope_id']);
}


$installer->endSetup();
