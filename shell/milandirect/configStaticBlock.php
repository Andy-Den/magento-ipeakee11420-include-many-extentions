<?php

/**
 * Milandirect shell script change email config
 *
 * @category  Milandirect
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . '/../abstract.php'; // Use absolute path to have it working well with crontab.
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
function assist($e)
{
    print_r($e);
    echo PHP_EOL;
}

ini_set('display_errors', 1);
class Mage_Shell_Milandirect_configStaticBlock extends Mage_Shell_Abstract
{
    protected $_storeId = 0;
    public function run()
    {
        $this->getStore('Australia Store View');
        $collections = $this->getAllBlockByStore();
        if(count($collections)){
            $this->getStore('MD Finishings Store View');
            foreach($collections as $block){
                echo '.';
                $cmsBlockData = array(
                    'title' => $block->getTitle(),
                    'identifier' => $block->getIdentifier(),
                    'stores' => array($this->_storeId),
                    'content' => $block->getContent(),
                    'is_active' => $block->getIsActive(),
                );
                $block = Mage::getModel('cms/block')->setStoreId($this->_storeId)->load($block->getIdentifier());
                if(!$block){
                    Mage::getModel('cms/block')->setData($cmsBlockData)->save();
                }
            }
        }
        $this->getStore('Australia Store View');
        unset($collections);
        $collections = $this->getAllBlock();
        if(count($collections)){
            $this->getStore('MD Finishings Store View');
            foreach($collections as $block){
                echo '.';
                $blocks = Mage::getModel('cms/block')->load($block->getBlockId());
                if(count($blocks->getStores()) > 1){
                    $block = Mage::getModel('cms/block')->setStoreId($this->_storeId)->load($block->getIdentifier());
                    if(!$block){
                        $stores = $block->getStores();
                        $stores[] = $this->_storeId;
                        $blocks->setData('stores',$stores)->save();
                    }
                }
            }
        }
        $this->getStore('MD Finishings Store View');
        $collections = $this->getAllBlockByStore();
        if(count($collections)){
            $this->getStore('MD Finishings Store View');
            foreach($collections as $block){
                echo '.';
                $block = Mage::getModel('cms/block')->load($block->getBlockId());
                if($block){
                    $blocks = Mage::getModel('cms/block')->load($block->getBlockId());
                    $blocks->setData('is_active',1)->save();
                }
            }
        }
        echo PHP_EOL;
        print_r('Clone All Static Block Success');
    }
    protected function getAllBlockByStore(){
        $collection =  Mage::getModel('cms/block')->getCollection()->addStoreFilter($this->_storeId,false);
        return $collection;
    }
    protected function getAllBlock(){
        $collection =  Mage::getModel('cms/block')->getCollection()->addStoreFilter($this->_storeId);
        return $collection;
    }
    public function getStore($name){
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    if($store->getName() == $name){
                        $this->_storeId = $store->getId();
                        break;
                    }
                }
            }
        }
    }
}
try{
    $shell = new Mage_Shell_Milandirect_configStaticBlock();
    $shell->run();
}catch (Exception $ex){
    echo $ex->getMessage();
}

