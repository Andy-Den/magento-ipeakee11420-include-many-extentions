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

// Create static block
$encoded = file_get_contents($dataPath . DS . 'cmsblocks.json');
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

// Create cms pages by json data
$encoded = file_get_contents($dataPath . DS . 'cmspages.json');
$pages = json_decode($encoded, true);
foreach ($pages as $page) {
    $collection = Mage::getModel('cms/page')->getCollection();
    $collection->addFieldToFilter('identifier', $page['identifier']);
    foreach ($collection as $cmsPage) {
        $cmsPage->delete();
    }
    if (isset($page['page_id'])) {
        unset($page['page_id']);
    }
    $page['stores'] = array(0);
    Mage::getModel('cms/page')->setData($page)->save();
}

// Update theme config
$encoded    = file_get_contents($dataPath . DS . 'themeconfig.json');
$themesCf   = json_decode($encoded, true);
foreach ($themesCf as $config) {
    $installer->setConfigData($config['path'], $config['value'], $config['scope'], $config['scope_id']);
}


// Remove old megamenu
$megaMenus = Mage::getModel('megamenu/megamenu')->getCollection();
foreach ($megaMenus as $megaMenu) {
    $megaMenu->delete();
}

// Megamenu item tempate
$encoded = file_get_contents($dataPath . DS . 'megamenu.json');
$megaMenus = json_decode($encoded, true);
foreach ($megaMenus as $menu) {
    if (isset($menu['megamenu_id'])){
        unset($menu['megamenu_id']);
    }
    $megaTemplate = Mage::getModel('megamenu/megamenu')->setData($menu)->save();
}

$installer->endSetup();
