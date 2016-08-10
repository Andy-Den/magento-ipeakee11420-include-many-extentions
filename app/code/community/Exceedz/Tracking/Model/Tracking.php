<?php

/**
 * Tracking Model
 *
 * @category   Exceedz
 * @package    Exceedz_Tracking
 */

class Exceedz_Tracking_Model_Tracking extends Mage_Core_Model_Abstract
{

	public function courierRedirection($orderId)
    {
		$postData = array();

		if($orderId['orderid'] != "" && $orderId['trackid'] != ""){

			$order = Mage::getModel('sales/order')->loadByIncrementId($orderId['orderid']);

			if($order->getBillingAddress() != ""){
				$billingData = $order->getBillingAddress()->getData();
				$postcode = $billingData['postcode'];

				$trackingCollection = Mage::getResourceModel('sales/order_shipment_track_collection')
						->setOrderFilter($order)
						->getData();

        		foreach ($trackingCollection as $tracking){

			 		// This will give me the shipment IncrementId, but not the actual tracking information.
			 		if(in_array($orderId['trackid'],$tracking)){
			 			$trackingNumber = $tracking['track_number'];
			 			$carrierCode = $tracking['carrier_code'];
			 		}

        		}
			}
		}

		//if($carrierCode != 'eparcel'){
			if($carrierCode == 'tracktrace'){
				$postUrl = 'https://online.toll.com.au/trackandtrace';
			}else if($carrierCode == 'bluestar'){
				$postUrl = "http://www.bslots.com/Tracker.asp?cn=".$trackingNumber."&pc=".$postcode."";
			}else if($carrierCode == 'royalmail'){
				$postUrl = "http://track2.royalmail.com/";
			}else if($carrierCode == 'dhl'){
				$postUrl = "http://www.dhl.co.uk/content/gb/en/express/tracking.shtml?brand=DHL&AWB=".$trackingNumber;
			}else if($carrierCode == 'city-link'){
				 $postUrl = "http://www.city-link.co.uk/";
			}else if($carrierCode == 'nightfreight'){
				$postUrl = 'http://www.nightfreight.co.uk/franke/default.aspx';
			}else if($carrierCode == 'eparcel'){
				$postUrl = 'http://auspost.com.au/track/';
			}

		//} else {
				//$auData = $this->aupostData($carrierCode, $trackingNumber);
                //$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//}
		$postData = array( "carrierCode" => $carrierCode,"trackingNumber" => $trackingNumber,"postUrl" => $postUrl,"postcode" => $postcode);
		return $postData;
    }

    public function aupostData($carrierCode, $trackingNumber){
    	$ga_username = 'anonymous@auspost.com.au';
		$ga_password = 'password';
        $url = 'https://devcentre.auspost.com.au/myapi/QueryTracking.xml?q='.$trackingNumber;
		//$postUrl = 'http://auspost.com.au/track/';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, $ga_username . ":" . $ga_password);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIE, 'OBBasicAuth=fromDialog');
        $result = curl_exec($ch);
     	print_r($result);
        exit;
    }

    /*
     * Get Shipment info for order
     * @param $orderId - order increment id
     * @param $postcode - postcode
     * $return array $trackingCollection - containing tracking data.
     */
    public function getShipmentInfo($orderId, $postcode) {

    	$trackingCollection = array();

    	if($orderId != "" && $postcode != ""){

			$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

			if($order->getStoreId() == Mage::app()->getStore()->getStoreId()) {

				$trackingCollection = Mage::getResourceModel('sales/order_shipment_track_collection')
					->setOrderFilter($order)
					->addFilter('postcode', $postcode)
					->getData();

			}

		}

		return $trackingCollection;
    }
}