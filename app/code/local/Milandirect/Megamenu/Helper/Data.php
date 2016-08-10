<?php

/**
 * Override Magemenu Helper
 *
 * @category  Milandirect
 * @package   Milandirect_Megamenu
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */
class Milandirect_Megamenu_Helper_Data extends Magestore_Megamenu_Helper_Data
{
    /**
     * Save Cache
     * @param Varien_Object $store store want to save
     * @return void
     */
    public function saveCacheHtml($store = null)
    {
        $currentStore = Mage::app()->getStore()->getStoreId();
        $stores = Mage::app()->getStores(true);
        foreach ($stores as $id => $store) {
            /*
             * set package name to call template in custom theme
             * if template is not exist, it fall back to base theme
             * */

            $packageName = Mage::getStoreConfig('design/package/name', $id);
            Mage::app()->getLocale()->emulate($id);
            Mage::app()->setCurrentStore($store->getId());
            Mage::getDesign()->setArea('frontend')
                ->setStore($store->getId())
                ->setPackageName($packageName) //Name of Package
                ;
            Mage::getSingleton('core/design')->loadChange($store->getId());
            $layout = Mage::getModel('core/layout');
            $block = $layout->createBlock('megamenu/navigationtop')->setTemplate('megamenu/topmenu.phtml');

            $html = $block->toHtml();

            $staticBlock = Mage::getModel('cms/block')->load('megamenu_' . $id, 'identifier');
            if (!$staticBlock->getId()) {
                $staticBlock = Mage::getModel('cms/block');
                $staticBlock->setData('title', 'Mega Menu ' . $store->getName());
                $staticBlock->setData('identifier', 'megamenu_' . $id);
                $staticBlock->setId(null)->save();
            }
            $staticBlock->setStores(array($id))->setContent($html)->save();

            $blockleft = $layout->createBlock('megamenu/navigationleft')
                ->setTemplate('megamenu/navigationleft.phtml');

            $htmlleft = $blockleft->toHtml();
            $staticBlockleft = Mage::getModel('cms/block')->load('megamenuleft_' . $id, 'identifier');
            if (!$staticBlockleft->getId()) {
                $staticBlockleft = Mage::getModel('cms/block');
                $staticBlockleft->setData('title', 'Mega Menu ' . $store->getName());
                $staticBlockleft->setData('identifier', 'megamenuleft_' . $id);
                $staticBlockleft->setId(null)->save();
            }
            $staticBlockleft->setStores(array($id))->setContent($htmlleft)->save();

            Mage::app()->getLocale()->revert();
        }
        Mage::app()->setCurrentStore($currentStore);
        Mage::getModel('core/config')->saveConfig('megamenu/general/reindex', 0);
        Mage::app()->getCacheInstance()->cleanType('config');
        Mage::app()->getCacheInstance()->cleanType('block_html');

    }
}
