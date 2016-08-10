<?php

$installer = $this;
$installer->startSetup();

$cmsPagesChange = array("about-milan-direct","track-my-order","ikea-ikea-australia","delivery-information","zanui-zanui-furniture","saturday-delivery-milan-direct","gift-registry","competitors","always-open,warranty-information","brand","help-centre","wayfair-wayfair-australia","commercial-orders","vamos","fantastic-furniture-fantastic-furniture-catalogue","bedside-tables","freedom-furniture-freedom-australia","officeworks-officeworks-australia","news-and-media","returns-refunds");

$cmsPages = Mage::getModel('cms/page')->getCollection()->addFieldToFilter('identifier',array('in'=>$cmsPagesChange));
if($cmsPages->getSize()){
    foreach($cmsPages as $row) {
        $cmsDetail = Mage::getModel('cms/page')->load($row->getId());
        $cmsDetail->setCustomTheme("");
        $cmsDetail->save();
    }
}
$installer->endSetup();
