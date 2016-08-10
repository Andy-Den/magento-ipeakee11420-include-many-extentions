<?php

/**
 *
 * @category  Milandirect
 * @package   Milandirect_ConfigurableSwatches
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2016 Balance
 */
class Milandirect_ConfigurableSwatches_Helper_Mediafallback extends Mage_ConfigurableSwatches_Helper_Mediafallback
{

    /**
     * Set child_attribute_label_mapping on products with attribute label -> product mapping
     * Depends on following product data:
     * - product must have children products attached
     *
     * @param array $parentProducts
     * @param $storeId
     * @return void
     */
    public function attachConfigurableProductChildrenAttributeMapping(array $parentProducts, $storeId)
    {
        $listSwatchAttr = Mage::helper('configurableswatches/productlist')->getSwatchAttribute();

        $parentProductIds = array();
        /* @var $parentProduct Mage_Catalog_Model_Product */
        $addFix = 0;
        foreach ($parentProducts as $parentProduct) {
            $parentProductIds[] = $parentProduct->getId();
            if ($parentProduct->getTypeId() == 'configurable') {
                $addFix = 1;
            }
        }

        $configAttributes = Mage::getResourceModel('configurableswatches/catalog_product_attribute_super_collection')
            ->addParentProductsFilter($parentProductIds)
            ->attachEavAttributes()
            ->setStoreId($storeId)
        ;
        $addColor = 1;
        if ($addFix == 1) {
            $attributeSwatch = Mage::getStoreConfig('configswatches/general/product_list_attribute');
            foreach ($configAttributes as $attribute) {
                if ($attribute->getAttributeId() == $attributeSwatch) {
                    $addColor = 0;
                    break;
                }
            }
        }
        if ($addColor == 1) {
            $colorAttribute = Mage::getResourceModel('configurableswatches/catalog_product_attribute_super_collection')
                ->attachEavAttributes()
                ->setStoreId($storeId);
            $attributeSwatch = Mage::getStoreConfig('configswatches/general/product_list_attribute');
            $colorAttribute->getSelect()->where('`main_table`.`attribute_id` = '.$attributeSwatch);
            $colorAttribute = $colorAttribute->getFirstItem();
            $configAttributes->addItem($colorAttribute);
        }


        $optionLabels = array();
        foreach ($configAttributes as $attribute) {
            $optionLabels += $attribute->getOptionLabels();
        }

        foreach ($parentProducts as $parentProduct) {
            $mapping = array();
            $listSwatchValues = array();

            /* @var $attribute Mage_Catalog_Model_Product_Type_Configurable_Attribute */
            foreach ($configAttributes as $attribute) {
                /* @var $childProduct Mage_Catalog_Model_Product */
                if (!is_array($parentProduct->getChildrenProducts())) {
                    continue;
                }

                foreach ($parentProduct->getChildrenProducts() as $childProduct) {

                    // product has no value for attribute, we can't process it
                    if (!$childProduct->hasData($attribute->getAttributeCode())) {
                        continue;
                    }
                    $optionId = $childProduct->getData($attribute->getAttributeCode());

                    // if we don't have a default label, skip it
                    if (!isset($optionLabels[$optionId][0])) {
                        continue;
                    }

                    // normalize to all lower case before we start using them
                    $optionLabels = array_map(function ($value) {
                        return array_map('Mage_ConfigurableSwatches_Helper_Data::normalizeKey', $value);
                    }, $optionLabels);

                    // using default value as key unless store-specific label is present
                    $optionLabel = $optionLabels[$optionId][0];
                    if (isset($optionLabels[$optionId][$storeId])) {
                        $optionLabel = $optionLabels[$optionId][$storeId];
                    }

                    // initialize arrays if not present
                    if (!isset($mapping[$optionLabel])) {
                        $mapping[$optionLabel] = array(
                            'product_ids' => array(),
                        );
                    }
                    $mapping[$optionLabel]['product_ids'][] = $childProduct->getId();
                    $mapping[$optionLabel]['label'] = $optionLabel;
                    $mapping[$optionLabel]['default_label'] = $optionLabels[$optionId][0];
                    $mapping[$optionLabel]['labels'] = $optionLabels[$optionId];

                    if ($attribute->getAttributeId() == $listSwatchAttr->getAttributeId()
                        && !in_array($mapping[$optionLabel]['label'], $listSwatchValues)
                    ) {
                        $listSwatchValues[$optionId] = $mapping[$optionLabel]['label'];
                    }
                } // end looping child products
            } // end looping attributes


            foreach ($mapping as $key => $value) {
                $mapping[$key]['product_ids'] = array_unique($mapping[$key]['product_ids']);
            }

            $parentProduct->setChildAttributeLabelMapping($mapping)
                ->setListSwatchAttrValues($listSwatchValues);
        } // end looping parent products
    }

    /**
     * Attaches children product to each product via
     * ->setChildrenProducts()
     *
     * @param array $products products collection
     * @param int   $storeId  magento store id
     * @return void
     */
    public function attachChildrenProducts(array $products, $storeId)
    {
        $productIds = array();
        /* @var $product Mage_Catalog_Model_Product */
        foreach ($products as $product) {
            $productIds[] = $product->getId();
        }

        $collection = Mage::getResourceModel(
            'configurableswatches/catalog_product_type_configurable_product_collection'
        );

        $collection->setFlag('product_children', true)
            ->addStoreFilter($storeId)
            ->addAttributeToSelect($this->_getChildrenProductsAttributes())
            ->addAttributeToSelect('status');
           // ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $collection->addProductSetFilter($productIds);

        $collection->load();

        $mapping = array();
        /* @var $childProduct Mage_Catalog_Model_Product */
        foreach ($collection as $childProduct) {
            foreach ($childProduct->getParentIds() as $parentId) {
                if (!isset($mapping[$parentId])) {
                    $mapping[$parentId] = array();
                }
                $mapping[$parentId][] = $childProduct;
            }
        }

        foreach ($mapping as $parentId => $childrenProducts) {
            $products[$parentId]->setChildrenProducts($childrenProducts);
        }
    }

    /**
     * filter by status of children product to each product via
     *
     * @param array $_productIds current product
     * @param int $storeId  magento store id
     * @return string status
     */
    public function getChildrenStatus(array $_productIds, $storeId){

        $statusFilter = Mage::getModel('catalog/product')->getCollection()
            ->setStoreId($storeId)
            ->addStoreFilter($storeId)
            ->addFieldToFilter('entity_id', $_productIds)
            ->addAttributeToSelect('status')
            ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        if (!count($statusFilter)){
            return Mage_Catalog_Model_Product_Status::STATUS_DISABLED;
        } else {
            return Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
        }
    }

}
