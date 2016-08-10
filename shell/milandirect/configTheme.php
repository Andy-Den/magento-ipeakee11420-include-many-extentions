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
class Mage_Shell_Milandirect_configTheme extends Mage_Shell_Abstract
{

    public function run()
    {

        $section = 'design';
        $website = 'finishings';
        $store = null;
        $data = $this->getConfig();
        Mage::getSingleton('adminhtml/config_data')
            ->setSection($section)
            ->setWebsite($website)
            ->setStore($store)
            ->setGroups($data)
            ->save();

        // reinit configuration
        Mage::getConfig()->reinit();
        Mage::dispatchEvent('admin_system_config_section_save_after', array(
            'website' => $website,
            'store'   => $store,
            'section' => $section
        ));
        Mage::app()->reinitStores();
        echo 'Config Theme Success';
    }
    protected function getConfig(){
        $data = array(
            'package' =>array(
                'fields' => array(
                    'name' => array( 'value'=>'milandirectupdate'),
                    'ua_regexp' => array( '__empty'=>'')
                )
            ),
            'theme' =>array(
                'fields' => array(
                    'locale' => array( 'value'=>''),
                    'template' => array( 'value'=>''),
                    'template_ua_regexp' => array('__empty'=>''),
                    'skin' => array( 'value'=>''),
                    'skin_ua_regexp' => array('__empty'=>''),
                    'layout' => array( 'value'=>''),
                    'layout_ua_regexp' => array('__empty'=>''),
                    'default' => array( 'value'=>'default'),
                    'default_ua_regexp' => array('__empty'=>'')
                )
            )
        );
        return $data;
    }
}
try{
    $shell = new Mage_Shell_Milandirect_configTheme();
    $shell->run();
}catch (Exception $ex){
    echo $ex->getMessage();
}

