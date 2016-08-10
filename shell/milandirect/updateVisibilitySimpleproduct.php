<?php

/**
 * Milandirect shell script php change visibility confiburable children product
 *
 * @category  Milandirect
 * @package   Milandirect_Adminhtml
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */
require_once '../abstract.php';
class Mage_Shell_Milandirect_UpdateVisibility extends Mage_Shell_Abstract
{
    public function run()
    {
        $products = Mage::getModel('catalog/product')->getCollection();
        $products->addAttributeToSelect('*')->addAttributeToFilter('type_id', 'configurable');
        foreach ($products as $product) {
            $childs = Mage::getModel('catalog/product_type_configurable')
                ->getUsedProducts(null, $product);
            foreach ($childs as $child) {
                try {
                    $child->setVisibility(1);
                    $child->getResource()->saveAttribute($child, 'visibility');
                    Mage::log('Updated:'. $child->getId(), null, 'updatedVisibility.log');
                } catch (Exception $e) {
                    Mage::log('Can not update: '.$child->getId(), null, 'updatedVisibility.log');
                }
            }
        }
        echo "Done, check file /var/log/updatedVisibility.log\n";
    }
}

$shell = new Mage_Shell_Milandirect_UpdateVisibility();
$shell->run();
