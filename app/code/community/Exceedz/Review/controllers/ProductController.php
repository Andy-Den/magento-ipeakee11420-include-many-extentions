<?php
/**
 * Override Review controller
 *
 * @category   Exceedz
 * @package    Exceedz_Review
 */
require_once('Mage/Review/controllers/ProductController.php');
class Exceedz_Review_ProductController extends Mage_Review_ProductController
{
	function addAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}
}