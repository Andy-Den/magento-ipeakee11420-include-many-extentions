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


class AW_Onsale_Model_Rule_Condition_Product extends Mage_CatalogRule_Model_Rule_Condition_Product
{
    public function validate(Varien_Object $object)
    {
        if ('qty' == $this->getAttribute()) {
            return $this->validateAttribute($object->getData($this->getAttribute()));
        }
        if (!method_exists($this, '_getAttributeValue')
            && !$object->getData($this->getAttribute())
            && (!is_null($this->_entityAttributeValues))
            && array_key_exists($object->getId(), $this->_entityAttributeValues)
        ) {
            //set default value
            $value = $this->_entityAttributeValues[$object->getId()][0];
            if (array_key_exists($object->getStoreId(), $this->_entityAttributeValues[$object->getId()])) {

                //set store value
                $value = $this->_entityAttributeValues[$object->getId()][$object->getStoreId()];
            }
            $object->setData($this->getAttribute(), $value);

            //unset values - because parent method ignore product storeId
            unset($this->_entityAttributeValues[$object->getId()]);
        }
        return parent::validate($object);
    }

    protected function _addSpecialAttributes(array &$attributes)
    {
        parent::_addSpecialAttributes($attributes);
        $attributes['qty'] = Mage::helper('onsale')->__('Qty');
    }

    public function getInputType()
    {
        if ($this->getAttribute()==='qty') {
            return 'numeric';
        }
        return parent::getInputType();
    }

    public function collectValidatedAttributes($productCollection)
    {
        $attribute = $this->getAttribute();
        if ('qty' == $attribute) {
            $productCollection
                ->getSelect()
                ->joinLeft(
                    array('cisi' => $productCollection->getTable('cataloginventory/stock_item')),
                    'cisi.product_id = e.entity_id',
                    array('qty' => 'cisi.qty')
                )
            ;
            return $this;
        }
        return parent::collectValidatedAttributes($productCollection);
    }
}