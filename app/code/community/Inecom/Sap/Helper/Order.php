<?php

/**
 * Helper class that basically single handedly reports Magento orders to the SAP
 * REST service. Methods here can be called either from a controller or from a
 * cron job (Observer.php)
 *
 * Order qeueu explained:
 * - Order gets added to the queue with status 'pending'
 * - Once delivered status is updated to 'delivered'
 * - in case delivery failed the status becomes 'failed'
 * - Once the error has been reported through the notification system the status
 *   is set to 'reported'
 * - From the 'Failed Order Reports' panel in the Magento admin, an item can be
 *   pushed back into the queue with status 'pending'
 *
 */
class Inecom_Sap_Helper_Order extends Mage_Core_Helper_Abstract
{
    /**
     * FreightItem: Item to be used for freight
     */
    const FREIGHT_ITEM = 'Freight';

    /**
     * MaxOrders: Maximum number of new orders to send to SAP Business One
     */
    const MAX_ORDERS = 20;

    /**
     * Pending: order is queued for delivery
     */
    const PENDING = 'pending';

    /**
     * Delivered: order has been created through SAP web service
     */
    const DELIVERED = 'delivered';

    /**
     * Failed: order has been rejected by SAP web service
     */
    const FAILED = 'failed';

    /**
     * Reported: order has been reported as failed and can be pushed back into
     * the queue after being updated from the 'Failed Order Reports' panel.
     */
    const REPORTED = 'reported';

    /**
     * Reference to HTTP client
     * @var Zend_Http_Client
     */
    protected $client;

    /**
     * Fetches new orders that haven't been queued for pa push to SAP yet and
     * adds them to the queue
     *
     * @return void
     */
    public function populateQueue()
    {
        Mage::log('Start populating queue from orders...', Zend_Log::INFO, 'sap.log');

        // Fetch complete orders that are not yet in the queue
        $collection = Mage::getModel('sales/order')->getCollection();
        $collection
            ->addFieldToSelect(array('entity_id', 'state', 'status'))
            ->addFieldToFilter('sap_order_queue.order_id', array('null' => 1))
            // status filter disabled for development
            //->addFieldToFilter('state', 'new')
            ->addFieldToFilter('state', array("in"=>array('processing','pending','new')))
            //->addFieldToFilter('state', array("in"=>array('pending','processing')))
            ->addFieldToFilter('main_table.created_at',array('gt'=>'2015-08-31'))
//            ->addFieldToFilter('store_id', array("neq"=>"6"))
            //->addFieldToFilter('state', 'complete')
            ->setPageSize(self::MAX_ORDERS)
            ->setCurPage(1)
            ->getSelect()
            ->joinLeft(
            //array('table_alias' => Mage::getResourceModel('sap/order')->getTable('sap/order_queue')),
                'sap_order_queue',
                'sap_order_queue.order_id = main_table.entity_id',
                array('sap_order_queue.order_id')
            )
            ->order('main_table.entity_id desc');

        // echo "Collection count :" .count($collection);
        foreach ($collection as $item) {
            // Create new queue record
            $model = Mage::getModel('sap/queue');
            $order = Mage::getModel('sales/order');
            $order->load($item->getEntityId());
            $store = Mage::getModel('core/store')->load($order->getStoreId());
            //echo "<br> StoreName :" .$store->getName();

            switch($store->getName()) {

            }
            //  $storeid = $order->getStoreId();
            //echo '... added order to queue: '.$item->getEntityId();
            //if($storeid!=="6") {
            $model->setData(array(
                'order_id' => $item->getEntityId()
            ));
            /*
            if ($model->getCreatedAt() == null || $model->getUpdatedAt() == null) {
                $model->setCreatedAt(now())
                    ->setUpdatedAt(now());
            } else {
                $model->setUpdatedAt(now());
            }
            */
            $model->save();

            Mage::log('... added order to queue: '.$item->getEntityId(), Zend_Log::INFO, 'sap.log');
            //ONLY DO 1 to test
            //break;
            //}
        }

        Mage::log('Completed populating queue', Zend_Log::INFO, 'sap.log');
    }


    /**
     * Fetches x orders from the queue and shoots as many requests off to the
     * REST service
     *
     * @return void
     */
    public function processQueue()
    {
        // Initialise ZF HTTP client
        $this->client = Mage::helper('inecom_sap/data')->initHttpClient();

        // Get eligible orders from the queue
        $collection = Mage::getModel('sales/order')->getCollection();
        $collection
            ->addFieldToSelect(array('entity_id'))
            //->addFieldToSelect('*')
            //->addFieldToFilter('sap_order_queue.order_id', array('notnull' => 1))
            ->addFieldToFilter('sap_order_queue.status', self::PENDING)
            // status filter disabled for development
            ->addFieldToFilter('state', array("in"=>array('processing','pending','new')))
            //  ->addFieldToFilter('state', array("in"=>array('processing','pending')))
            //->addFieldToFilter('state', 'complete')
            ->getSelect()
            ->joinLeft(
            //array('table_alias' => Mage::getResourceModel('sap/order')->getTable('sap/order_queue')),
                'sap_order_queue',
                'sap_order_queue.order_id = main_table.entity_id',
                array('sap_order_queue.order_id', 'sap_order_queue.queue_id')
            );

        // Iterate orders and push them one by one
        foreach ($collection as $order) {
            Mage::log('Creating XML ', Zend_Log::INFO, 'sap.log');
            // Construct the REST request message
            $msg = $this->constructRequestMessage($order);

//            header('Content-type: text/xml');
//            echo $msg;
//            exit;

            Mage::log('Pushing order with message: '.$msg, Zend_Log::INFO, 'sap.log');

            // Message ok
            if ($msg !== false) {
                // Fire in the hole!
                $ok = $this->pushOrder($msg, $order, $order->getQueueId());

                // Push validated?
                if ($ok !== false) {
                    // Mark the order in the queue as 'delivered'
                    if($order->getState()!='new') {
                        Mage::getModel('sap/queue')
                            ->setData(array(
                                'status' => self::DELIVERED
                            ))
                            ->setId($order->getQueueId())
                            ->save();
                    }
                }
            } else {
                Mage::getModel('sap/queue')
                    ->setData(array(
                        'status' => self::FAILED
                    ))
                    ->setId($order->getQueueId())
                    ->save();

            }
        }
    }

    /**
     * Creates request message for creating a new order through the SAP REST
     * service
     *
     * Should produce something like:
     *
     *  <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
     *    <SOAP-ENV:Body>
     *      <AddOrderTest xmlns="http://tempuri.org/">
     *        <CardCode>CASHSALE</CardCode>
     *        <lines>
     *          <row>
     *            <item>CY-3-RW</item>
     *            <price>123.45</price>
     *            <quantity>12</quantity>
     *          </row>
     *          <row>
     *            <item>RAWHTCPRO</item>
     *            <price>100.00</price>
     *            <quantity>4</quantity>
     *          </row>
     *        </lines>
     *      </AddOrderTest>
     *    </SOAP-ENV:Body>
     *  </SOAP-ENV:Envelope>
     *
     * @param Mage_Sales_Model_Order $order
     * @return string XML message
     */
    public function constructRequestMessage($order)
    {
        // Fetch order items
        //$items = $order->getAllItems();
        $items = $order->getAllVisibleItems();

        // Check if all makes sense
        if (!count($items)) {
            Mage::log('Order does not contain any items.', Zend_Log::WARN, 'sap.log');
            return false;
        }
        $queueid = $order->getQueueId();
        $order = Mage::getModel('sales/order')->load($order->getId());


        // Check to see if total order has zero value
        if (!$order->getGrandTotal()) {
            Mage::log('Order has zero value.', Zend_Log::WARN, 'sap.log');

            $zeroitems = array();
            foreach ($items as $item) {
                if($item->getPriceInclTax()==0) {
                    $zeroitems[] = $item;
                }
            }
            $emsg = '';
            if(count($zeroitems)>0) {
                $emsg = 'Items in order' .$order->getIncrementId()." have zero value:\n";
                foreach($zeroitems as $item) {
                    $emsg .= $item->getSku()."\n";
                }
            }
            Mage::getModel('sap/queue')
                ->setData(array(
                    'status' => self::FAILED,
                    'messages' => "Zero value in total or items\n".$emsg
                ))
                ->setId($queueid)
                ->save();
            // Mage::helper('inecom_sap/data')->notify('Order ' . $order->getIncrementId().' has zero total and was not sent to SAP'."\n\n".$emsg, 'SAP Order ' . $order->getQueueId() . ' / '.$order->getIncrementId().' has zero total');
            //echo "<br> Error with zero total";
            return false;
        }

        $storeid = $order->getStoreId();
        // Check to see if it any item has zero value and cache item objects
        $zeroitems = array();
        $totalweight = 0;
        $item_objects = array();
        foreach ($items as $item) {
            $item_objects[$item->getSku()] = Mage::getModel('catalog/product')->setStoreId($storeid)->loadByAttribute('sku', $item->getSku());
            if ($item_objects[$item->getSku()] != null){
                $totalweight += ($item_objects[$item->getSku()]->getWeight() *$item->getQtyOrdered()) ;
            }
            if($item->getPriceInclTax()==0) {
                $zeroitems[] = $item;
            }
        }

        if(count($zeroitems)>0) {
            $emsg = 'Items in order' .$order->getIncrementId()." have zero value:\n";
            foreach($zeroitems as $item) {
                $emsg .= $item->getSku()."\n";
            }
            Mage::log('Order item has zero value.', Zend_Log::WARN, 'sap.log');
            Mage::getModel('sap/queue')
                ->setData(array(
                    'status' => self::FAILED,
                    'messages' => "Zero value items\n".$emsg
                ))
                ->setId($queueid)
                ->save();
            // Mage::helper('inecom_sap/data')->notify( 'Error ', 'SAP Order items in ' .$order->getIncrementId().' have zero total');
            // echo "<br> Error with zero total";
            return false;

        }

        $customerEmail = $order->getCustomerEmail();
        //echo "Order Number : " .$order->getIncrementId();
        // echo " Email used : " .$customerEmail . "</br>";


        //Check and confirm the email address used
        if ($customerEmail =="" ||customerEmail =="n/a@na.na") {
            Mage::log('Order has invalid email address.', Zend_Log::ALERT, 'sap.log');

            Mage::getModel('sap/queue')
                ->setData(array(
                    'status' => self::FAILED,
                    'messages' => "Email address not found on the customer"
                ))
                ->setId($queueid)
                ->save();
            //Mage::helper('inecom_sap/data')->notify('Order ' . $order->getIncrementId().' has invalid email address and was not sent to SAP'."\n\n", 'SAP Order ' . $order->getQueueId() . ' / '.$order->getIncrementId().' has invalid email');

            return false;
        }
        $shippingAddress = $order->getShippingAddress();
        $billingAddress = $order->getBillingAddress();

        $customer = $order->getCustomer();

        $customer_id = 'GUEST';

        $shipping_method = $order->getShippingCarrier()->getCarrierCode();



        if($customer) {
            $customer_id = $customer->getId();
        }
        //Adding ordersource for website and Ebay
        $orderSource = 'Website';

        $payment_method = false;
        $payment_transid = false;
        $payment = $order->getPayment();
        if($payment) {
            $payment_method = $payment->getMethodInstance()->getTitle();
            $payment_transid = $payment->getLastTransId();
            // Only Mastercard & Visa are supported
            switch ($payment->getCcType()) {
                case 'VI':
                    $cardType = 'VISA';
                    break;
                case 'MC':
                    $cardType = 'MC';
                    break;
                /*case 'DI':
                    $cardType = 'DISCOVER';
                    break;
                case 'AE':
                    $cardType = 'AMERICANEXPRESS';
                    break;*/
            }
            if($payment_method =='m2epropayment' || $payment_method =="eBay Payment")
            {
                $orderSource ='Ebay';
            }

        }
        switch ($shipping_method) {
            case 'freeshipping':
            case 'flatrate':
                $shiptype = '1';
                break;
            default:
                $shiptype = '2';
                break;
        }
        //This is just for testing where we are adding UK to shipping-method
        //this should have been set in Magneto instead
        if($order->getStore()->getCode() == 'united_kingdom')
        {
            $shipping_method .= "-UK";
        }
        /*
        if($storeid==6) {
            if(stripos($billingAddress->getName(),'tester')!==false) {
                $storeid=4;
            } else {
                return false;
            }
        }

        echo "<br>getTaxInvoiced: ".$order->getTaxInvoiced();
        echo "<br>getTaxAmount: ".$order->getTaxAmount();
        $tax_info = $order->getFullTaxInfo();
        echo "getFullTaxInfo: ".print_r($tax_info,true);
        exit();
        */
        $shipcountry = $shippingAddress->getCountryId();
        $billcountry = $billingAddress->getCountryId();
        // if($shipcountry=='EW')
        //  $shipcountry = 'UK';
        $shipState = '';
        $shipCity = $shippingAddress->getCity();
        switch($shipcountry) {
            case 'US':
            case 'AU':
                $shipState = $this->fixsautate($shippingAddress->getRegionCode());
                break;
            default:
                $shipCity .= ". " .$shippingAddress->getRegionCode();
        }

        $billState = '';
        $billCity = $billingAddress->getCity();

        //echo "<br> billing regionCode : " .$billingAddress->getRegionCode();
        switch($billcountry) {
            case 'US':
            case 'AU':
                $billState = $this->fixsautate($billingAddress->getRegionCode());
                break;
            default:
                $billCity .= ". " .$billingAddress->getRegionCode();

        }
        if($order->getStore()->getCode() != 'australia')
        {
            $taxCode = 'UKP1';
            $taxPercent = 6;
        } else {
            $taxCode = 'S1';
            $taxPercent = 11;
        }
        $shippingCost = $order->getShippingInclTax();

        $ccode = $order->getOrderCurrencyCode()=='AUD'? '$' : $order->getOrderCurrencyCode();
        $msg = '<?xml version=\'1.0\' encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
    <SOAP-ENV:Body>
        <WebOrderNew xmlns="">
            <config>
                <database>'.Mage::getStoreConfig('sap/settings/sapsystem').'</database>
                <series object="customer">'.Mage::getStoreConfig('sap/settings/newcustseries').'</series>
                <!--<series object="order">'.Mage::getStoreConfig('sap/settings/neworderseries').'</series>-->
            </config>
            <manifest>
                <object>
                    <customer>
                        <store_customer_id>'.$customer_id.'</store_customer_id>
                         <email><![CDATA['.$customerEmail.']]></email>
                        <bill_to_name><![CDATA['.$billingAddress->getName().']]></bill_to_name>
                        <bill_to_business><![CDATA['.$billingAddress->getCompany().']]></bill_to_business>
                        <bill_to_address><![CDATA['.$billingAddress->getStreet1().']]></bill_to_address>
                        <bill_to_address2><![CDATA['.$billingAddress->getStreet2().']]></bill_to_address2>
                        <bill_to_city><![CDATA['.$billCity.']]></bill_to_city>
                        <bill_to_postcode><![CDATA['.$billingAddress->getPostcode().']]></bill_to_postcode>
                        <bill_to_country><![CDATA['.$billingAddress->getCountry().']]></bill_to_country>
                        <bill_to_countryshort><![CDATA['.$billcountry.']]></bill_to_countryshort>
                        <bill_to_phone><![CDATA['.$billingAddress->getTelephone().']]></bill_to_phone>
                        <bill_to_state><![CDATA['.$shipState.']]></bill_to_state>
                        <ship_to_name><![CDATA['.$shippingAddress->getName().']]></ship_to_name>
                        <ship_to_business><![CDATA['.$shippingAddress->getCompany().']]></ship_to_business>
                        <ship_to_address><![CDATA['.$shippingAddress->getStreet1().']]></ship_to_address>
                        <ship_to_address2><![CDATA['.$shippingAddress->getStreet2().']]></ship_to_address2>
                        <ship_to_city><![CDATA['.$shipCity.']]></ship_to_city>
                        <ship_to_postcode><![CDATA['.$shippingAddress->getPostcode().']]></ship_to_postcode>
                        <ship_to_country><![CDATA['.$shippingAddress->getCountry().']]></ship_to_country>
                        <ship_to_countryshort><![CDATA['.$shipcountry.']]></ship_to_countryshort>
                        <ship_to_phone><![CDATA['.$shippingAddress->getTelephone().']]></ship_to_phone>
                        <ship_to_state><![CDATA['.$shipState.']]></ship_to_state>
                    </customer>
                    <order>
                        <store_id>'.$order->getIncrementId().'</store_id>
                        <order_type>magento</order_type>
                        <order_campaign>campaign</order_campaign>
                        <order_date>'.$order->getCreatedAtStoreDate()->toString('yyyyMMdd').'</order_date>
                        <order_source>'.Mage::getStoreConfig('sap/settings/websiteref').'</order_source>
                        <U_INE_WEBSOURCE>'.$order->getStore()->getCode().'</U_INE_WEBSOURCE>
                        <order_total currency="'.$ccode.'">'.number_format($order->getGrandTotal(), 2,".","").'</order_total>
                        <order_discount>0</order_discount>
                        <order_comment><![CDATA['.$order->getBiebersdorfOrdercomment().']]></order_comment>
                        <U_INE_DiscountCode>'.$order->getDiscountDescription().'</U_INE_DiscountCode>
                        <U_INE_DiscountAmt>'.$order->getDiscountAmount().'</U_INE_DiscountAmt>
                        <U_INE_OrderSource>'.$orderSource.'</U_INE_OrderSource>
                        <order_lines>';

        // Iterate order items and create message body
        foreach ($items as $item) {
            $product = $item->getProduct();
            $_product = $item_objects[$item->getSku()];
            if( $_product != null) {
                $override_warehouse  = $_product->getAttributeText('ine_warehouse');
            }else{
                $override_warehouse='';
            }
            // Load product for attributes
            //$product = Mage::getModel('catalog/product')->load($item->getId());


            // work out item options
            $options = $item->getProductOptions();
            $opt_list = array();
            $msg_opts = '';
            $total_opt_value = 0;

            if(is_array($options['options'])) {
                $opts = $options['options'];

                foreach($opts as $option_key=>$option_arr) {
                    //print_r($option_arr, true);
                    $opt_list[] = $option_arr['value'];
                    $option = $product->getOptionById($option_arr['option_id']);


                    //print_r($option);
                    $price = 0;
                    if($option){
                        foreach ($option->getValues() as $values) {
                            if ($values->getId() == $option_arr['option_value']) {
                                $price = $values->price;
                            }
                        }
                    }
                    //$store = Mage::getModel('core/store')->load($order->getStoreId());

                    $itemValue = $option_arr['value'];
                    //echo "<br> Item Value :" .$itemValue;
                    // echo "<br> Store Value :" .$order->getStore()->getCode();
                    if($order->getStore()->getCode() == 'united_kingdom')
                    {
                        $itemValue .= "-UK";
                    }
                    // echo "<br> Item Value :" .$itemValue;

                    $total_opt_value += $price;
                    $msg_opts .= '
                                    <line>
                                        <tax>'.number_format($price/$taxPercent, 2,".","").'</tax>
                                        <store_line_id>'.$item->getItemId().'-'.$option_key.'</store_line_id>
                                        <item_code>'. $itemValue.'</item_code>
                                        <dispatch_date>'.$order->getCreatedAtStoreDate()->toString('yyyyMMdd').'</dispatch_date>
                                        <price_ea>'.number_format($price-$price/$taxPercent, 2,".","").'</price_ea>
                                        <price_ea_gross>'.number_format($price, 2,".","").'</price_ea_gross>
                                        '.($override_warehouse!=''? '<warehouse><![CDATA['.$override_warehouse.']]></warehouse>' : '').'
                                        <U_INE_Comments><![CDATA['.$option_arr['print_value'].']]></U_INE_Comments>
                                        <U_INE_WarrProduct>'.$item->getItemId().'</U_INE_WarrProduct>
                                        <weight>0</weight>
                                        <U_INE_ShippingCharge>0</U_INE_ShippingCharge>
                                        <qty>'.number_format($item->getQtyOrdered(), 2,".","").'</qty>
                                    </line>';
                }
            }

            // Add row to message
            $item_price = $item->getPriceInclTax();
            $price = $item_price - $total_opt_value;

            if( $_product != null) {
                $static_price = $_product->getPrice();
            }else{
                $static_price =0;
            }

            if($static_price>0)
                $discount = 100 * (($static_price-$price)/$static_price);
            else
                $discount = 0;
            $itemShipCost = 0;

            if( $_product != null) {
                $_weight = $_product->getWeight();
            }else{
                $_weight =0;
            }

            if($shippingCost>0 and $_weight >0) {
                $itemShipCost = $shippingCost*(($_weight*$item->getQtyOrdered())/$totalweight);
            }

            $_dispatch = str_replace('-','',$item->getDispatchDate());
            // echo $_dispatch;
            //Again this should be testing only
            //Setting the shipping protection for UK..
            $itemSKU =  $item->getSku();

            //echo "<br> item sku : " .$itemSKU;
            if( $itemSKU == 'Shipping-Protection' && $order->getStore()->getCode() =='united_kingdom'){
                $itemSKU .= "-UK";
                // echo "<br> changed";

            }
            //echo "<br> item sku : " .$itemSKU;

            $msg .= '
                            <line>
                                <tax>'.number_format($price/$taxPercent, 2,".","").'</tax>
                                <store_line_id>'.$item->getItemId().'</store_line_id>
                                <item_code>'.$itemSKU.'</item_code>
                                <dispatch_date>'.$order->getCreatedAtStoreDate()->toString('yyyyMMdd').'</dispatch_date>
                                <price_ea>'.number_format($price-$price/$taxPercent, 2,".","").'</price_ea>
                                <price_ea_gross>'.number_format($price, 2,".","").'</price_ea_gross>
                                <qty>'.number_format($item->getQtyOrdered(), 2,".","").'</qty>
                                <weight>'.$_weight.'</weight>
                                '.($override_warehouse!=''? '<warehouse><![CDATA['.$override_warehouse.']]></warehouse>' : '').'
                                <U_INE_Comments><![CDATA['.$item->getName().']]></U_INE_Comments>
                                <U_INE_ShippingCharge>'.number_format($itemShipCost, 2,".","").'</U_INE_ShippingCharge>
                                <U_INE_MagentoOpts><![CDATA['.implode(',',$opt_list).']]></U_INE_MagentoOpts>
                                <U_INE_StaticPrice>'.number_format($static_price, 2,".","").'</U_INE_StaticPrice>
                                <U_INE_PreorderDate>'.$_dispatch.'</U_INE_PreorderDate>
                            </line>';

            $msg .= $msg_opts;

            /*
            $options = $item->getProductOptions();
            $optionIds = array_keys($options['info_buyRequest']['bundle_option']);
            $types = Mage_Catalog_Model_Product_Type::getTypes();
            $typemodel = Mage::getSingleton($types[Mage_Catalog_Model_Product_Type::TYPE_BUNDLE]['model']);
            $typemodel->setConfig($types[Mage_Catalog_Model_Product_Type::TYPE_BUNDLE]);
            $selections = $typemodel->getSelectionsCollection($optionIds, $item);
            $selection_map = array();
            foreach($selections->getData() as $selection) {
                if(!isset($selection_map[$selection['option_id']])) {
                    $selection_map[$selection['option_id']] = array();
                }
                $selection_map[$selection['option_id']][$selection['selection_id']] = $selection;
            }
            $i = 1;
            //echo "<pre>".print_r($options,true)."</pre>";
            foreach($options['info_buyRequest']['bundle_option'] as $op => $sel) {
                //foreach($sel['value'] as $selval) {
                    $_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$selection_map[$op][$sel]['sku']);
                    //echo "FOUND SKU for $op: ".$_product->getSapItemcode();
                    $unitprice = $i==1? $item->getPrice() : 0;
                    $tax = $i==1? $item->getTaxAmount() : 0;
                    $totalprice = $i==1? $item->getPriceInclTax() : 0;
                    $qty = $options['bundle_options'][$op]['value'][0]['qty'];
                    $msg .= $this->createProductRow($item->getItemId().$op,$_product->getSapItemcode(),$unitprice,$qty,$tax,$totalprice,'','',$_product->getSku());
                    $i++;
                //}

            }
            */
        }

        if($shippingCost>0) {
            $msg .= '
                            <line>
                                <tax>'.number_format($shippingCost/$taxPercent, 2,".","").'</tax>
                                <store_line_id>shipping</store_line_id>
                                <weight>0</weight>
                                <U_INE_ShippingCharge>0</U_INE_ShippingCharge>
                                <item_code>'.Mage::getStoreConfig('sap/settings/shippingprefix').'-'.$shipping_method.'</item_code>
                                <dispatch_date>'.$order->getCreatedAtStoreDate()->toString('yyyyMMdd').'</dispatch_date>
                                <price_ea>'.number_format($shippingCost-$shippingCost/$taxPercent, 2,".","").'</price_ea>
                                <price_ea_gross>'.number_format($shippingCost, 2,".","").'</price_ea_gross>
                                <qty>1</qty>
                            </line>
                    ';
        }

        $msg .= '
                        </order_lines>
                    </order>';
        if($payment and $order->getTotalPaid()>0) {
            $msg .= '
                    <payments>
                        <payment>
                            <payment_date>'.$order->getCreatedAtStoreDate()->toString('yyyyMMdd').'</payment_date>
                            <reference>'.$payment_transid.'</reference>
                            <id>'.$payment_transid.'</id>
                            <total>'.number_format($order->getTotalPaid(), 2,".","").'</total>
                            <method>'.$payment_method.'</method>
                        </payment>
                    </payments>';
        }
        $msg .= '
                </object>
            </manifest>
        </WebOrderNew>
    </SOAP-ENV:Body>
</SOAP-ENV:Envelope>';

        return $msg;
    }

    /**
     * Posts order message to the SAP REST service
     *
     * @param string $msg XML message to post
     * @return bool True on success response
     */
    protected function pushOrder($msg, $order, $queueId)
    {
        try{
            // Post message to REST service
            $result = $this->client->setRawData($msg, 'text/xml')->request('POST');
        } catch (Exception $e) {
            echo "<br/> " .print_r($e->getMessage(), true);
            $resulttxt = print_r($e->getMessage(), true);
            //Mage::helper('inecom_sap/data')->notify($resulttxt, 'SAP Integration error - Order ' . $order->getQueueId() . ' push failure');
            Mage::log('SAP Integration error. ' .$resulttxt , Zend_Log::ALERT, 'sap.log');
            echo "<br> Error connecting to B1if";
            return false;
        }

        // Check if the request was successful
        if ($result->isSuccessful()) {

            Mage::log('Successfully posted new order queue message to REST service.', Zend_Log::INFO, 'sap.log');
        } else {
            Mage::log($result, Zend_Log::ALERT, 'sap.log');
            // Notify admin contact
            $resulttxt = print_r($result, true);
            if(strpos($resulttxt,"Internal Server Error")) {
                // Mage::helper('inecom_sap/data')->notify($resulttxt, 'SAP Integration error - Order ' . $order->getQueueId() . ' push failure');
                echo "<br> Integration error :" .$resulttxt ;
            } else {
                // Mage::helper('inecom_sap/data')->notify($resulttxt, 'SAP Order ' . $order->getQueueId() . ' push failure');
                echo "<br> Integration error :" .$resulttxt ;
            }
            return false;
        }

        // Get response body/xml
        $xml = $result->getBody();

        // the XML is currently a string. Convert to xml to parse it
        // Get rid of the crappy stuff that simplexml can't handle
        $searchArray = array(
            '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"><SOAP-ENV:Body>',
            '</SOAP-ENV:Body></SOAP-ENV:Envelope>');
        $normalizedXml = str_replace($searchArray, array('', ''), $xml);

        // Check $xml for response code etc
        // @todo ...
//        header("Content-type: text/xml");
//        print_r($xml);
//        exit;

        $xml = new SimpleXMLElement($normalizedXml);


        if (strtolower((string) $xml->Result) == 'success') {
            Mage::log('Order with id '.$order->getQueueId().' has been successfully delivered to SAP.', Zend_Log::INFO, 'sap.log');
            // Give the ok
            return true;
        }

        // Add relevant info to error log
        Mage::log('Order with id '.$order->getQueueId().' has been rejected by SAP. Error message: '.$xml->Error, Zend_Log::ALERT, 'sap.log');

        // Mark the order in the queue as 'failed'. Some manual work will need to
        // be done to add this item back into the queue, after the necessary
        // changes have been made to this order
        Mage::getModel('sap/queue')
            ->setData(array(
                'status' => self::FAILED,
                'messages' => $normalizedXml
            ))
            ->setId($order->getQueueId())
            ->save();

        // Make caller aware of this sad event
        return false;
    }

    /**
     * Reports rejected orders (by SAP web service) so that they will get
     * attended to
     * @return void
     */
    public function reportFailedOrders()
    {
        $collection = Mage::getModel('sap/queue')
            ->getCollection();

        $collection->addFieldToFilter('main_table.status', self::FAILED)
            ->getSelect()
            ->joinLeft(
                'sales_flat_order',
                'sales_flat_order.entity_id = main_table.order_id',
                array('sales_flat_order.increment_id')
            );

        $description = "Order number(s):\n";
        $count = 0;
        foreach ($collection as $failure) {
            $count++;
            if ($count > 1) $description .= ', ';
            $description .= $failure->getIncrementId();
            //$description .= htmlspecialchars($failure->getMessages())."\r\n";
        }

        $count = $collection->count();
        if ($count > 0) {
            $adminnotificationModel = Mage::getModel('adminnotification/inbox');
            $adminnotificationModel->setData(array(
                'severity' => 1, // CRITICAl
                'date_added' => gmdate('Y-m-d H:i:s'),
                'title' => $count.' new order'.($count > 1 ? 's were' : ' was').' rejected by the SAP web service!',
                'description' => $description,
                'url' => ''
                //'url' => Mage::helper("adminhtml")->getUrl("adminhtml/sap_order/index/", array())
            ));
            $adminnotificationModel->save();
        }

        // Update status for each to 'reported'
        foreach ($collection as $failure) {
            Mage::getModel('sap/queue')
                ->setData(array(
                    'status' => self::REPORTED
                ))
                ->setId($failure->getQueueId())
                ->save();
        }
    }

    public function getOptionArray()
    {
        return array(
            array('label' => self::PENDING, 'value' => 'Pending'),
            array('label' => self::DELIVERED, 'value' => 'Delivered'),
            array('label' => self::FAILED, 'value' => 'Failed'),
            array('label' => self::REPORTED, 'value' => 'Reported'),
//            self::DELIVERED => 'Delivered',
//            self::FAILED => 'Failed',
//            self::REPORTED => 'Reported'
        );
    }

    public function fixsautate($state)
    {
        $state = strtolower($state);
        switch($state){
            case 'nsw':
            case 'new south wales':
                return "NSW";
                break;
            case 'act':
            case 'australian capital territory':
                return "ACT";
                break;
            case 'qld':
            case 'queensland':
                return "QLD";
                break;
            case 'vic':
            case 'victoria':
                return "VIC";
                break;
            case 'sa':
            case 'south australia':
                return "SA";
                break;
            case 'nt':
            case 'northern territory':
                return "NT";
                break;
            case 'wa':
            case 'western australia':
                return "WA";
                break;
            case 'tas':
            case 'tasmania':
                return "TAS";
                break;
            default:
                return $state;
                break;
        }

    }
}