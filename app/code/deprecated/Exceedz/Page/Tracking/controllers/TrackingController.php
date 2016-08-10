<?php
/**
 * Tracking controller
 *
 * @category   Exceedz
 * @package    Exceedz_Tracking
 */
class Exceedz_Tracking_TrackingController extends Mage_Core_Controller_Front_Action{
	/**
     *Tracking order action     */    public function trackorderAction()    {
		echo $carrierCode = 'tracktrace';		$trackingNumber  = '123456';		$postcode = '2312';		if($carrierCode == 'tracktrace'){			$postUrl = 'https://online.toll.com.au/trackandtrace';		}else if($carrierCode == 'nightfreight'){			$postUrl = 'http://www.nightfreight.co.uk/franke/default.aspx';		}		return $postUrl;
    }

}
