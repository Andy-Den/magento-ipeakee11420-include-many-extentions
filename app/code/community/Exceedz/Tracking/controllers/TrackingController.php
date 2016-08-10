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
		$model  = Mage::getModel('exceedz_tracking/tracking');        $postData = $model->courierRedirection();
    }

}
