<?php

/**
 * Install script to add cms blocks and cms pages
 *
 * @category  Themesetup
 * @package   Milandirect_Themesetup
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2016 Balance
 */

// update tax
$installer = $this;
$installer->startSetup();

$dataPath = Mage::getModuleDir('data', 'Milandirect_Themesetup');
$dataPath .= DS . 'milandirect_themesetup_setup' . DS . 'data';


// Update tax config
$encoded    = file_get_contents($dataPath . DS . 'taxconfig29042016.json');
$taxCf   = json_decode($encoded, true);
Mage::log("Total".sizeof($taxCf), null, 'testscript.log');
if (sizeof($taxCf) > 0) {
    /**
     * Get the resource model
     */
    $resource = Mage::getSingleton('core/resource');
    /**
     * Retrieve the write connection
     */
    $writeConnection = $resource->getConnection('core_write');
    $sql = 'DELETE FROM core_config_data WHERE path LIKE "tax%"';
    $result = $writeConnection->query($sql);
    Mage::log($result, 'testscript.log');
}
foreach ($taxCf as $config) {
    $installer->setConfigData($config['path'], $config['value'], $config['scope'], $config['scope_id']);
}
$installer->endSetup();
