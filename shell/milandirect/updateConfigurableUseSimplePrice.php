<?php

/**
 * Milandirect shell script php update configurable product use simple product price
 *
 * @category  Milandirect
 * @package   Milandirect_Adminhtml
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2016 Balance
 */
require_once '../abstract.php';
class Mage_Shell_Milandirect_UpdateConfigurableUseSimplePrice extends Mage_Shell_Abstract
{
    public function run()
    {
        $products = Mage::getModel('catalog/product')->getCollection();
        $products->addAttributeToSelect('*')->addAttributeToFilter('type_id', 'configurable');
        foreach ($products as $product) {
            try {
                $product->setScpproductspecific(0);
                $product->getResource()->saveAttribute($product, 'scpproductspecific');
                Mage::log('Updated:'. $product->getId(), null, 'UpdateConfigurableUseSimplePrice.log');
            } catch (Exception $e) {
                Mage::log('Can not update: '.$product->getId(), null, 'UpdateConfigurableUseSimplePrice.log');
            }
        }
        echo "Done, check file /var/log/UpdateConfigurableUseSimplePrice.log\n";
    }
}

$shell = new Mage_Shell_Milandirect_UpdateConfigurableUseSimplePrice();
$shell->run();
