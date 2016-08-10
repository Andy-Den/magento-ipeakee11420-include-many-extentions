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

// Create our brand static block
$encoded = file_get_contents($dataPath . DS . 'footerupdate.json');
$blocks = json_decode($encoded, true);
foreach ($blocks as $block) {
    if ($cmsBlock = Mage::getModel('cms/block')->load($block['identifier'])) {
        $cmsBlock->delete();
    }
    if (isset($block['block_id'])) {
        unset($block['block_id']);
    }
    $block['is_active'] = 1;
    $block['stores'] = array(0);
    Mage::getModel('cms/block')->setData($block)->save();
}

$installer->endSetup();