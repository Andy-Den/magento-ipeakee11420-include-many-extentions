<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Onsale
 * @version    2.5.4
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Onsale_Model_Rule extends Mage_Rule_Model_Rule
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'onsale_rule';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getRule() in this case
     *
     * @var string
     */
    protected $_eventObject = 'rule';

    /**
     * Store matched product Ids
     *
     * @var array
     */
    protected $_productIds;

    /**
     * Limitation for products collection
     *
     * @var int|array|null
     */
    protected $_productsFilter = null;


    /**
     * Init resource model and id field
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_init('onsale/rule');
        $this->setIdFieldName('rule_id');
    }

    /**
     * Object after load processing. Implemented as public interface for supporting objects after load in collections
     *
     * @return Mage_Core_Model_Abstract
     */
    public function afterLoad()
    {
        $this->getResource()->afterLoad($this);
        $this->_afterLoad();
        return $this;
    }

    /**
     * Getter for rule conditions collection
     *
     * @return AW_Onsale_Model_Rule_Condition_Combine
     */
    public function getConditionsInstance()
    {
        return Mage::getModel('onsale/rule_condition_combine');
    }

    /**
     * Getter for rule actions collection
     *
     * @return Mage_Rule_Model_Action_Collection
     */
    public function getActionsInstance()
    {
        return parent::getActionsInstance();
    }

    /**
     * Get array of product ids which are matched by rule
     *
     * @return array
     */
    public function getMatchingProductIds()
    {
        if (is_null($this->_productIds)) {
            $this->_productIds = array();
            $this->setCollectedAttributes(array());

            $storeCollection = Mage::getModel('core/store')->getCollection();
            foreach ($storeCollection as $storeModel) {
                $_storeIds = $this->getStoreIds();
                if (is_string($_storeIds)) {
                    $_storeIds = @explode(',', $this->getStoreIds());
                }
                if (null === $_storeIds || !is_array($_storeIds)) {
                    $_storeIds = array();
                }
                if ((!in_array($storeModel->getId(), $_storeIds)
                    && !in_array(0, $_storeIds)) == TRUE
                ) {
                    continue;
                }
                $productCollection = Mage::getResourceModel('catalog/product_collection');
                $productCollection->addStoreFilter($storeModel);
                if ($this->_productsFilter) {
                    $productCollection->addIdFilter($this->_productsFilter);
                }
                $this->getConditions()->collectValidatedAttributes($productCollection);
                Mage::getSingleton('core/resource_iterator')->walk(
                    $productCollection->getSelect(),
                    array(array($this, 'callbackValidateProduct')),
                    array(
                        'attributes' => $this->getCollectedAttributes(),
                        'product'    => Mage::getModel('catalog/product'),
                        'store_id'   => $storeModel->getId()
                    )
                );
            }
        }
        return $this->_productIds;
    }

    /**
     * Callback function for product matching
     *
     * @param $args
     *
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);
        $product->setData('store_id', $args['store_id']);
        if ($this->getConditions()->validate($product)) {
            $this->_productIds[$args['store_id']][] = $product->getId();
        }
    }

    /**
     * Apply rule to product
     * @param Mage_Catalog_Model_Product $product
     *
     * @return $this
     */
    public function applyToProduct($product)
    {
        if (is_numeric($product)) {
            $product = Mage::getModel('catalog/product')->load($product);
        }
        $this->getResource()->applyToProduct($this, $product);
        return $this;
    }

    /**
     * Apply all price rules, invalidate related cache and refresh price index
     *
     * @return AW_Onsale_Model_Rule
     */
    public function applyAll()
    {
        $this->getResourceCollection()->walk(array($this->_getResource(), 'updateRuleProductData'));
    }

    /**
     * Filtering products that must be checked for matching with rule
     *
     * @param  int|array $productIds
     */
    public function setProductsFilter($productIds)
    {
        $this->_productsFilter = $productIds;
    }

    /**
     * Returns products filter
     *
     * @return array|int|null
     */
    public function getProductsFilter()
    {
        return $this->_productsFilter;
    }
}