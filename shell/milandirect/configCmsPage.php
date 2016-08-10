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
class Mage_Shell_Milandirect_configCmsPage extends Mage_Shell_Abstract
{
    protected $_storeId = 0;
    public function run()
    {
        $this->getStore('Australia Store View');
        $collections = $this->getAllCmsPageByStore();
        if(count($collections)){
            $this->getStore('MD Finishings Store View');
            foreach($collections as $page){
                echo '.';
                $page = Mage::getModel('cms/page')->setStoreId($this->_storeId)->load($page->getIdentifier());
                if(!$page){
                    $cmsPageData = array(
                        'title' => $page->getTitle(),
                        'root_template' => $page->getRootTemplate(),
                        'meta_keywords' => $page->getMetaKeywords(),
                        'meta_description' => $page->getMetaDescription(),
                        'identifier' => $page->getIdentifier(),
                        'content_heading' => $page->getContentHeading(),
                        'stores' => array($this->_storeId),
                        'content' => $page->getContent(),
                        'is_active' => '1',
                        'layout_update_xml' => $page->getLayoutUpdateXml(),
                        'swedish_heading' => $page->getSwedishHeading(),
                    );
                    Mage::getModel('cms/page')->setData($cmsPageData)->save();
                }
            }
        }
        $this->getStore('Australia Store View');
        unset($collections);
        $collections = $this->getAllCmsPage();
        if(count($collections)){
            $this->getStore('MD Finishings Store View');
            foreach($collections as $page){
                echo '.';
                $pages = Mage::getModel('cms/page')->load($page->getPageId());
                if(count($pages->getStoreId()) > 1){
                    $stores = $pages->getStoreId();
                    $stores[] = $this->_storeId;
                    $page = Mage::getModel('cms/page')->setStoreId($this->_storeId)->load($page->getIdentifier());
                    if(!$page){
                        $pages->setData('stores',$stores)->save();
                    }
                }
            }
        }
        echo PHP_EOL;
        print_r('Clone All Cms Page Success');
    }
    protected function getAllCmsPageByStore(){
        $collection =  Mage::getModel('cms/page')->getCollection()->addStoreFilter($this->_storeId,false);
        return $collection;
    }
    protected function getAllCmsPage(){
        $collection =  Mage::getModel('cms/page')->getCollection()->addStoreFilter($this->_storeId);
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
    $shell = new Mage_Shell_Milandirect_configCmsPage();
    $shell->run();
}catch (Exception $ex){
    echo $ex->getMessage();
}

