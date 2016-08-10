<?php
/**
 * Tracking Result Block
 *
 * @category   Exceedz
 * @package    Exceedz_Tracking
 */
class Exceedz_Tracking_Block_Result extends Mage_Core_Block_Template
{
    public function getTrackingInfo()
    {
		$orderId = $this->getRequest()->getParam('orderid');
		$postcode = $this->getRequest()->getParam('postcode');
		return Mage::getModel('exceedz_tracking/tracking')->getShipmentInfo($orderId, $postcode);
    }
}