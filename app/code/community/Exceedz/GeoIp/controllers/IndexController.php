<?php
	/**
	 * Exceedz Solutions
	 * GeoIp Module
	 *
	 * @category   Exceedz
	 * @package    Exceedz_GeoIp
	 */

	class Exceedz_GeoIp_IndexController extends Mage_Core_Controller_Front_Action
	{
		/**
		 * index action
		 *
		 * @return JSON containing response message.
		 */
		public function indexAction()
		{
			$countryName = Mage::helper('geoip')->getCountryName();
			$setStoreUrl = false;
            $isStoreFound = false;
			if (!Mage::getModel('core/store')->isStoreCookieSet() &&
				($countryName == 'Australia' || $countryName == 'United Kingdom') ) {
				$setStoreUrl = true;
			} elseif($this->getRequest()->getParam('store') == 'australia' || $this->getRequest()->getParam('store') == 'united_kingdom') {
                Mage::getModel('core/store')->setStoreCookie(ucwords(str_replace('_',' ', $this->getRequest()->getParam('store'))), true);
                $result['store'] = $this->getRequest()->getParam('store');
                $isStoreFound = true;
            }
			if (Mage::getModel('core/store')->checkStore() || $isStoreFound) {
				$result['message'] = $this->__('Store is found for this country.');
				$storeCodeByCountryName = str_replace(' ','_', strtolower($countryName));
				if($setStoreUrl && $storeCodeByCountryName == Mage::app()->getStore()->getCode()) {
					$result['storeUrl'] = Mage::getModel('core/store')->getStoreUrlByCode($countryName);
				} else if(!$setStoreUrl && $storeCodeByCountryName != Mage::app()->getStore()->getCode() && !Mage::getModel('core/store')->isStoreCookieSet()) {
					$result['message'] = $this->__('Store is not found for this country.');
				}
			} else {
				$result['message'] = $this->__('Store is not found for this country.');
			}

			$this->getResponse()->setBody(Zend_Json::encode($result));
		}

		/**
		 * used for store switching
		 *
		 * @return JSON containing response message if request is for setting store otherwise show
		 * screen to select store .
		 */
		public function storeAction()
		{
			$params = $this->getRequest()->getParams();
			if (isset($params['store'])) {
				Mage::getModel('core/store')->setStoreCookie($params['store'], false);
				$result['message'] = $this->__('Store is set.');
				$this->getResponse()->setBody(Zend_Json::encode($result));
			} else {

				$this->loadLayout();
				// Create the block layout
				$block = $this->getLayout()->createBlock(
					'page/switch',
					'store_switch_block',
					array('template' => 'page/switch/popup_stores.phtml')
				);
				// Set the layout
				$this->getLayout()->getBlock('root')->setTemplate('page/popup.phtml');
				$this->getLayout()->getBlock('content')->append($block);
				$this->renderLayout();
			}
		}

		/**
		 * get action
		 *
		 * @return JSON containing response message.
		 */
		public function getAction()
		{
            $store = Mage::getModel('core/store')->isStoreCookieSet();
			if ($store) {
				$result['message'] = $this->__('Store is set.');
				$baseurl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
				$result['storeUrl'] = Mage::getModel('core/store')->getStoreUrlByCode(ucwords(str_replace('_',' ', $store)), true);
			} else {
				$result['message'] = $this->__('Store is not set.');
			}

			$this->getResponse()->setBody(Zend_Json::encode($result));
		}
	}