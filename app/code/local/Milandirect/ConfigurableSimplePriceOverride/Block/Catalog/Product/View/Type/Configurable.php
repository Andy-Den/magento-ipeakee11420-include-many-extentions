<?php

/**
 * Rewrite block to add simple product stock
 *
 * @category  Milandirect
 * @package   Milandirect_ConfigurableSimplePriceOverride
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2016 Balance
 */
class Milandirect_ConfigurableSimplePriceOverride_Block_Catalog_Product_View_Type_Configurable extends Balance_ConfigurableSimplePriceOverride_Block_Catalog_Product_View_Type_Configurable
{
    protected $_arrayProductsStatus = array();
    /**
     * Preparing Json Object to pharse to the Product View Page
     * @return string
     * @see Mage_Catalog_Block_Product_View_Type_Configurable::getJsonConfig()
     */
    public function getJsonConfig()
    {
        $stockHelper = Mage::helper('amstockstatus');
        $childProducts = array();

        if (Mage::registry('idx_product') == 1) {
            $stockData = array();
            $prices = array();
            $originalPrices = array();
        }

        // code base
        $attributes = array();
        $options    = array();
        $store      = $this->getCurrentStore();
        $taxHelper  = Mage::helper('tax');
        // end code base
        $mediaPath = Mage::getBaseDir('media');
        $currentProduct = $this->getProduct();

        $preconfiguredFlag = $currentProduct->hasPreconfiguredValues();
        if ($preconfiguredFlag) {
            $preconfiguredValues = $currentProduct->getPreconfiguredValues();
            $defaultValues       = array();
        }
        //Create the extra price and tier price data/html we need.
        //start foreach list allow products
        foreach ($this->getAllowProducts() as $product) {

            $productId = $product->getId();

            $childProducts[$productId] = array(
                'price' => $this->_registerJsPrice($this->_convertPrice($product->getPrice())),
                'finalPrice' => $this->_registerJsPrice($this->_convertPrice($product->getFinalPrice()))
            );

            if (Mage::getStoreConfig('SCP_options/product_page/change_name')) {
                $childProducts[$productId]['productName'] = $product->getName();
            }
            if (Mage::getStoreConfig('SCP_options/product_page/change_description')) {
                $childProducts[$productId]['description'] = $product->getDescription();
            }
            if (Mage::getStoreConfig('SCP_options/product_page/change_short_description')) {
                $childProducts[$productId]['shortDescription'] = $product->getShortDescription();
            }

            $childBlock = $this->getLayout()->createBlock('catalog/product_view_attributes');
            $childProducts[$productId]['productAttributes'] = $childBlock
                ->setTemplate('catalog/product/view/attributes.phtml')
                ->setProduct($product)
                ->toHtml();


            // if image changing is enabled..
            if (Mage::getStoreConfig('SCP_options/product_page/change_image')) {
                // but dont bother if fancy image changing is enabled
                if (!Mage::getStoreConfig('SCP_options/product_page/change_image_fancy')) {
                    // If image is not placeholder...
                    if ($product->getImage() !== 'no_selection') {
                        $childProducts[$productId]['imageUrl'] = (string) Mage::helper('catalog/image')->init(
                            $product,
                            'image'
                        );
                    }
                }
            }
            $stockStatus = $stockHelper->getCustomStockStatusText($product);
            $stockStatusId = $stockHelper->getCustomStockStatusId($product);

            $stockHtml = '';
            $stockItem = $product->getStockItem();
            $isInStock = 1;
            if (is_object($stockItem)) {
                $isInStock = $stockItem->getIsInStock();
            }
            $childProducts[$productId]['in_stock'] = $isInStock;
            if ($product->isAvailable()
                && $isInStock == 1
                && $product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                $childProducts[$productId]['saleAble'] = 1;
                $this->_arrayProductsStatus[$productId] = 1;
                if ($stockStatusId) {
                    $style = '';
                    $stockIconUrl = $stockHelper->getStatusIconUrl($stockStatusId);
                    if ($stockIconUrl!='') {
                        $style = 'style="background-image:url(\''.$stockIconUrl.'\');"';
                    }
                    $stockHtml .= '<span '.$style.' class="icon-stock icon-stock-'.$stockStatusId.'"></span>';
                    if ($stockStatus) {
                        $stockHtml .= '<span class="stock-status">'.$stockStatus;
                        if ($product->getPreorderCalender()) {
                            $preOrder = date('d/m/Y', strtotime($product->getPreorderCalender()));
                            $stockHtml .= $preOrder;
                        }
                        $stockHtml .= '</span>';
                    }
                }
            } else {
                $childProducts[$productId]['saleAble'] = 0;
                $this->_arrayProductsStatus[$productId] = 0;
                if ($product->getData('hide_default_stock_status')) {
                    if ($stockStatusId) {
                        $style = '';
                        $stockIconUrl = $stockHelper->getStatusIconUrl($stockStatusId);
                        if ($stockIconUrl!='') {
                            $style = 'style="background-image:url(\''.$stockIconUrl.'\');"';
                        }
                        $stockHtml .= '<span '.$style.' class="icon-stock icon-stock-'.$stockStatusId.'"></span>';
                        if ($stockStatus) {
                            $stockHtml .= '<span class="stock-status">'.$stockStatus;
                            if ($product->getPreorderCalender()) {
                                $preOrder = date('d/m/Y', strtotime($product->getPreorderCalender()));
                                $stockHtml .= $preOrder;
                            }
                            $stockHtml .= '</span>';
                        }
                    }
                }
            }
            $productId  = $product->getId();

            // code base
            foreach ($this->getAllowAttributes() as $attribute) {
                $productAttribute   = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue     = $product->getData($productAttribute->getAttributeCode());
                if (!isset($options[$productAttributeId])) {
                    $options[$productAttributeId] = array();
                }

                if (!isset($options[$productAttributeId][$attributeValue])) {
                    $options[$productAttributeId][$attributeValue] = array();
                }
                $options[$productAttributeId][$attributeValue][] = $productId;
            }
            // end code base

            $childProducts[$productId]['stockHtml'] = $stockHtml;
            if (Mage::registry('idx_product') == 1) {
                if ($product->getPrice() != false) {
                    $prices[] =  $product->getFinalPrice();
                    $originalPrices[] = $product->getPrice();
                } else {
                    $prices[] =  $product->getData('price');
                    $originalPrices[] = $product->getData('price');
                }
                $stockData[] = $product->getStockItem()->getData();
                $product->clearInstance();
            }
        }
        // end foreach list allow products
        if (Mage::registry('idx_product') == 1) {
            Mage::unregister('idx_prices');
            Mage::unregister('idx_org_prices');
            Mage::unregister('idx_stock');

            Mage::register('idx_prices', $prices);
            Mage::register('idx_org_prices', $originalPrices);
            Mage::register('idx_stock', $stockData);
        }
        // code base
        $this->_resPrices = array(
            $this->_preparePrice($currentProduct->getFinalPrice())
        );

        foreach ($this->getAllowAttributes() as $attribute) {
            $productAttribute = $attribute->getProductAttribute();
            $attributeId = $productAttribute->getId();
            $info = array(
                'id'        => $productAttribute->getId(),
                'code'      => $productAttribute->getAttributeCode(),
                'label'     => $attribute->getLabel(),
                'options'   => array()
            );

            $optionPrices = array();
            $prices = $attribute->getPrices();
            if (is_array($prices)) {
                foreach ($prices as $value) {
                    if (!$this->_validateAttributeValue($attributeId, $value, $options)) {
                        continue;
                    }
                    $currentProduct->setConfigurablePrice(
                        $this->_preparePrice($value['pricing_value'], $value['is_percent'])
                    );
                    $currentProduct->setParentId(true);
                    Mage::dispatchEvent(
                        'catalog_product_type_configurable_price',
                        array('product' => $currentProduct)
                    );
                    $configurablePrice = $currentProduct->getConfigurablePrice();

                    if (isset($options[$attributeId][$value['value_index']])) {
                        $productsIndex = $options[$attributeId][$value['value_index']];
                    } else {
                        $productsIndex = array();
                    }
                    $notAvaible = 0;
                    foreach ($productsIndex as $productId) {
                        if ($this->_arrayProductsStatus[$productId] == 1) {
                            $notAvaible = 1;
                            break;
                        }
                    }
                    $info['options'][] = array(
                        'id'        => $value['value_index'],
                        'label'     => $value['label'],
                        'price'     => $configurablePrice,
                        'oldPrice'  => $this->_prepareOldPrice($value['pricing_value'], $value['is_percent']),
                        'products'  => $productsIndex,
                        'available' => $notAvaible
                    );
                    $optionPrices[] = $configurablePrice;
                }
            }
            /**
             * Prepare formated values for options choose
             */
            foreach ($optionPrices as $optionPrice) {
                foreach ($optionPrices as $additional) {
                    $this->_preparePrice(abs($additional-$optionPrice));
                }
            }
            if ($this->_validateAttributeInfo($info)) {
                $attributes[$attributeId] = $info;
            }

            // Add attribute default value (if set)
            if ($preconfiguredFlag) {
                $configValue = $preconfiguredValues->getData('super_attribute/' . $attributeId);
                if ($configValue) {
                    $defaultValues[$attributeId] = $configValue;
                }
            }
        }

        $taxCalculation = Mage::getSingleton('tax/calculation');
        if (!$taxCalculation->getCustomer() && Mage::registry('current_customer')) {
            $taxCalculation->setCustomer(Mage::registry('current_customer'));
        }

        $_request = $taxCalculation->getDefaultRateRequest();
        $_request->setProductClassId($currentProduct->getTaxClassId());
        $defaultTax = $taxCalculation->getRate($_request);

        $_request = $taxCalculation->getRateRequest();
        $_request->setProductClassId($currentProduct->getTaxClassId());
        $currentTax = $taxCalculation->getRate($_request);

        $taxConfig = array(
            'includeTax'        => $taxHelper->priceIncludesTax(),
            'showIncludeTax'    => $taxHelper->displayPriceIncludingTax(),
            'showBothPrices'    => $taxHelper->displayBothPrices(),
            'defaultTax'        => $defaultTax,
            'currentTax'        => $currentTax,
            'inclTaxTitle'      => Mage::helper('catalog')->__('Incl. Tax')
        );

        $config = array(
            'attributes'        => $attributes,
            'template'          => str_replace('%s', '#{price}', $store->getCurrentCurrency()->getOutputFormat()),
            'basePrice'         => $this->_registerJsPrice($this->_convertPrice($currentProduct->getFinalPrice())),
            'oldPrice'          => $this->_registerJsPrice($this->_convertPrice($currentProduct->getPrice())),
            'productId'         => $currentProduct->getId(),
            'chooseText'        => Mage::helper('catalog')->__('Choose an Option...'),
            'taxConfig'         => $taxConfig
        );

        if ($preconfiguredFlag && !empty($defaultValues)) {
            $config['defaultValues'] = $defaultValues;
        }

        $config = array_merge($config, $this->_getAdditionalConfig());
        // end code base


        if (is_array($config['attributes'])) {
            foreach ($config['attributes'] as $attributeID => &$info) {
                if (is_array($info['options'])) {
                    foreach ($info['options'] as &$option) {
                        unset($option['price']);
                    }
                    unset($option); //clear foreach var ref
                }
            }
            unset($info); //clear foreach var ref
        }

        $p = $this->getProduct();
        $moduleStatus = Mage::helper('configurablesimplepriceoverride')->checkModuelStatus($p);

        $config['childProducts'] = $childProducts;
        if ($p->getMaxPossibleFinalPrice() != $p->getFinalPrice()) {
            $config['priceFromLabel'] = $this->__('Price From:');
        } else {
            $config['priceFromLabel'] = $this->__('');
        }
        $config['ajaxBaseUrl'] = Mage::getUrl('oi/ajax/');
        $config['productName'] = $this->getProduct()->getName();
        $config['description'] = $this->getProduct()->getDescription();
        $config['shortDescription'] = $this->getProduct()->getShortDescription();

        if (Mage::getStoreConfig('SCP_options/product_page/change_image')) {
            $config['imageUrl'] = (string) Mage::helper('catalog/image')->init(
                $this->getProduct(),
                'image'
            );
        }

        $childBlock = $this->getLayout()->createBlock('catalog/product_view_attributes');
        $config['productAttributes'] = $childBlock->setTemplate('catalog/product/view/attributes.phtml')
            ->setProduct($this->getProduct())
            ->toHtml();

        if (Mage::getStoreConfig('SCP_options/product_page/change_image')) {
            if (Mage::getStoreConfig('SCP_options/product_page/change_image_fancy')) {
                $childBlock = $this->getLayout()->createBlock('catalog/product_view_media');
                $config['imageZoomer'] = $childBlock->setTemplate('catalog/product/view/media.phtml')
                    ->setProduct($this->getProduct())
                    ->toHtml();
            }
        }

        if (Mage::getStoreConfig('SCP_options/product_page/show_price_ranges_in_options')) {
            $config['showPriceRangesInOptions'] = true;
            $config['rangeToLabel'] = $this->__('to');
        }
        if (Mage::app()->getRequest()->getRouteName() == 'quickview') {
            $config['containerId'] = 'product_addtocart_form';
        }
        if ($moduleStatus) {
            return Zend_Json::encode($config);
        } else {
            return parent::getJsonConfig();
        }

    }

    /**
     * Get Allowed Products
     *
     * @return array
     */
    public function getAllowProducts()
    {
        if (!$this->hasAllowProducts()) {
            $products = array();
            // $skipSaleableCheck = Mage::helper('catalog/product')->getSkipSaleableCheck();
            $allProducts = $this->getProduct()->getTypeInstance(true)
                ->getUsedProducts(null, $this->getProduct());
            foreach ($allProducts as $product) {
                $products[] = $product;
            }
            $this->setAllowProducts($products);
        }
        return $this->getData('allow_products');
    }

    /**
     * Convert product id to string
     * @param string $productId product id
     * @return string
     */
    public function convertIdToString($productId)
    {
        $baseArray = array(
            0 => 'a',
            1 => 'b',
            2 => 'c',
            3 => 'd',
            4 => 'e',
            5 => 'f',
            6 => 'g',
            7 => 'h',
            8 => 'i',
            9 => 'j',
            10 => 'k'
        );
        $stringId = '';
        $arrayProductId = str_split($productId);
        foreach ($arrayProductId as $id) {
            $stringId .= $baseArray[$id];
        }
        return $stringId;
    }
}
