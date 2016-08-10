<?php

// Handle maintenance mode.
$maintenanceFlag = __DIR__.'/maintenance.flag';
if (file_exists($maintenanceFlag)) {
    require_once "Handler.php";
    $options = require_once $maintenanceFlag;
    $handler = new Maintenance\Handler($options);
    // Get the remote ip address.
    $remoteIp = $_SERVER['REMOTE_ADDR'];
    if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) &&
        !empty($_SERVER['HTTP_X_FORWARDED_FOR'])
    ) {
        $remoteIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    $handler->setIp($remoteIp);
    if (!empty($_SERVER['MAGE_RUN_CODE'])) {
        $handler->setStore($_SERVER['MAGE_RUN_CODE']);
    }
    if ($handler->isOn()) {
        include_once dirname(dirname(__DIR__)) . '/errors/503.php';
        exit;
    }
}