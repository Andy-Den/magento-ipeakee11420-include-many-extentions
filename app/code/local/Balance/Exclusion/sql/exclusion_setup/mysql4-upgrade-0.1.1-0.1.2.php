<?php

$installer = $this;
$installer->startSetup();
$installer->run(
    "
 ALTER TABLE {$installer->getTable('newsletter_subscriber')}
 ADD
 (`subscribe_firstname` TEXT NULL,
 `subscribe_lastname` TEXT NULL)
 "
);

$installer->endSetup();
