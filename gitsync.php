<?php

require_once('app/Mage.php');
Mage::app('admin');
$request = Mage::app()->getRequest();

if ($request->isPost()) {
    $cryptKey = (string) Mage::getConfig()->getNode('global/crypt/key');
    $postKey = (string) $request->getParam('cryptkey', null);

    if (!$postKey || $cryptKey != $postKey) {
        $output = 'Invalid Key';
//        $this->getResponse()->setBody($output);
        echo $output;
        return;
    }
      $output = shell_exec('sh ' . Mage::getModuleDir('', 'Balance_Deployment') . DS . 'shell' . DS . 'gitsync.sh ' . Mage::getBaseDir('base'));
//    $output = shell_exec('date').$_SERVER['SERVER_ADDR'].' '.Mage::getModuleDir('', 'Balance_Deployment').' '.Mage::getBaseDir('base');
    echo $output;//Mage::app()->getResponse()->setBody($output);
}