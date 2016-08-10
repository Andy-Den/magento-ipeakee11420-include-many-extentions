<?php

class Magestore_Megamenu_Model_Status extends Varien_Object
{
    const STATUS_ENABLED	= 1;
    const STATUS_DISABLED	= 2;

    static public function getOptionArray(){
        return array(
            self::STATUS_ENABLED	=> Mage::helper('megamenu')->__('Enabled'),
            self::STATUS_DISABLED   => Mage::helper('megamenu')->__('Disabled')
        );
    }

    static public function getOptionHash(){
        $options = array();
        foreach (self::getOptionArray() as $value => $label)
            $options[] = array(
                'value'	=> $value,
                'label'	=> $label
            );
        return $options;
    }
   
    static public function getStyleShow(){
        /*$arr[] = array('value' => 0, 'label' => Mage::helper('megamenu')->__('Name only')); 
        $arr[] = array('value' => 1, 'label' => Mage::helper('megamenu')->__('Content'));
        $arr[] = array('value' => 2, 'label' => Mage::helper('megamenu')->__('Products'));
        $arr[] = array('value' => 3, 'label' => Mage::helper('megamenu')->__('Products & Content'));
        $arr[] = array('value' => 4, 'label' => Mage::helper('megamenu')->__('Categories'));*/
        $arr[] = array('value' => 1, 'label' => Mage::helper('megamenu')->__('Content only')); 
        $arr[] = array('value' => 2, 'label' => Mage::helper('megamenu')->__('Product Listing'));
        $arr[] = array('value' => 3, 'label' => Mage::helper('megamenu')->__('Category Listing'));
        $arr[] = array('value' => 4, 'label' => Mage::helper('megamenu')->__('Product & Category Listing'));
        $arr[] = array('value' => 5, 'label' => Mage::helper('megamenu')->__('Submit Form'));
        return $arr;
    }
}