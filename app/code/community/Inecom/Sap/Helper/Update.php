<?php

/**
 * Helper that manages the order updates that are made available through the
 * SAP REST service
 *
 * AS part of the order updates we programatically create shipments against the
 * order at hand. Multiple shipments can be created for each order, each with
 * their individual status.
 * An email should be sent to the customer as soon as a new shipment is
 * dispatched.
 *
 *
 */
class Inecom_Sap_Helper_Update extends Mage_Core_Helper_Abstract
{
    /**
     * FreightItem: Item to be used for freight
     */
    const FREIGHT_ITEM = 'FREIGHT';
    /**
     * Send email when status dispatched
     * -> when shipment item is created
     */
    const SHIPMENT_STATUS_DISPATCHED = 1;
    const SHIPMENT_STATUS_INCOUNTRY = 2;
    const SHIPMENT_STATUS_INNETWORK = 3;
    const SHIPMENT_STATUS_RECEIVED = 4;

    /**
     * Array of order numbers that we request updates for
     * @var array
     */
    protected $orderMumbers;

    /**
     * Queries the SAP REST service for order updates
     *
     * @return mixed XML body or false on failure
     */
    public function getUpdates()
    {
        // Initialise ZF REST client
        $client = Mage::helper('inecom_sap/data')->initRestClient();

        // Fetch relevant orders (increment_id)
        $this->orderMumbers = self::getOrderNumbers();

        // Get message to post to REST service
        $msg = $this->getRequestMessage();

        // Post message to REST service
        $result = $client->restPost(Mage::getStoreConfig('sap/settings/rest_uri_path'), $msg);
        //$this->logger('Checking order updates with the following XML', Zend_Log::ALERT, 'sap_update.log');
        //$this->logger($msg, Zend_log::ALERT, 'sap_update.log');
        // Check if the request was successful
        if (!$result->isSuccessful()) {
            $this->logger($result, Zend_Log::ALERT, 'sap_update.log');
            // Notify admin contact
            Mage::helper('inecom_sap/data')->notify(print_r($result, true), 'SAP Orders Update Failure');
            return false;
        }
        // Information log
        $this->logger('Successfully posted updates request message to REST service.', Zend_Log::INFO, 'sap_update.log');

        // Get response body/xml
        $xml = $result->getBody();

        // the XML is currently a string. Convert to xml to parse it
        // Get rid of the crappy stuff that simplexml can't handle
        $searchArray = array(
            '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"><SOAP-ENV:Body>',
            '</SOAP-ENV:Body></SOAP-ENV:Envelope>');
        $normalizedXml = str_replace($searchArray, array('', ''), $xml);

        return $normalizedXml;
    }

    /**
     * Process updates in XML response message
     *
     * Response message like:
     *
     * ﻿<?xml version='1.0' encoding="UTF-8"?>\r
     *  <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
     *    <SOAP-ENV:Body>
     *       <OrderStatusResponse>
     *         <order>
     *           <OrderNumber>31</OrderNumber>
     *           <OrderStatus>Open</OrderStatus>
     *         </order>
     *         <order>
     *           <OrderNumber>32</OrderNumber>
     *           <OrderStatus>Open</OrderStatus>
     *           <Delivery>
     *             <DeliveryNumber>4045814</DeliveryNumber>
     *             <TrackingNumber>TEST 123</TrackingNumber>
     *             <Title/>
     *             <Description/>
     *             <DeliveryLines>
     *               <WebLineNumber>0</WebLineNumber>
     *               <SAPLineNumber>0</SAPLineNumber>
     *               <Product>CY0341POGPP</Product>
     *               <Quantity>1.000000</Quantity>
     *             </DeliveryLines>
     *           </Delivery>
     *         </order>
     *         <order>
     *           <OrderNumber>49</OrderNumber>
     *           <OrderStatus>Open</OrderStatus>
     *           <Delivery>
     *             <DeliveryNumber>4045812</DeliveryNumber>
     *             <TrackingNumber>1234567</TrackingNumber>
     *             <Title/>
     *             <Description/>
     *             <DeliveryLines>
     *               <WebLineNumber>0</WebLineNumber>
     *               <SAPLineNumber>0</SAPLineNumber>
     *               <Product>CY0024PCZIP</Product>
     *               <Quantity>9.000000</Quantity>
     *             </DeliveryLines>
     *             <DeliveryLines>
     *               <WebLineNumber>0</WebLineNumber>
     *               <SAPLineNumber>1</SAPLineNumber>
     *               <Product>CY0018CPCHR</Product>
     *               <Quantity>7.000000</Quantity>
     *             </DeliveryLines>
     *           </Delivery>
     *           <Delivery>
     *             <DeliveryNumber>4045816</DeliveryNumber>
     *             <TrackingNumber/>
     *             <Title/>
     *             <Description/>
     *             <DeliveryLines>
     *               <WebLineNumber>0</WebLineNumber>
     *               <SAPLineNumber>0</SAPLineNumber>
     *               <Product>CY0017CPCHR</Product>
     *               <Quantity>1.000000</Quantity>
     *             </DeliveryLines>
     *             <DeliveryLines>
     *               <WebLineNumber>0</WebLineNumber>
     *               <SAPLineNumber>1</SAPLineNumber>
     *               <Product>CY0005CPNEO</Product>
     *               <Quantity>4.000000</Quantity>
     *             </DeliveryLines>
     *           </Delivery>
     *         </order>
     *         <order/>
     *         <order>
     *           <OrderNumber>43</OrderNumber>
     *           <OrderStatus>Open</OrderStatus>
     *         </order>
     *         <order>
     *           <OrderNumber>44</OrderNumber>
     *           <OrderStatus>Open</OrderStatus>
     *         </order>
     *         <order>
     *           <OrderNumber>49</OrderNumber>
     *           <OrderStatus>Open</OrderStatus>
     *           <Delivery>
     *             <DeliveryNumber>4045812</DeliveryNumber>
     *             <TrackingNumber>1234567</TrackingNumber>
     *             <Title/>
     *             <Description/>
     *             <DeliveryLines>
     *               <WebLineNumber>0</WebLineNumber>
     *               <SAPLineNumber>0</SAPLineNumber>
     *               <Product>CY0024PCZIP</Product>
     *               <Quantity>9.000000</Quantity>
     *             </DeliveryLines>
     *             <DeliveryLines>
     *               <WebLineNumber>0</WebLineNumber>
     *               <SAPLineNumber>1</SAPLineNumber>
     *               <Product>CY0018CPCHR</Product>
     *               <Quantity>7.000000</Quantity>
     *             </DeliveryLines>
     *           </Delivery>
     *           <Delivery>
     *             <DeliveryNumber>4045816</DeliveryNumber>
     *             <TrackingNumber/>
     *             <Title/>
     *             <Description/>
     *             <DeliveryLines>
     *               <WebLineNumber>0</WebLineNumber>
     *               <SAPLineNumber>0</SAPLineNumber>
     *               <Product>CY0017CPCHR</Product>
     *               <Quantity>1.000000</Quantity>
     *             </DeliveryLines>
     *             <DeliveryLines>
     *               <WebLineNumber>0</WebLineNumber>
     *               <SAPLineNumber>1</SAPLineNumber>
     *               <Product>CY0005CPNEO</Product>
     *               <Quantity>4.000000</Quantity>
     *             </DeliveryLines>
     *           </Delivery>
     *         </order>
     *       </OrderStatusResponse>
     *     </SOAP-ENV:Body>
     *   </SOAP-ENV:Envelope>
     *
     * @param string $xml XML response message
     */
    public function processUpdates($normalizedXml)
    {
		//$this->logger("Received XML:\n" . $normalizedXml, Zend_Log::ALERT, 'sap_update.log');
        // Validate response XML
        $xml = new SimpleXMLElement($normalizedXml);

        //$this->logger("print_r:\n" . print_r($xml,true), Zend_Log::ALERT, 'sap_update.log');
        // Iterate updates
        foreach ($xml->order as $node) {

            $incrementId = (string) $node->OrderNumber;
            // Raise if order appears not known to SAP
            if (!isset($node->OrderStatus) and !isset($node->OrderNumber)) {
                if(isset($node->OrderNumber)) {
                    $this->logger('Order '.$incrementId.' not known in SAP. This requires investigation. Node: '."\n". $node->asXML(), Zend_Log::ALERT, 'sap_update.log');
                    // Notify admin contact
                    Mage::helper('inecom_sap/data')->notify('Order not known in SAP. This requires investigation. Response message: '. print_r($normalizedXml, true), 'SAP Order number unknown');
                }
                continue;
            }

            //$this->logger('Found Order '.$incrementId."\n". $node->asXML(), Zend_Log::ALERT, 'sap_update.log');
            // Get (custom) order number / 'increment_id'
            // Check if we should mark the order as closed
            $close = ((string) $node->OrderStatus !== 'Open') ? true : false;

            // Skip if we didn't request updates for this order	- Update: Never check this now - SAP is pushing to this.
/*            if (!in_array($incrementId, $this->orderMumbers)) {
                $this->logger('Unrequested order update in feed: '.$incrementId, Zend_Log::ALERT, 'sap_update.log');
                continue;
            }
*/
            // Load order from increment id (Order Number)
            //$order = Mage::getModel('sales/order')->load($incrementId);
            $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
            if (!$order->getId()) {
                $this->logger('Order '.$incrementId.' not known in Magento. This requires investigation. Node: '."\n". $node->asXML(), Zend_Log::ALERT, 'sap_update.log');
                // Notify admin contact
                Mage::helper('inecom_sap/data')->notify('Order not known in Magento. This requires investigation. Response message: '. $node->asXML(), 'SAP Order number unknown');
            	continue;
            } else {

                $this->logger('Found Order ID '.$order->getId(), Zend_Log::ALERT, 'sap_update.log');
            }
            $shipment = Mage::getModel('sales/order_shipment');

            $hasDeliveries = false;


            // Iterate deliveries
            if($node->Delivery!=null) {
                foreach ($node->Delivery as $delivery) {

                    $hasDeliveries = true;

                    $deliveryNumber = (string) $delivery->DeliveryNumber;
                    $trackingNumber = (string) $delivery->TrackingNumber;
                    $trackingCarrier = (string) $delivery->Carrier;
                    $title = (string) $delivery->Title;
                    $description = (string) $delivery->Description;
                    $status = (int) $delivery->Status;


                    $this->logger('deliveryNumber: ' . $deliveryNumber, Zend_Log::ALERT, 'sap_update.log');


                    /**
                     * Check if delivery has already been created in Magento
                     * with this incerement_id (DeliveryNumber)
                     */
                    /*

                    if ($this->deliveryExists($deliveryNumber)) {
                        continue;
                    }
                    */
    				$shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($deliveryNumber);

    				$existingShipment = $shipment->getEntityId() ? true : false;

                    // Iterate delivery lines
                    $totalQty = 0;

                    //$currentShipment = $this->getShipment($deliveryNumber);
                    $this->logger('currentShipment: ' . $currentShipment, Zend_Log::ALERT, 'sap_update.log');

                    if($existingShipment) {
    					$this->logger('Delivery Exists already - dont make a new one', Zend_Log::ALERT, 'sap_update.log');
                    } else {
    					foreach ($delivery->DeliveryLines as $delivery) {

    						// Load product on SKU for name and product_id
    						//$product = Mage::getModel('catalog/product');
    						$sku = (string) $delivery->Product;
                            if($sku != self::FREIGHT_ITEM) {
        						//$productId = $product->getIdBySku($sku);
        						$orderItemId = (string) $delivery->WebLineNumber;
        						// Load product using product id
        						//$product->load($productId);
        						// Load order item for normalised name
        						$orderItem = Mage::getModel('sales/order_item')->load($orderItemId);
        						if (!$orderItem->getId()) {
        							$this->logger('orderItem '.$orderItemId.' not known in Magento. This requires investigation. Node: '."\n". $node->asXML(), Zend_Log::ALERT, 'sap_update.log');
        							continue;

        						}

        						// skip shipment item if it exists already
        						//$this->logger('orderItemId: ' . $orderItemId, Zend_Log::ALERT, 'sap_update.log');
        						//if ($this->shipmentItemExists($orderItemId,$currentShipment)) {
        						//    $this->logger('orderItemId exists. Skip. ', Zend_Log::ALERT, 'sap_update.log');
        						//    continue;
        						//}

        						$shipmentItem = Mage::getModel('sales/order_shipment_item');
        						$theData = array(
        							'qty' => (string) $delivery->Quantity,
        							'name' => $orderItem->getName(),
        							'sku' => $sku,
        							'product_id' => $orderItem->getProductId(),
        							'order_item_id' => (string) $delivery->WebLineNumber
        						);
        						$this->logger("sales/order_shipment_item data: \n" . print_r($theData,true), Zend_Log::ALERT, 'sap_update.log');
        						$shipmentItem->setData($theData);

        						$shipment->addItem($shipmentItem);

        						$totalQty += (int) $delivery->Quantity;
                            }
    					}

    					// Shipment parent record
    					$shipment_cfg = array(
    						'store_id' => $order->getStoreId(),
    						'total_qty' => $totalQty,
    						'order_id' => $order->getEntityId(),
    						'customer_id' => $order->getCustomerId(),
    						'shipping_address_id' => $order->getShippingAddressId(),
    						'billing_address_id' => $order->getBillingAddressId(),
    						'increment_id' => $deliveryNumber,
    						//'increment_id' => $order->getIncrementId(),
    						'shipment_status' => $status,
    						'email_sent' => 1
    					);
    						$this->logger("sales/order_shipment_item data: \n" . print_r($shipment_cfg,true), Zend_Log::ALERT, 'sap_update.log');
    					//if (!empty($currentShipment)) {
    					//      $shipment_cfg['entity_id'] = $currentShipment;
    					//}

    					$shipment->setData($shipment_cfg);

    					$shipment->save();
    				}
                    // Add tracking record to shipment
                    if ($trackingNumber !== '') {
                        $track = Mage::getModel('sales/order_shipment_track');
                        $track->setOrderId($order->getEntityId());
    					$theData = array(
                            //'weight' => 0,
                            'qty' => $totalQty,
                            'order_id' => $order->getEntityId(),
                            'track_number' => $trackingNumber,
                            'title' => $trackingCarrier,
                            'description' => '',
                            'carrier_code' => 'custom'
                        );
    					$this->logger("sales/order_shipment_track data: \n" . print_r($theData,true), Zend_Log::ALERT, 'sap_update.log');
                        $track->setData($theData);
                        $shipment->addTrack($track);
    					$shipment->save();
                    }

                    // Notify customer
                    $comment_template = '
                        <table cellspacing="0" cellpadding="0" border="0" width="650" style="border:1px solid #eaeaea">
                            <tr>
                                <td align="left" valign="top" style="font-size:11px;padding:3px 9px">
                                <strong>{COMMENT}</strong></td></tr>
                        </table>';
                    $comment = 'Your order has been successfully shipped.';

                    // process_shipment_items
                    $processed_shipment_items = $this->getShipmentItemsCount($order->getId());

                    // total items in the order
                    $total_order_items = sizeof($order->getAllItems());

                    if ($total_order_items != $processed_shipment_items){
    					$this->logger('OrderNumber: ' . $incrementId.' Mismatch in shipping quantities! total_order_items:' .$total_order_items.', processed_shipment_items: '.$processed_shipment_items, Zend_Log::ALERT, 'sap_update.log');
                        $comment = 'Due to the size of your order we will be dispatching products in multiple shipments. The remaining products in your order will be sent as they are available. You will be notified via email as these are sent';
                    }
                    if($existingShipment) {
                    	$comment = 'Your '.$trackingCarrier.' tracking number is '.$trackingNumber;
                    	$shipment->sendUpdateEmail(true, $comment);
                    } else {
    					$comment = str_replace('{COMMENT}',$comment,$comment_template);
    					$shipment->sendEmail(true, $comment);
                    }
                    //$shipment_cfg = array('total_qty'=>$processed_shipment_items,'entity_id'=>$shipment->getEntityId());
                    //$shipment->setData($shipment_cfg);
                    //$shipment->save();

                }
            } else {
                $this->logger('No deliveries in XML: '."\n". $node->asXML(), Zend_Log::ALERT, 'sap_update.log');

            }

            /**
             * Inecom does notdifferentiate between Closed and Cancelled
             * there is only closed. I understand from Inecom that it’s
             * not a requirement to differentiate.
             *
             * CLOSED/CANCELED are protected states and can not be set using
             * setState method ..
             */
            if ($close) $order->setState(Mage_Sales_Model_Order::STATE_CLOSED, true)->save();
            /*
            if ($close) {
                $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CLOSED, 'Closed on request of SAP web service');
                $order->setData(array(
                    'state' => Mage_Sales_Model_Order::STATE_CLOSED,
                    'status' => Mage_Sales_Model_Order::STATE_CLOSED
                ));
                $order->save();
            }
            exit;
            */
        }
    }

    /**
     * Check if a shipment with the given increment_id already exists.
     *
     * @param string $deliveryNumber
     */
    protected function deliveryExists($deliveryNumber)
    {
        $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($deliveryNumber);
        return $shipment->getEntityId() ? true : false;

    }

    /**
     * Return existed shipment ID or zero
     * @param type $deliveryNumber
     * @return type
     */
    protected function getShipment($deliveryNumber)
    {
        $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($deliveryNumber);
        return (int)@$shipment->getEntityId();

    }

    /**
     * Check id shipment item exists
     * @param type $id
     * @param type $shipmentId
     * @return string
     */
    protected function shipmentItemExists($id = 0, $shipmentId = 0){
        //$shipmentItem = Mage::getModel('sales/order_shipment_item')->loadByOrderItemId(30);
        $shipmentItems = Mage::getModel('sales/order_shipment_item')->getCollection()->setShipmentFilter($shipmentId);
        foreach ($shipmentItems as $t) if ($t->getOrderItemId() == $id) return true;
        return false;
    }

    /**
     * Get shipments items amount
     * @param type $shipmentId
     * @return type
     */
    protected function getShipmentItemsCount($order_id = 0){
        $shipments = Mage::getModel('sales/order_shipment')->getCollection()->addFilter('order_id',$order_id);
        $count = 0;
        foreach ($shipments as $shipment) {
            $count+=$shipment->getTotalQty();
        }
        return $count;
    }

    /**
     * Returns XML message to post to SAP REST service to query for order
     * updates
     *
     * @todo Write method
     * @return string XML
     */
    protected function getRequestMessage()
    {
        $msg = '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
            <SOAP-ENV:Body>
                <GetOrderStatus xmlns:b1mb="http://tempuri.org">
                    <Orders>';

                    foreach ($this->orderMumbers as $number) {
                        $msg .= '<Order>
                    <WebsiteRef>'.Mage::getStoreConfig('sap/settings/websiteref').'</WebsiteRef>
                    <System>'.Mage::getStoreConfig('sap/settings/sapsystem').'</System>
                           <OrderNumber>'.$number.'</OrderNumber>
                        </Order>';
                    }

                    $msg .= '
                    </Orders>
                </GetOrderStatus>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';

        return $msg;
    }

    /**
     * Returns array with order numbers of orders that have been reported to SAP
     * with state = 'processing'
     *
     * @return array
     */
    protected function getOrderNumbers()
    {
        $incrementIds = array();

        // Collect orders with state 'processing' and a SAP order queue status
        // of 'delivered'
        $collection = Mage::getModel('sales/order')->getCollection();
        $collection
            ->addFieldToSelect(array('entity_id', 'increment_id'))
            //->addFieldToSelect('*')
            //->addFieldToFilter('sap_order_queue.order_id', array('notnull' => 1))
            ->addFieldToFilter('sap_order_queue.status', Inecom_Sap_Helper_Order::DELIVERED)
            // status filter disabled for development
            //->addFieldToFilter('state', 'new')
            ->addFieldToFilter('state', array("in"=>array('new','processing','pending','payment_review')))
            //->addFieldToFilter('state', 'complete')
            ->getSelect()
            ->joinLeft(
                'sap_order_queue',
                'sap_order_queue.order_id = main_table.entity_id',
                array('sap_order_queue.order_id', 'sap_order_queue.queue_id')
            );

        foreach ($collection as $order) {
            $incrementIds[$order->getEntityId()] = $order->getIncrementId();
        }
        return $incrementIds;
    }

    /**
     * Converts integer to something human readable From here a language file
     * can assist in modifying this
     *
     * @param integer $status
     * @return string
     */
    public function prettyStatus($status)
    {
        $ret = '';
        switch ($status) {
            case self::SHIPMENT_STATUS_DISPATCHED:
                $ret = 'Dispatched';
                break;
            case self::SHIPMENT_STATUS_INCOUNTRY:
                $ret = 'In country';
                break;
            case self::SHIPMENT_STATUS_INNETWORK:
                $ret = 'In network';
                break;
            case self::SHIPMENT_STATUS_RECEIVED:
                $ret = 'Received';
                break;
        }
        return $ret;
    }
    public function logger($msg,$lvl,$file) {
    	Mage::log($msg,$lvl,$file);
    }
}
