<?php
/**
 * @category   Exceedz
 * @package    Exceedz_ShippingFilter
 */

class Exceedz_ShippingFilter_Model_Config_Source_Shipping_Methods
	extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	protected $_options;

	protected $_storeCode = Mage_Core_Model_Store::ADMIN_CODE;

	public function toOptionArray()
	{
		if (!$this->_options)
		{
			$store = Mage::app()->getStore($this->_storeCode);
			$this->_options = Mage::helper('shippingfilter')->getShippingCarrierOptions($store->getId());
		}
		return $this->_options;
	}

	public function getAllOptions()
	{
		return $this->toOptionArray();
	}

	/**
	 * Get a text for option value
	 *
	 * @param string|integer $value
	 * @return string
	 */
	public function getOptionText($value)
	{
		$isMultiple = false;
		if (strpos($value, ','))
		{
			$isMultiple = true;
			$value = explode(',', $value);
		}

		$options = $this->getAllOptions();

		if ($isMultiple)
		{
			$values = array();
			foreach ($options as $item)
			{
				if (in_array($item['value'], $value))
				{
					$values[] = $item['label'];
				}
			}
			return $values;
		}
		else
		{
			foreach ($options as $item)
			{
				if ($item['value'] == $value)
				{
					return $item['label'];
				}
			}
			return false;
		}
	}

	/**
	 * Retrieve Column(s) for Flat Catalog
	 *
	 * @return array
	 */
	public function getFlatColums()
	{
		$columns = array();
		$columns[$this->getAttribute()->getAttributeCode()] = array(
			'type'      => 'varchar(255)',
			'unsigned'  => false,
			'is_null'   => true,
			'default'   => null,
			'extra'     => null
		);

		return $columns;
	}

	/**
	 * Retrieve Indexes for Flat Catalog
	 *
	 * @return array
	 */
	public function getFlatIndexes()
	{
		$indexes = array();

		$index = 'IDX_' . strtoupper($this->getAttribute()->getAttributeCode());
		$indexes[$index] = array(
			'type'      => 'index',
			'fields'    => array($this->getAttribute()->getAttributeCode())
		);

		$sortable   = $this->getAttribute()->getUsedForSortBy();

		return $indexes;
	}

	/**
	 * Retrieve Select For Flat Attribute update
	 *
	 * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
	 * @param int $store
	 * @return Varien_Db_Select|null
	 */
	public function getFlatUpdateSelect($store)
	{
		return Mage::getResourceModel('eav/entity_attribute_option')
			->getFlatUpdateSelect($this->getAttribute(), $store);
	}
}