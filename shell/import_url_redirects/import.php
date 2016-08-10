<?php

require_once __DIR__.'/../abstract.php';
require_once __DIR__.'/../../app/Mage.php';
umask(0);
//Mage::app('admin');

if (isset($_SERVER['MAGE_IS_DEVELOPER_MODE'])) {
    Mage::setIsDeveloperMode(true);
}
ini_set('memory_limit','2048M');
ini_set('display_errors', 1);
set_time_limit(0);

class Balance_Shell_ImportUrlRedirects extends Mage_Shell_Abstract
{

    protected $_existedRedirects = array();

    public function run()
    {
        $this->_loadExistedRedirects();
        $this->_importAu();
        $this->_importUk();
    }

    protected function _loadExistedRedirects()
    {
        $readDb = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $readDb->select();
        $select->from('enterprise_url_rewrite_redirect', array('redirect_id', 'identifier', 'store_id'));
        $rows = $readDb->fetchAll($select);
        foreach ($rows as $r) {
            $this->_existedRedirects[$r['store_id']][$r['identifier']] = $r['redirect_id'];
        }
    }

    /**
     *
     */
    protected function _importAu()
    {
        $redirectsData = $this->_getData(file_get_contents(__DIR__.'/data/au_redirects.txt'), 2, 'www.milandirect.com.au');
//        $redirectsData = $this->_getData(file_get_contents(__DIR__.'/data/test.txt'), 2, 'www.milandirect.com.au');
        $this->_importRedirects($redirectsData);
    }

    protected function _importUk()
    {
        $redirectsData = $this->_getData(file_get_contents(__DIR__.'/data/uk_redirects.txt'), 3, 'www.milandirect.co.uk');
        $this->_importRedirects($redirectsData);
    }

    /**
     * Get the data for Magento redirect model.
     *
     * @param $file
     * @param $storeId
     * @param $domain
     * @return array
     */
    protected function _getData($file, $storeId, $domain)
    {
        $auRedirects = explode(PHP_EOL, trim($file, PHP_EOL));
        $redirectsData = array();
        foreach ($auRedirects as $r) {
            $pair = explode('=>', $r);
            $targetPath = ltrim(str_replace($domain, '', trim($pair[1])), '/');
            // Do not redirect to home page at the moment.
            if (empty($targetPath)) {
                continue;
            }
//            $targetPath = !empty($targetPath) ? $targetPath : '/';
            $identifier = ltrim(str_replace($domain, '', trim($pair[0])), '/');
            $redirectsData[$identifier] = array(
                'identifier' => $identifier,
                'target_path' => $targetPath,
                'options' => 'RP',
                'store_id' => $storeId
            );
        }
        return $redirectsData;
    }

    /**
     * @param $fromToRedirects
     */
    protected function _importRedirects($fromToRedirects)
    {
        /**
         * @var $redirectModel Enterprise_UrlRewrite_Model_Redirect
         */
        $redirectModel = Mage::getSingleton('enterprise_urlrewrite/redirect');
        $i = 0;
        foreach ($fromToRedirects as $r) {
            $redirectModel->unsetData();
            $storeId = $r['store_id'];
            if (array_key_exists($r['identifier'], $this->_existedRedirects[$storeId])) {
                $r['redirect_id'] = $this->_existedRedirects[$storeId][$r['identifier']];
            }
            $r['identifier'] = trim($r['identifier'], '/');
            $r['target_path'] = trim($r['identifier'], '/');
            $redirectModel->setData($r);
            print_r($redirectModel->getData());
            $redirectModel->save();
            $i++;
            echo sprintf('Saved %s.'.PHP_EOL, $i);
        }
    }
}

try {
    $shell = new Balance_Shell_ImportUrlRedirects();
    $shell->run();
} catch (Exception $e) {
    echo $e->getMessage().PHP_EOL;
}


