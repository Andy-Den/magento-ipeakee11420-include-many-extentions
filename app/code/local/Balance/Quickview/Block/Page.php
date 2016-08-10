<?php

/**
 * Balance_Quickview extension
 * 
 * @category  Balance
 * @package   Balance_Quickview
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */
class Balance_Quickview_Block_Page extends Mage_Core_Block_Template
{
	/**
	 * initialize
	 * @return void
	 */
 	public function __construct(){
		parent::__construct();
	}
	/**
	 * prepare the layout
	 * @return void
	 */
	protected function _prepareLayout(){
		parent::_prepareLayout();
		
	}
	/**
	 * get the pager html
	 * @return string
	 */
	public function getPagerHtml(){
		return $this->getChildHtml('pager');
	}
}