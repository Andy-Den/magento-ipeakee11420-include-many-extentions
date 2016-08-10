<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml tax rate controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Nick_Trackingimport_Adminhtml_Sintax_ImportController extends Mage_Adminhtml_Controller_Action
{

	private $_processedOrders = array();
    /**
     * Show Main Grid
     *
     */
      protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/trackingimport')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Sales'), Mage::helper('adminhtml')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Import Tacking'), Mage::helper('adminhtml')->__('Import Tracking'))
        ;
        return $this;
    }

    /**
     * Overview page
     */
   public function indexAction()
    {
         $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('trackingimport/import'))
            ->renderLayout();
    }


	public function exportorderPostAction()
    {
        /** start csv content and set template */
        $headers = new Varien_Object(array(
            'orderid'         => Mage::helper('trackingimport')->__('Order Id'),
            'trackingno' => Mage::helper('trackingimport')->__('Tracking No'),
            'carrier'  => Mage::helper('trackingimport')->__('Carrier'),
        	'postcode'  => Mage::helper('trackingimport')->__('Postcode'),
        	'dateDispatched'  => Mage::helper('trackingimport')->__('Date dispatched'),
        	'trackingLink'  => Mage::helper('trackingimport')->__('Tracking Link')
        ));
        $template = '"{{orderid}}","{{trackingno}}","{{carrier}}","{{postcode}}","{{dateDispatched}}","{{trackingLink}}"';
        $content = $headers->toString($template);

       $orders = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter("state", Mage_Sales_Model_Order::STATE_PROCESSING)->load();

        $content .= "\n";


		foreach ($orders as $order) {
			$orderStatus = $order->getStatus();
			$content .=  $order->getRealOrderId().", , , , ,"."\n";
		}

        $this->_prepareDownloadResponse('processing_orders.csv', $content);
    }


     public function trackingimportPostAction()
    {
        if ($this->getRequest()->isPost() && !empty($_FILES['import_tracking_file']['tmp_name'])) {
            try {
                $this->_importTracking();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('trackingimport')->__('Tracking was successfully imported'));
            }
            catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        else {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*');
    }



	 protected function _importTracking()
    {

		$fileName   = $_FILES['import_tracking_file']['tmp_name'];
        $csvObject  = new Varien_File_Csv();
		//$csvObject->setDelimiter(Mage::getStoreConfig('trackingimport/general/delimiter'));
		//$csvObject->setEnclosure(Mage::getStoreConfig('trackingimport/general/enclosure'));
        $csvData = $csvObject->getData($fileName);

        /** checks columns */
        $csvFields  = array(
            0   => Mage::getStoreConfig('trackingimport/csvheaders/orderid'),
            1   => Mage::getStoreConfig('trackingimport/csvheaders/shipmentid'),
			2   => Mage::getStoreConfig('trackingimport/csvheaders/carrierid'),
			3   => Mage::getStoreConfig('trackingimport/csvheaders/postcode'),
			4   => Mage::getStoreConfig('trackingimport/csvheaders/date_dispatched'),
			5   => Mage::getStoreConfig('trackingimport/csvheaders/tracking_link')
        );

		foreach ($csvData as $k => $v) {
				$orderId = $v[0];
				$trackingNum = $v[1];
				$carrierTitle = $v[2];
				$postcode = $v[3];
				$dateDispatched = $v[4];
				$trackingLink = $v[5];

			try {
				Mage::getModel('Trackingimport/import')->BeginImport($orderId, $trackingNum, $carrierTitle, $postcode, $dateDispatched, $trackingLink);
			} catch (Mage_Core_Exception $e) {
			    Mage::log("$e->getMessage()");
			   return;
			}
		}
     return;
	}

	private function _traverseShipmentArray($csvData){
		$shipmentIndex = 1;
		$resultData = array();
		foreach($csvData as $key => $data){

			if($key == 0){
				$resultData[] = $data;
				 continue;
			}
			if(array_key_exists($data[0], $this->_processedOrders) && in_array($data[1],$this->_processedOrders[$data[0]]))
				continue;

			$resultData[] = $this->checkinArray($csvData, $key);
			$this->_processedOrders[$data[0]][] = $data[1];
		}
		return $resultData;
	}

	private function checkinArray($csvData, $currentKey){
		$orderId = $csvData[$currentKey][0];
		$trackingNum = $csvData[$currentKey][1];
		$resultArray = array();
		$resultArray[0] = $orderId;
		$resultArray[1] = $trackingNum;
		$resultArray[2] = $csvData[$currentKey][2];
		$resultArray[3][] = trim($csvData[$currentKey][3]);
		$resultArray[4][] = trim($csvData[$currentKey][4]);
		for($index = $currentKey+1; $index < count($csvData); $index++){
			if($csvData[$index][0] == $orderId && $csvData[$index][1] == $trackingNum){
				$resultArray[3][] = trim($csvData[$index][3]);
				$resultArray[4][] = trim($csvData[$index][4]);
			}
		}
		$resultArray[3] = implode(',',$resultArray[3]);
		$resultArray[4] = implode(',',$resultArray[4]);
		return $resultArray;
	}
}

