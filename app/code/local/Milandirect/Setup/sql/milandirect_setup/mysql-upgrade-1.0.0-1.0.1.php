<?php
$installer = $this;
$installer->startSetup();

for($i=0; $i< 5; $i++ ) {
	$installer->removeAttribute('catalog_product', 'custom_label_'. $i);
}

$installer->endSetup();