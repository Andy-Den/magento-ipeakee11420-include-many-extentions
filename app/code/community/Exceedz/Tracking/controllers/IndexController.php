<?php
/**
 * Index controller
 *
 * @category   Exceedz
 * @package    Exceedz_Tracking
 */
class Exceedz_Tracking_IndexController extends Mage_Core_Controller_Front_Action{
	/**
     *Tracking order action     */    public function indexAction()    {
		$this->loadLayout();        $this->renderLayout();
    }
}