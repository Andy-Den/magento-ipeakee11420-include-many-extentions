<?php
/**
 * Shipping carrier table rate grid block
 *
 * @category    Exceedz
 * @package     Exceedz_Adminhtml
 */
class Exceedz_Adminhtml_Block_Shipping_Carrier_Tablerate_Grid extends 
		Mage_Adminhtml_Block_Shipping_Carrier_Tablerate_Grid
{
    /**
     * Prepare table columns
     *
     * @return Exceedz_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('dest_country', array(
            'header'    => Mage::helper('adminhtml')->__('Country'),
            'index'     => 'dest_country',
            'default'   => '*',
        ));

        $this->addColumn('dest_region', array(
            'header'    => Mage::helper('adminhtml')->__('Region/State'),
            'index'     => 'dest_region',
            'default'   => '*',
        ));

        $this->addColumn('dest_zip', array(
            'header'    => Mage::helper('adminhtml')->__('Zip/Postal Code'),
            'index'     => 'dest_zip',
            'default'   => '*',
        ));

        $label = Mage::getSingleton('shipping/carrier_tablerate')
            ->getCode('condition_name_short', $this->getConditionName());
        $this->addColumn('condition_value', array(
            'header'    => $label,
            'index'     => 'condition_value',
        ));
		
		$this->addColumn('weight_rate', array(
            'header'    => Mage::helper('adminhtml')->__('Weight Rate'),
            'index'     => 'weight_rate',
        ));

        $this->addColumn('price', array(
            'header'    => Mage::helper('adminhtml')->__('Shipping Price'),
            'index'     => 'price',
        ));
		
		$this->addColumn('markup', array(
            'header'    => Mage::helper('adminhtml')->__('Markup'),
            'index'     => 'markup',
        ));

        return parent::_prepareColumns();
    }
}