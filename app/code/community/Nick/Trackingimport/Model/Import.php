<?php

class Nick_Trackingimport_Model_Import
{
    public function BeginImport($orderId, $trackingNum, $carrierTitle, $postcode, $dateDispatched, $trackingLink){

		$includeComment = false;
		$comment = NULL;
		$emailCustomer = Mage::getStoreConfig('trackingimport/general/email');


		$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

		if($order['increment_id'] == "") return ;

		$email = $order->getCustomerEmail();

		if ($order->canUnhold()) {
			$order->unhold();
			$order->save();
		}

		if ($order->canInvoice() && Mage::getStoreConfig('trackingimport/general/invoice') == 1) {

			$invoice = $order->prepareInvoice();
			$invoice->register()->pay();
			Mage::getModel('core/resource_transaction')
				->addObject($invoice)
				->addObject($invoice->getOrder())
				->save();

			$comment = Mage::helper('trackingimport')->__('Invoice #%s created', $invoice->getIncrementId());
			$orderState = Mage_Sales_Model_Order::STATE_PROCESSING;
			$orderStatus = 'processing';
			$order->setState($orderState, $orderStatus, $comment, $emailCustomer);
			$order->setEmailSent(true);
			$order->save();
		}

		$Id = $order->getId();

		$shipments = Mage::getResourceModel('sales/order_shipment_collection')
				->addAttributeToSelect('*')
				->setOrderFilter($Id)
				->load();

		//This converts the order to "Completed".
		if ($order->canShip() && !$shipments->getSize()) {

			$convertor = Mage::getModel('sales/convert_order');
			$shipment = $convertor->toShipment($order);
			$shipment->setIncrementId($orderId);

			foreach ($order->getAllItems() as $orderItem) {

				if (!$orderItem->getQtyToShip()) continue;
				if ($orderItem->getIsVirtual()) continue;

				$item = $convertor->itemToShipmentItem($orderItem);
				$qty = $orderItem->getQtyToShip();
				$item->setQty($qty);
				$shipment->addItem($item);
			}

		} else {

			foreach ($order->getShipmentsCollection() as $shipment) {
				$shipmentId = $shipment->getIncrementId();
			}

			$shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentId);
		}

		$carrier = 'custom';

		if ($carrierTitle == 'ups') {
			$carrier = 'ups';
			$carrierTitle = 'United Parcel Service';
		}

		if ($carrierTitle == 'usps') {
			$carrier = 'usps';
			$carrierTitle = 'United States Parcel Service';
		}

		if ($carrierTitle == 'fedex') {
			$carrier = 'fedex';
			$carrierTitle = 'Federal Express';
		}

		if ($carrierTitle == 'dhl') {
			$carrier = 'dhl';
			$carrierTitle = 'DHL';
		}

		if ($carrierTitle == 'tracktrace') {
			$carrier = 'tracktrace';
			$carrierTitle = 'TrackTrace';
		}

		if ($carrierTitle == 'bluestar') {
			$carrier = 'bluestar';
			$carrierTitle = 'EFM';
		}

		if ($carrierTitle == 'royalmail') {
			$carrier = 'royalmail';
			$carrierTitle = 'Royalmail';
		}

		if ($carrierTitle == 'city-link') {
			$carrier = 'city-link';
			$carrierTitle = 'City Link';
		}

		if ($carrierTitle == 'eparcel') {
			$carrier = 'eparcel';
			$carrierTitle = 'Australia Post eParcel';
		}

		if ($carrierTitle == 'nightfreight') {
			$carrier = 'nightfreight';
			$carrierTitle = 'NightFreight';
		}


		$data = array();
		$data['carrier_code'] = $carrier;
		$data['title'] = $carrierTitle;
		$data['number'] = $trackingNum;
		$data['postcode'] = $postcode;

		$convertedDispatchedDate = '';
		if(!empty($dateDispatched)) {
			list($d, $m, $y) = explode('/', $dateDispatched);

			if(!empty($d) && !empty($m) && !empty($y))
				$convertedDispatchedDate = mktime(0, 0, 0, $m, $d, $y);
			else {
				list($d, $m, $y) = explode('-', $dateDispatched);
				$convertedDispatchedDate = mktime(0, 0, 0, $m, $d, $y);
			}
		}

		$data['date_dispatched'] = strftime('%Y-%m-%d', $convertedDispatchedDate);//exit;
		$data['tracking_link'] = $trackingLink;

		if ($trackingNum != NULL){
			$track = Mage::getModel('sales/order_shipment_track')->addData($data);
			$shipment->addTrack($track);
		}


		if ($order->canShip() && !$shipments->getSize()) {

			$shipment->register();

			$shipment->addComment($comment, $email && $emailCustomer);
			$shipment->setPostcode($postcode);
			$shipment->setDateDispatched($dateDispatched);
			$shipment->setTrackingLink($trackingLink);
			$shipment->setEmailSent(true);
			$shipment->getOrder()->setIsInProcess(true);

			$transactionSave = Mage::getModel('core/resource_transaction')
				->addObject($shipment)
				->addObject($shipment->getOrder())
				->save();

			$shipment->sendEmail($email, ($includeComment ? $comment : ''));

			$shipment->save();
			$order->setStatus(Mage::getStoreConfig('trackingimport/general/importstatus'));
			$order->addStatusToHistory(Mage::getStoreConfig('trackingimport/general/importstatus'), '', $emailCustomer);
			$order->save();

		} else {
			if ($trackingNum != NULL){ $track->save();}

			if (!$order->canShip()){

				$status = Mage::getStoreConfig('trackingimport/general/importstatus');
				$order->setStatus($status);
				$order->addStatusToHistory($status, '', $emailCustomer);
			}
			$order->save();
		}
	}
}