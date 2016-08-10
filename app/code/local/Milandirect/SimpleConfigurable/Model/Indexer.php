<?php

/**
 * Milandirect
 *
 * @category  Milandirect
 * @package   Milandirect_SimpleConfigurable
 * @copyright 2016 Balance Internet
 */
class Milandirect_SimpleConfigurable_Model_Indexer extends Mage_Index_Model_Indexer_Abstract
{
    /**
     * Data key for matching result to be saved in
     */
    const EVENT_MATCH_RESULT_KEY = 'simpleconfigurable';

    /**
     * @var array
     */
    protected $_matchedEntities = array(
        Mage_Catalog_Model_Product::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE,
            Mage_Index_Model_Event::TYPE_MASS_ACTION,
        )
    );

    /**
     * @var array
     */
    protected $_defaultRateRequest = array();

    /**
     * @var array
     */
    protected $_rateRequest = array();

    /**
     * @var array
     */
    protected $_priceFormat = array();

    /**
     * @var array
     */
    protected $_includeTax = array();

    /**
     * @var array
     */
    protected $_showIncludeTax = true;

    /**
     * @var array
     */
    protected $_showBothPrices = true;

    /**
     * Get default rate request
     * @param int $storeId magento store id
     * @return mixed
     */
    public function getDefaultRateRequest($storeId)
    {
        if (isset($this->_defaultRateRequest[$storeId])) {
            return $this->_defaultRateRequest[$storeId];
        } else {
            return $this->_defaultRateRequest[$storeId] = Mage::getSingleton('tax/calculation')
                ->getDefaultRateRequest();
        }
    }

    /**
     * Get default rate request
     * @param int $storeId magento store id
     * @return mixed
     */
    public function getRateRequest($storeId)
    {
        if (isset($this->_rateRequest[$storeId])) {
            return $this->_rateRequest[$storeId];
        } else {
            return $this->_rateRequest[$storeId] = Mage::getSingleton('tax/calculation')->getRateRequest();
        }
    }

    /**
     * @param int $storeId magento store id
     * @return bool
     */
    public function getIncludeTax($storeId)
    {
        if (isset($this->_includeTax[$storeId])) {
            return $this->_includeTax[$storeId];
        } else {
            return $this->_includeTax[$storeId] = Mage::helper('tax')->priceIncludesTax() ? 'true' : 'false';
        }
    }

    /**
     * @param int $storeId magento store id
     * @return bool
     */
    public function showIncludeTax($storeId)
    {
        if (isset($this->_showIncludeTax[$storeId])) {
            return $this->_showIncludeTax[$storeId];
        } else {
            return $this->_showIncludeTax[$storeId] = Mage::helper('tax')->displayPriceIncludingTax();
        }
    }

    /**
     * @param int $storeId magento store id
     * @return bool
     */
    public function showBothPrices($storeId)
    {
        if (isset($this->showBothPrices[$storeId])) {
            return $this->showBothPrices[$storeId];
        } else {
            return $this->showBothPrices[$storeId] = Mage::helper('tax')->displayBothPrices();
        }
    }

    /**
     * Get price format
     * @param int $storeId magento store id
     * @return sring price format
     */
    public function getPriceFormat($storeId)
    {
        if (isset($this->_priceFormat[$storeId])) {
            return $this->_priceFormat[$storeId];
        } else {
            return $this->_priceFormat[$storeId] = Mage::app()->getLocale()->getJsPriceFormat();
        }
    }

    /**
     * get index name keep default magento
     * @return string
     */
    public function getName()
    {
        return Mage::helper('catalog')->__('Update configurable price/stock');
    }

    /**
     * get description
     * @return string
     */
    public function getDescription()
    {
        return Mage::helper('catalog')->__('Update configurable price/stock data display on frontend');
    }

    /**
     * Register event when product update
     * @param Mage_Index_Model_Event $event event update save product
     * @return void
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, true);
        $entity = $event->getEntity();
        $dataObj = $event->getDataObject();

        if ($entity == Mage_Catalog_Model_Product::ENTITY) {
            if ($event->getType() == Mage_Index_Model_Event::TYPE_SAVE) {
                $event->addNewData('simpleconfigurable_update_product_id', $dataObj->getId());
            } elseif ($event->getType() == Mage_Index_Model_Event::TYPE_DELETE) {
                $event->addNewData('simpleconfigurable_delete_product_id', $dataObj->getId());
            } elseif ($event->getType() == Mage_Index_Model_Event::TYPE_MASS_ACTION) {
                $event->addNewData('simpleconfigurable_mass_action_product_ids', $dataObj->getProductIds());
            }
        }
    }

    /**
     * @param Mage_Index_Model_Event $event even update delete product
     * @return $this
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        // Temporary skip this allow save product faster
        return $this;
    }

    /**
     * Get product Json config for price
     * @param object $product product
     * @return json
     */
    public function getJsonConfig($product)
    {
        $config = array();
        if (!$product->getTypeInstance(true)->hasOptions($product)) {
            return Mage::helper('core')->jsonEncode($config);
        }

        $_request = $this->getDefaultRateRequest($product->getStoreId());
        /* @var $product Mage_Catalog_Model_Product */
        $_request->setProductClassId($product->getTaxClassId());
        $defaultTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_request = $this->getRateRequest($product->getStoreId());
        $_request->setProductClassId($product->getTaxClassId());
        $currentTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_regularPrice = $product->getPrice();
        $_finalPrice = $product->getFinalPrice();

        $_priceInclTax = Mage::helper('tax')->getPrice($product, $_finalPrice, true);
        $_priceExclTax = Mage::helper('tax')->getPrice($product, $_finalPrice);

        $_tierPrices = array();
        $_tierPricesInclTax = array();
        foreach ($product->getTierPrice() as $tierPrice) {
            $_tierPrices[] = Mage::helper('core')->currency(
                Mage::helper('tax')->getPrice($product, (float)$tierPrice['website_price'], false) - $_priceExclTax
                , false, false);
            $_tierPricesInclTax[] = Mage::helper('core')->currency(
                Mage::helper('tax')->getPrice($product, (float)$tierPrice['website_price'], true) - $_priceInclTax
                , false, false);
        }
        $showIncludeTax = true;
        if (Mage::getStoreConfig('tax/display/type') == 1) {
            $showIncludeTax = false;
        }
        $config = array(
            'productId'           => $product->getId(),
            'priceFormat'         => $this->getPriceFormat($product->getStoreId()),
            'includeTax'          => $this->getIncludeTax($product->getStoreId()),
            'showIncludeTax'      => $showIncludeTax,
            'showBothPrices'      => $this->showBothPrices($product->getStoreId()),
            'productPrice'        => Mage::helper('core')->currency($_finalPrice, false, false),
            'productOldPrice'     => Mage::helper('core')->currency($_regularPrice, false, false),
            'priceInclTax'        => Mage::helper('core')->currency($_priceInclTax, false, false),
            'priceExclTax'        => Mage::helper('core')->currency($_priceExclTax, false, false),
            /**
             * @var skipCalculate
             * @deprecated after 1.5.1.0
             */
            'skipCalculate'       => ($_priceExclTax != $_priceInclTax ? 0 : 1),
            'defaultTax'          => $defaultTax,
            'currentTax'          => $currentTax,
            'idSuffix'            => '_clone',
            'oldPlusDisposition'  => 0,
            'plusDisposition'     => 0,
            'plusDispositionTax'  => 0,
            'oldMinusDisposition' => 0,
            'minusDisposition'    => 0,
            'tierPrices'          => $_tierPrices,
            'tierPricesInclTax'   => $_tierPricesInclTax,
        );

        $responseObject = new Varien_Object();
        Mage::dispatchEvent('catalog_product_view_config', array('response_object' => $responseObject));
        if (is_array($responseObject->getAdditionalOptions())) {
            foreach ($responseObject->getAdditionalOptions() as $option => $value) {
                $config[$option] = $value;
            }
        }

        return Mage::helper('core')->jsonEncode($config);
    }

    /**
     * Process update all configurable
     * @return void
     */
    public function reindexAll()
    {
        ini_set('memory_limit', '-1');
        $websites = Mage::app()->getWebsites();
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');
        $tableUpdate = $resource->getTableName('simpleconfigurable/configurable');

        // create temporary table
        $sqlCreateTmp = 'CREATE TABLE IF NOT EXISTS '.$tableUpdate.'_tmp LIKE '.$tableUpdate;
        $writeConnection->query($sqlCreateTmp);

        // truncate data
        $sqlTruncate = 'TRUNCATE '.$tableUpdate.'_tmp';
        $writeConnection->query($sqlTruncate);


        // end truncate data
        $error = false;
        Mage::app('admin');
        $appEmulation = Mage::getSingleton('core/app_emulation');
        foreach ($websites as $_website) {
            foreach ($_website->getStores() as $_store) {
                $products = Mage::getModel('catalog/product')->getCollection()
                    ->setStoreId($_store->getId())
                    ->addStoreFilter($_store->getId())
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('type_id', 'configurable');
                foreach ($products as $product) {
                    try {
                        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($_store->getId());
                        Mage::unregister('product');
                        Mage::register('product', $product);

                        Mage::getDesign()->setArea(Mage_Core_Model_App_Area::AREA_FRONTEND)
                            ->setStore($_store->getId());
                        $layout = Mage::app()->getLayout();

                        $optionPrice = $this->getJsonConfig($product);

                        Mage::unregister('idx_product');
                        Mage::register('idx_product', 1);

                        $blockConfigurable = $layout->createBlock('catalog/product_view_type_configurable');
                        $blockConfigurable->setTemplate('catalog/product/view/type/configurable_listing.phtml');
                        $optionHtml = $blockConfigurable->toHtml();
                        if ($optionHtml == '' || $optionHtml == ' ' || strlen($optionHtml) == 1) {
                            $blockConfigurable->getJsonConfig();
                        }

                        Mage::unregister('idx_product');

                        $stockData = Mage::registry('idx_stock');
                        $prices = Mage::registry('idx_prices');
                        $originalPrices = Mage::registry('idx_org_prices');

                        // use default configurable value if total child allow is null
                        if (!count($prices)) {
                            if ($product->getPrice() != false) {
                                $prices[] = $product->getFinalPrice();
                                $originalPrices[] = $product->getPrice();
                            } else {
                                $prices[] =  $product->getData('price');
                                $originalPrices[] = $product->getData('price');
                            }
                        }
                        $arrayUpdate = array(
                            $product->getId(),
                            $_store->getId(),
                            '\''.min($originalPrices).'\'',
                            '\''.max($originalPrices).'\'',
                            '\''.min($prices).'\'',
                            '\''.max($prices).'\'',
                            '\''.base64_encode(json_encode($stockData)).'\'',
                            '\''.base64_encode($optionPrice).'\'',
                            '\''.base64_encode($optionHtml).'\''
                        );

                        $sqlUpdate = 'INSERT INTO '.$tableUpdate.'_tmp'.
                            '(
                            `entity_id`,
                            `store_id`,
                            `price_from`,
                            `price_to`,
                            `final_price_from`,
                            `final_price_to`,
                            `stock`,
                            `option_price`,
                            `option_html`
                            )
                            VALUES('.implode(',', $arrayUpdate).')';
                        $writeConnection->query($sqlUpdate);
                        Mage::unregister('product');
                        $product->clearInstance();
                        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                    } catch (Exception $e) {
                        $error = true;
                        Mage::logException($e);
                    }
                }
                //
            }
        }
        if ($error == false) {
            // rename table
            // drop old table
            $sqlDrop = 'DROP TABLE IF EXISTS `'.$tableUpdate.'_old`'; ///Library/WebServer/Documents/milandirect-upgrade/src/app/code/local/Milandirect/SimpleConfigurable/Model/Indexer.php
            $writeConnection->query($sqlDrop);

            // rename table base table to old
            $sqlRename = 'RENAME TABLE `'.$tableUpdate.'` TO `'.$tableUpdate.'_old`';
            $writeConnection->query($sqlRename);

            // rename table tmp to base table
            $sqlRename = 'RENAME TABLE `'.$tableUpdate.'_tmp` TO `'.$tableUpdate.'`';
            $writeConnection->query($sqlRename);
        }
        // reindex all data
    }
}
