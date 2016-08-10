<?php

class Exceedz_ShippingFilter_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * Fetch all configured shipping carriers for the given store (0 = global config scope) as an options array for select widgets
	 *
	 * @param integer $store_id
	 * @return array()
	 */
	public function getShippingCarrierOptions($store_id)
	{
		$carriers = Mage::helper('shipping')->getShippingCarriers($store_id);
		return $carriers;

	}/**
	 * Return the config value for the passed key (current store)
	 *
	 * @param string $key
	 * @return string
	 */
	public function getConfig($key)
	{
		$path = 'checkout/shippingfilter/' . $key;
		return Mage::getStoreConfig($path, Mage::app()->getStore());
	}

	/**
	 * Check if the extension has been disabled in the system configuration
	 *
	 * @return boolean
	 */
	public function moduleActive()
	{
		return ! (bool) $this->getConfig('disable_ext');
	}
    
    /**
     * Check for allow shipping method
     *
     * @param   int $productId
     * @param   string $quote
     * @return  boolean
     */
    public function isItemAllowForShipping($productId, $method)
    {
        $_product = Mage::getModel('catalog/product')->load($productId);
        $productShippingMethods = $_product->getProductShippingMethods();
        $disabledShippingMethods = explode(',', $productShippingMethods);
        
        if(empty($productShippingMethods) && $method == 'tablerate') {
            return true;
        }
        if(empty($productShippingMethods) || (is_array($disabledShippingMethods) && in_array($method, $disabledShippingMethods))) {
            return false;
        }
        
        return true;
    }
}
