<?php
class Exceedz_ShippingFilter_Helper_Shipping extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    /**
     * Retrieve available shipping carriers for store, taking conigured customer group shipping carriers into account.
     *
     * @param   mixed $store
     * @param   boolean $quote
     * @return  array
     */
    public function getShippingCarriers($store=null, $quote=null)
    {

	$configobj = Mage::getSingleton('shipping/config');
	$carriers  = $configobj->getActiveCarriers($store);

		  $ink = 0;
		  $res_carriers = array();
		  if (is_array($carriers)) {
               $res_carriers[$ink] = array("value"=> '', "label"=> 'No shipping method required');
               $ink++;
			   foreach ($carriers as $carrierCode=>$carrierConfig) {
					$carrername = parent::getCarrierName($carrierCode);
					$res_carriers[$ink] = array("value"=> $carrierCode, "label"=>$carrername);
					$ink++;
			   }
			}
		$carriers =$res_carriers;
    	return $carriers;
    }
}