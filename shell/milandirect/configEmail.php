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
class Mage_Shell_Milandirect_configEmail extends Mage_Shell_Abstract
{
    /**
     * @var array
     */
    protected $_fielsEmail = array(
        'ident_general',
        'ident_sales',
        'ident_support',
        'ident_custom1',
        'ident_custom2'
    );
    protected $_valueEmail = 'dev@balanceinternet.com.au';
    protected $_valueName = 'Milan Direct';
    public function run()
    {

        $section = 'trans_email';
        $website = $this->getArg('website');

        if(!$website){
            $website = 'finishings';
        }
        $name = $this->getArg('name');

        if($name){
            $this->_valueName = $name;
        }
        $email = $this->getArg('email');
        if($email){
            $this->_valueEmail = $email;
        }
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
        echo 'Config Email Success';
    }
    protected function getConfig(){
        $data = array();
        foreach($this->_fielsEmail as $field){
            $data[$field] = array(
                'fields' => array(
                    'name' => array('value'=>$this->_valueName),
                    'email' => array('value'=>$this->_valueEmail)
                )
            );
        }
        return $data;
    }
}
try{
    $shell = new Mage_Shell_Milandirect_configEmail();
    $shell->run();
}catch (Exception $ex){
    echo $ex->getMessage();
}

