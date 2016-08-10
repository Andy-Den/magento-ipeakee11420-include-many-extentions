<?php

$installer = $this;
$installer->startSetup();
$installer->run(
    "
 ALTER TABLE {$installer->getTable('newsletter_subscriber')}
 ADD
 (`lastname` TEXT NULL,
 `firstname` TEXT NULL)
 "
);

$installer->endSetup();
