<?php
/**
 * Created by PhpStorm.
 * Author: nick
 * Date: 22/10/14
 * Time: 3:30 PM
 */

require_once(dirname(__FILE__).'/../app/Mage.php');
Mage::app('admin');

$gen = Mage::getModel('australia/myshopping_cron');
$gen->update();
