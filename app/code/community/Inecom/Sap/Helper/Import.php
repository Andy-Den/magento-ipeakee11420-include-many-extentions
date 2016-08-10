<?php

class Inecom_Sap_Helper_Import extends Mage_Core_Helper_Abstract
{

    public function __construct()
    {
        $this->websites = Mage::app()->getWebsites();

        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getStores() as $store) {
                $this->stores[] = $store;
            }
        }
    }


    public function isRunning()
    {
        // Check if a cron job might still be running
        $c = Mage::getModel('cron/schedule')
                ->getCollection()
                ->addFieldToFilter('status', 'running')
                ->addFieldToFilter('executed_at', array(
                    'from' => date('Y-m-d H:i:s', strtotime('-10 minutes'))
                ));

        return $c->count() == 0 ? false : true;
    }

    /**
     * Gets product list from the SAP REST server
     *
     * @return String The XML String from the Rest service
     */
    public function getProducts()
    {

        $client = Mage::helper('inecom_sap/data')->initHttpClient();

        // Temporarily return fixed result
        /*
        return '<?xml version="1.0" encoding="UTF-8"?>
        <ListItemsTestResponse xmlns="http://tempuri.org/">
        <ListItemTestResult>
          <row>
            <ItemCode>CY0001CPNEO</ItemCode>
            <ItemName>Neon Red iPhone 3GS/3G Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Red</Colour>
            <Model>iPhone 3G/S</Model>
            <Range>Neon</Range>
            <Prices>
              <Price Country="AU">9.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0002CPNEO</ItemCode>
            <ItemName>Neon Pink iPhone 3GS/3G Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Pink</Colour>
            <Model>iPhone 3G/S</Model>
            <Range>Neon</Range>
            <Prices>
              <Price Country="AU">9.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>True</Available>
              </Warehouse>
            </Warehouses>
          </row>
          <row>
            <ItemCode>CY0003CPNEO</ItemCode>
            <ItemName>Neon Purple iPhone 3GS/3G Hard Case</ItemName>
            <ItemGroupCode>102</ItemGroupCode>
            <ItemGroupName>Cases</ItemGroupName>
            <Colour>Purple</Colour>
            <Model>iPhone 3G/S</Model>
            <Range>Neon</Range>
            <Prices>
              <Price Country="AU">9.000000</Price>
            </Prices>
            <Warehouses>
              <Warehouse Name="CN">
                <Available>False</Available>
              </Warehouse>
            </Warehouses>
          </row>
        </ListItemTestResult>
      </ListItemsTestResponse>';
      */

            Mage::log('doing sync', Zend_Log::ALERT, 'sapimport.log');

        $products = Mage::getModel('catalog/product')->getCollection();

        $products
            //->addFieldToFilter('sap_last_sync', array('null'=>1))
            ->setPageSize(15)
            ->setCurPage(1);

            /*
            ->order('created_at desc');
            */
        // Construct message body
        $msg = '
        <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
            <SOAP-ENV:Body>
                <ProductSync xmlns="">
                    <config>
                        <database>'.Mage::getStoreConfig('sap/settings/sapsystem').'</database>
                    </config>
                    <products>';
                foreach($products as $product) {
        $msg .= '
                    <product>
                        <sku><![CDATA['.$product->getSku().']]></sku>
                        <price><![CDATA['.($product->getPrice()+7).']]></price>
                        <finalprice><![CDATA['.($product->getFinalPrice()+2).']]></finalprice>
                        <name><![CDATA['.$product->getName().']]></name>
                    </product>';
                }

        $msg .= '
                    </products>
                </ProductSync>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>
        ';
            Mage::log('Created Message', Zend_Log::ALERT, 'sapimport.log');

        // Play the puck tones while attempting first contact
        $result = $client->setRawData($msg, 'text/xml')->request('POST');

        if ($result->isSuccessful()) {
            Mage::log('REST service successfully delivered product feed.', Zend_Log::INFO, 'sapimport.log');
        } else {
            Mage::log($result, Zend_Log::ALERT, 'sapimport.log');

            // Notify admin contact
            Mage::helper('inecom_sap/data')->notify(print_r($result, true), 'SAP Products update failure');

            return false;
        }
        Mage::log('Getting Body', Zend_Log::ALERT, 'sapimport.log');
        // Get response body/xml
        $xml = $result->getBody();
//		Mage::log($xml, Zend_Log::INFO, 'sapimport.log');

        // the XML is currently a string. Convert to xml to parse it
        // Get rid of the crappy stuff that simplexml can't handle
        $searchArray = array(
            '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"><SOAP-ENV:Body>',
            '</SOAP-ENV:Body></SOAP-ENV:Envelope>');
        $xml = str_replace($searchArray, array('', ''), $xml);


        return $xml;
    }

    /**
     * Converts XML from web service to a CSV file compatible with
     * Magento's importer model
     *
     * @param string $xml XML body as returned by web service
     * @return string CSV file path
     */
    public function process($xml)
    {
            Mage::log('Start Process', Zend_Log::ALERT, 'sapimport.log');
            Mage::log($xml, Zend_Log::ALERT, 'sapimport.log');
//echo 'stopped';
//exit();
        // get the list of extra attributes
        $attr_attributes = array();
        foreach (Mage::getResourceModel('eav/entity_attribute_collection')
           ->setEntityTypeFilter(4)
           ->addFieldToFilter('attribute_code',array(array('like'=>'attr%'),array('like'=>'atr%')))
            as $attribute){
                $attr_attributes[] = $attribute;
        }

        $rows = array();

        // Collect the base attribute set we'll be using for the CSV file
        $attributes = $this->getProductAttributes();

        Mage::log('Got Attributes', Zend_Log::ALERT, 'sapimport.log');


        //$xml = simplexml_load_string($xml);
        $xml = new SimpleXMLElement($xml);
        //$items = $xml->xpath('//ListItemTestResult');
        $items = $xml->product;

        // Get only my results
        if (count($items)) {
        Mage::log('Have Some Items', Zend_Log::ALERT, 'sapimport.log');
            // Create the header row
            $rows[] = array_keys($attributes);
//            var_dump($rows);
//            exit;

            // base CSV header
            $updates_csv_header = array('sku',
//              'price',
//              '_store',
//                'is_in_stock',
                'qty' ,'weight',
            'custom_stock_status',
            'preorder_calender'
                );
            // add extra attributes names
            $attributes_values = array();
            //foreach ($attr_attributes as $attr) $updates_csv_header[] = $attr->getAttributeCode();

            $updates[] = $updates_csv_header;

            // Update the values that need updating
            //$this->getRealCSVValues();

            // Now insert each item as a row or update the products
            foreach ($items as $item) {

        Mage::log($item->row->ItemCode, Zend_Log::ALERT, 'sapimport.log');
                $item = $item->row;
                $itemPrices = null;//$item->Prices->children();

                // Find out if it already exists or not
                $productId = Mage::getModel('catalog/product')->getIdBySku($item->ItemCode);

                //if ($attribute->getFrontend()->getValue($_product) !== 'Yes') continue;
                //$productId = false;
                if ($productId) {

                    // get product to read extra attributes
                    $_product = Mage::getModel('catalog/product')->load($productId);

                    // Update product price and stock levels
                    $this->updateProduct($productId, $item);

                    $update_line = array();
                    //$updates[] = $this->getUpdateProductRow($attributes, $item, $itemPrices);
                    // base CSV update line
                    //$update_line = $this->getUpdateProductRow($attributes, $item, $itemPrices);
                    // keep visibility
                    //$update_line[] = $_product->getResource()->getAttribute('visibility')->getDefaultValue();
                    // add extra attributes
                    //foreach ($attr_attributes as $attr) $update_line[] = $attr->getFrontend()->getValue($_product);
                    //$updates[] = $update_line;


                } else {
                    Mage::log('Didnt find product', Zend_Log::ALERT, 'sapimport.log');
                    //$rows[] = $this->getNewProductRow($attributes, $item, $itemPrices);

                    //$this->getPrice($ccode, $itemPrices), // price

                }
            }
        } else {

        Mage::log('No Items', Zend_Log::ALERT, 'sapimport.log');
        }

        // Any new files to import?
        if (count($rows) > 1) {

            // Write CSV data to file
            $path = $this->writeCsv($rows);

            // Import CSV
            //$path = Mage::getBaseDir() . '/var/import/test2.csv';
            $this->importFromCsv($path);
        }

        // Any files to update?
        if (count($updates) > 1) {

        Mage::log('Have Updates', Zend_Log::ALERT, 'sapimport.log');
            // Write CSV data to file
            $path = $this->writeCsv($updates, true);


            // Import CSV
            $this->importFromCsv($path);
        } else {

            Mage::log('Have No Updates', Zend_Log::ALERT, 'sapimport.log');
        }

        return true;
    }

    /**
     *
     * @param type $attributes
     * @param type $item
     * @param type $itemPrices
     * @return type
     */
    protected function getNewProductRow($attributes, $item, $itemPrices)
    {
        // For each attribute, either put it in or get the correct value and
        // then put it in

        // Check if is in stock
        $inStock = false;
        foreach ($item->Warehouses as $warehouse) {
            if ((string) $warehouse->Warehouse->Available == 'True') {
                $inStock = true;
            }
            $qty = (string) $warehouse->Warehouse->QuantityAvailable;
            //$qty = round((string) $warehouse->Warehouse->QuantityAvailable);
        }

        $itemImport = array();
        foreach ($attributes as $attribute => $value) {
            switch ($attribute) {
                case 'sku':
                    $itemImport[] = ((string) $item->ItemCode);
                    break;
                //case 'price':
                //    $itemImport[] = $this->getPrice('ausales', $itemPrices);
                    break;
                case 'weight':
                    $itemImport[] = ((string) $item->Weight)? ((string) $item->Weight) : '0';
                    break;
                case 'name':
                    $itemImport[] = ((string) $item->ItemName)?((string) $item->ItemName) : ((string) $item->ItemCode);
                    break;
                case 'qty':
                    $itemImport[] = $qty;
                    break;
                /**
                 * @todo make dynamic
                 */
                case 'custom_stock_status':
                break;
                case 'preorder_calender':
                break;
                case 'description':
                case 'short_description':
                    $itemImport[] = ($value != '') ? $value : '...';
                    break;
                case 'is_in_stock':
                    $itemImport[] = ($inStock) ? '1' : '0';
                    break;
                default:
                    // Just import the value
                    $itemImport[] = $value;
            }
        }
        return $itemImport;
    }

    protected function getUpdateProductRow($attributes, $item, $itemPrices)
    {
        // For each attribute, either put it in or get the correct value and
        // then put it in
        //
        // Check if is in stock
        $weight = ((string) $item->Weight);
        $cbm = ((string) $item->CBM);
        if($cbm>$weight)
            $weight = $cbm;

        if(!$weight) {
            $weight=0;
        }
//        $itemUpdate = array();
//        foreach ($attributes as $attribute => $value) {
//            switch ($attribute) {
//                case 'sku':
                    $itemUpdate[] = ((string) $item->ItemCode);
//                    break;
//                case 'price':
                  //  $itemUpdate[] = $this->getPrice('ausales', $itemPrices);
//                    $itemUpdate[] = '1';
//                    break;
//                case 'is_in_stock':
//                    $itemUpdate[] = ($inStock) ? '1' : '0';

                    $itemUpdate[] = (string) intval($item->Stock);
                    $itemUpdate[] = $weight;
                    $U_INE_CustStockStat = (string) $item->U_INE_CustStockStat;
                    $U_INE_CustStockStat = $U_INE_CustStockStat=='-'?'':$U_INE_CustStockStat;
                    //$U_INE_CustStockStat = $U_INE_CustStockStat==''?'':$U_INE_CustStockStat;
                    $itemUpdate[] = $U_INE_CustStockStat;
                    $ShipDate = '';
                    if($U_INE_CustStockStat=='Expected shipment date:' and preg_match('/^([0-9]{4})([0-9]{2})([0-9]{2})/',(string) $item->ShipDate,$matches)) {
                        $ShipDate = $matches[3].'/'.$matches[2].'/'.$matches[1];
                    }
                    $itemUpdate[] = $ShipDate;
//                    $itemUpdate[] =$weight;
//                    break;
//                case 'qty':
//                    $itemUpdate[] = (string) $item->ItemName;
//                    break;
//                case 'name':
//                    $itemImport[] = (string) $item->ItemName;
//                    break;
                /**
                 * @todo make dynamic
                 */
//                case 'description':
//                case 'short_description':
//                    $itemImport[] = ($value != '') ? $value : '...';
//                    break;
//                default:
//                    // Just import the value
//                    $itemImport[] = $value;
//            }
//        }
        return $itemUpdate;
    }

    /**
     *
     *
     * @param string $storeCode
     * @param array $itemPrices
     * @return type
     */
    protected function getPrice($storeCode, $itemPrices)
    {
        // We make sure the store and website codes correspond to the currency
        // codes we get through the SAP web service
        foreach ($itemPrices as $price) {
            if ((string) $price['Country'] === strtoupper($storeCode)) {
                return (string) $price;
                break;
            }
        }
        return '0';
    }

    /**
     * Updates previously imported product price and stock level
     *
     * @todo Add error handling
     *
     * @param integer $productId
     * @param SimpleXMLElement $item
     */
    protected function updateProduct($productId, $item)
    {
        $itemPrices = $item->Prices->children();

        // Update product prices and stock level
        // Create product object
        $product = Mage::getModel('catalog/product');
        // Load product using product id
        $product->load($productId);



        $weight = ((string) $item->Weight);
        $cbm = ((string) $item->CBM);
        if($cbm>$weight)
            $weight = $cbm;

        if(!$weight) {
            $weight=0;
        }



        $U_INE_CustStockStat = (string) $item->U_INE_CustStockStat;
        $U_INE_CustStockStat = $U_INE_CustStockStat=='-'?'':$U_INE_CustStockStat;
        //$U_INE_CustStockStat = $U_INE_CustStockStat==''?'':$U_INE_CustStockStat;

        $ShipDate = '';
        if($U_INE_CustStockStat=='Expected shipment date:' and preg_match('/^([0-9]{4})([0-9]{2})([0-9]{2})/',(string) $item->ShipDate,$matches)) {
            $ShipDate = $matches[3].'/'.$matches[2].'/'.$matches[1];
        }



        // Get product's general info such price, status, description
        $productInfoData = $product->getData();
        //$productInfoData = array();
        /*
                'qty' ,'weight',
            'custom_stock_status',
            'preorder_calender'
            */
        $productInfoData['weight'] = $weight;
        $productInfoData['preorder_calender'] = $ShipDate;

        $attr = $product->getResource()->getAttribute('custom_stock_status');
        if ($attr->usesSource()) {
            $atid = $attr->getSource()->getOptionId($U_INE_CustStockStat);
            $productInfoData['custom_stock_status'] = $atid;
        }
        // Update general info using new data


        // Then set product's general info to update
        $product->setData($productInfoData);

        // Get product's stock data such quantity, in_stock etc
        $stockData = $product->getStockData();
        //$stockData = array();

        // Update stock data using new data
        // @todo get qty from XML
        $stockData['qty'] = (string) intval($item->Stock);
        //$stockData['is_in_stock'] = 1;


        // Then set product's stock data to update
        $product->setStockData($stockData);

        // Call save() method to save your product with updated data
        $product->save();
        switch((string)$item->Disabled) {
            case 'tYES':
                Mage::getModel('catalog/product_status')->updateProductStatus($product->getId(), 0, Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
            break;
            case 'tNO':
                Mage::getModel('catalog/product_status')->updateProductStatus($product->getId(), 0, Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
            break;
        }
/*
        // And non default currency prices
        //foreach (Mage::app()->getWebsites() as $website) {
        //foreach ($this->websites as $website) {
            // Iterate stores as configured within this website
            //foreach ($website->getStores() as $store) {
            foreach ($this->stores as $store) {
                // Now add rows for each non default store
                switch ($store->getCode()) {
                    case 'default': // Skip, already in the basic update
                        break;
                    case 'cyt1': // Skip - Don't update test
                        break;
                    default:
                        // Get product with store scope set
                        $product = Mage::getModel('catalog/product')
                            ->setStoreId($store->getId())
                            ->load($productId);
                        // Get data
                        $productInfoData = $product->getData();
                        // Update price
                        $productInfoData['price'] = $this->getPrice($store->getCode(), $itemPrices);
                        // Write data back to object
                        $product->setData($productInfoData);
                        // Save
                        $product->save();
                }
            }
        //}
        */
    }

    /**
     * Returns product attributes used in the CSV as column headers
     *
     * NOTE: order here is important. The additional rows for prices for non
     * default currencies are counting on the first 4 values to be as below.
     *
     * @return array
     */
    protected function getProductAttributes()
    {
        return array(
            // DO NOT MODIFY UNLESS YOU KNOW WHAT YOU ARE DOING!
            'sku' => '',
            //'price' => 'AU',
            '_store' => '', // '' for default store
            '_product_websites' => 'base',
            // END DO NOT MODIFY UNLESS YOU KNOW WHAT YOU ARE DOING!

            '_attribute_set' => 'Default',
            '_type' => 'simple',
            '_category' => '',
            '_root_category' => 'Unlisted Products',
            'status' => '2', // Disabled
            //'visibility' => '4', // "Catalog, Search"
            //'tax_class_id' => '2', // "Taxable Goods"
            //'description' => '...',
            //'short_description' => '...',
            'custom_stock_status' => '',
            'preorder_calender' => '',
            'is_in_stock' => 1,
            'qty' => '', // @todo get from XML
            'name' => '',
            'product_type_id' => 'simple',
            'weight' => 0
        );
    }

    /**
     * Writes array of products to a CSV file
     *
     * @param array $rows First row are column headers, following product rows
     * @return string Path to CSV file
     */
    protected function writeCsv($rows, $update = false)
    {

        // File path for temporary file
        $path = Mage::getBaseDir() . '/var/import/';
        $file = date('Ymd_His') . '_product-list.csv';
        Mage::log('Writing File: '. $path.$file, Zend_Log::ALERT, 'sapimport.log');
        if ($update == true) $file = date('Ymd_His') . '_product-updates.csv';

        // Create var/import directory if not exist yet
        if (!is_dir($path)) mkdir($path);

        // Open the socket
        $handle = fopen($path.$file, 'w+');

        // Validate handle
        if ($handle === false) {
            Mage::log('Failed to create CSV file: '. $path.$file, Zend_Log::ALERT, 'sapimport.log');
            // Notify admin contact
            Mage::helper('inecom_sap/data')->notify('Failed to create CSV file: '. $path.$file , 'SAP order update unable to create CSV to import products');
            return false;
        }

        // Write line by line
        foreach ($rows as $line=>$row) {

            if ($line > 0) {
                foreach ($row as $key => $value) {
                     $row[$key] = $value."#@ @#";
                }
            }
            fputcsv($handle, $row);
        }

        // Close handle
        fclose($handle);
        $contents = file_get_contents($path.$file);
        $contents = str_replace("#@ @#", "", $contents);
        file_put_contents($path.$file, $contents);

        // Give it the correct permissions
        //chmod($fileLocation, 0666);

        return $path.$file;
    }

    /**
     * Does the actual import using the Import model of the importexport module
     *
     * @param string $path  Path to CSV file
     */
    public function importFromCsv($path)
    {
        /** @var $import Mage_ImportExport_Model_Import */
        $import = Mage::getModel('importexport/import');

        // Inform importer what type of entity we'll be processing
        $import->setData(array(
            'entity' => 'catalog_product'
        ));

//        try {
//        } catch (Exception $e) {
//        }

        // Validate CSV before import
        //$validationResult = $import->validateSource($path);

//        // Handle failed import
//        if ($validationResult == false) {
//            // Get validation error messages so we can log and email them
//            $messages = $import->getOperationResultMessages($validationResult);
//            Mage::log($messages, Zend_Log::ALERT, 'sapimport.log');
//            // Notify admin contact
//            Mage::helper('inecom_sap/data')->notify(print_r($messages, true));
//
//            return false;
//        }
        $notices = '';
        try {

            //$import = Mage::getModel('importexport/import');
            $validationResult = $import->validateSource($path);

            if (!$import->getProcessedRowsCount()) {
                $errors[] = 'File does not contain data. Please upload another one';
            } else {
                if (!$validationResult) {
                    if ($import->getProcessedRowsCount() == $import->getInvalidRowsCount()) {
                        $errors[] = 'File is totally invalid. Please fix errors and re-upload file';
                    } elseif ($import->getErrorsCount() >= $import->getErrorsLimit()) {
                        $errors[] = sprintf('Errors limit (%d) reached. Please fix errors and re-upload file', $import->getErrorsLimit());
                    } else {
                        if ($import->isImportAllowed()) {
                            $errors[] = 'Please fix errors and re-upload file or simply press "Import" button to skip rows with errors';
                        } else {
                            $errors[] = 'File is partially valid, but import is not possible';
                        }
                    }
                    // errors info
                    foreach ($import->getErrors() as $errorCode => $rows) {
                        $error = $errorCode . ' ' . $this->__('in rows:') . ' ' . implode(', ', $rows);
                        $errors[] = $error;
                    }
                } else {
                    if ($import->isImportAllowed()) {
                        $errors[] = 'File is valid! To start import process press "Import" button';
                    } else {
                        $errors[] = 'File is valid, but import is not possible';
                    }
                }

                $notices = $import->getNotices();
                $notices[] = sprintf('Checked rows: %d, checked entities: %d, invalid rows: %d, total errors: %d',
                        $import->getProcessedRowsCount(), $import->getProcessedEntitiesCount(),
                        $import->getInvalidRowsCount(), $import->getErrorsCount()
                );
            }
        } catch (Exception $e) {
            // Log error
            Mage::log('import error creating product CSV: '.$path.' - '.$e->getMessage(), Zend_Log::ALERT, 'sapimport.log');
            // Notify admin contact
            Mage::helper('inecom_sap/data')->notify($e->getMessage(), 'SAP Exception while creating product CSV!');
        }

        // Now attempt import
        try {
            $import->importSource();
            //$import->invalidateIndex();

            Mage::log('CSV file imported succesfully: '. $path, Zend_Log::INFO, 'sapimport.log');

        } catch (Exception $e) {

            // Log & notify phpdev via email
            Mage::log('import error importing product CSV: '.$path.' - '.$e->getMessage(), Zend_Log::ALERT, 'sapimport.log');

            // Notify admin contact
            Mage::helper('inecom_sap/data')->notify("Importing: $path\n".$e->getMessage(), 'SAP Exception while importing product CSV!'."\n\n".print_r($notices,true)."\n\n".print_r($errors,true));
        }
    }
}

