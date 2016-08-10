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
class Mage_Shell_Milandirect_configMenu extends Mage_Shell_Abstract
{
    protected $_storeId = 0;
    public function run()
    {
        $this->getStore('Australia Store View');
        $collections = $this->getAllMenu();
        if(count($collections)){
            $this->getStore('MD Finishings Store View');
            $link =  $this->getArg('link');
            foreach($collections as $menu){
                $data  = $menu->getData();
                unset($data['megamenu_id']);
                $data['stores'] =$this->_storeId;
                if($link){
                    $data['link'] = str_replace('http://mil0016-au.balancenet.com.au',$link,$data['link']);
                }
                $model = Mage::getModel('megamenu/megamenu');
                $model->setData($data);
                $model->save();
                unset($model);
                unset($data);
            }
        }
        print_r('Clone Mega Menu Success');
        echo PHP_EOL;
        print_r("Please go to Admin");
        echo PHP_EOL;
        print_r("Mega Menu > Menu Item  then click edit 1 item and click button save");
    }
    protected function getAllMenu(){
       $collection = Mage::getModel('megamenu/megamenu')->getCollection()->addFieldToFilter('stores',$this->_storeId);
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
    $shell = new Mage_Shell_Milandirect_configMenu();
    $shell->run();
}catch (Exception $ex){
    echo $ex->getMessage();
}

