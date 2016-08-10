<?php

class Inecom_Sap_Helper_Export extends Mage_Core_Helper_Abstract
{
        const FTPAddrress = '54.66.223.94';
        const FTPUser = 'ineMagento';
        const FTPPassword = '1n3c0mM1l@n';


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
     * Gets product list from Magento
     *
     * @send it to SAP to update
     */
    public function exportProducts()
    {
        
            Mage::log('Starting export', Zend_Log::INFO, 'sapexport.log');
       
      // $store = Mage::getModel('core/store')->load(2); 
      //  $code  = $store->getCode();


      // $store_id = Mage::app()->getStore()->getId();
       // echo "<br/> Current store : " .$code;

 //  $store = Mage::getConfig()->getNode()->stores->{"AU Store"}; 
    //  $storeID = $store->getID();;
     //   $code  = $store->getCode();
      // $store_id = Mage::app()->getStore()->getId();
    //   echo "<br/> Current store : " .$code;
   //      echo "<br/> Current store id : " .$storeID;

      //  foreach (Mage::app()->getWebsites() as $website) {
        //    foreach ($website->getStores() as $store) {
             //   echo "<br/> store details : " .print_r($store);
           // }
      //  }

       // $productAttrs = Mage::getResourceModel('catalog/product_attribute_collection');

       // foreach ($productAttrs as $productAttr) { /** @var Mage_Catalog_Model_Resource_Eav_Attribute $productAttr */
               // echo "<br/> Attribute code :" .$productAttr->getAttributeCode();
           // }
         //   exit();

        $collection = Mage::getModel("catalog/product")->getCollection();
       $collection
       // ->setStoreId(2)
       // ->addStoreFilter(2)
        //->addAttributeToSelect('*')
        ->addAttributeToSelect(array('sku','name','price','special_price','status','custom_stock_status','preorder_calender','weight','short_description','overview_specifications'))   
        // ->setPageSize(1000)
        // ->setCurPage(4)
          ->getSelect();
       // $products->addAttributeToSelect('category_ids');
      //  $products->addAttributeToFilter('status', 1);//optional for only enabled products
      //  $products->addAttributeToFilter('visibility', 4);//optional for products only visible in catalog and search
    
       
     // echo "<br/>Products row count :" .count($collection); 
    
     $rows = array();
      Mage::log('Creating csv for :' .count($collection), Zend_Log::INFO, 'sapexport.log');
    // $count = 0;

     foreach($collection as $product) {
          //print_r($product);
       //  $count += 1;
         $rows[] = $this->getExportRow($product);
       // echo "<br/> Export Line : " + print_r($rows);

        // if($count > 2)
        // {
           //  exit();
        // }
     }           
   

         $path = $this->writeCsv($rows);

   // echo"<br/> Path created : " .$path;
    //FTP to the integration folder
    
    //$this->sendExportFile($path);

    return $path;
    }
   
    public function sendExportFile($path)
    {
     
      Mage::log('Sending CSV via FTP: '.$path, Zend_Log::INFO, 'sapexport.log');
        try{
            $dest ="Export/ExportProduct.csv";

            $connection = ftp_connect('54.66.223.94');

            $login = ftp_login($connection, 'ineMagento', '1n3c0mM1l@n');

            if (!$connection || !$login) { 
                 Mage::log('FTP Login failed: ', Zend_Log::ALERT, 'sapexport.log');
                die('Connection attempt failed!'); }

            ftp_pasv($connection, true);

            $upload = ftp_put($connection, $dest, $path,FTP_ASCII);

            if (!$upload) {  Mage::log('FTP upload error: '. $path, Zend_Log::ALERT, 'sapexport.log'); }

            ftp_close($connection);


      //  $ftp = new Varien_Io_Ftp();
      //  $ftp->open(
     //   array(
            //    'host' => self::FTPAddrress,
            //    'user' => self::FTPUser,
            //    'password' => self::FTPPassword,
           // )
       // );
       // echo '<br/> FTP path to export : ' .$path;

       // $flocal = fopen($path, 'r');
       //  $ftp->write('ExportProduct.csv', $flocal);
       // $ftp->close();
           // Close handle
       // fclose($flocal);
        }
        catch(Exception $e) {
            Mage::log('FTP Error: ' .$e->getMessage(), Zend_Log::ALERT, 'sapexport.log');
        }
    }


    /**
     * Writes array of products to a CSV file
     *
     * @param array $rows First row are column headers, following product rows
     * @return string Path to CSV file
     */
    protected function writeCsv($rows)
    {

        // File path for temporary file
        $path = Mage::getBaseDir() . '/var/export/';
        $file = date('Ymd_His') . '_product-export.csv';
            
        // Create var/import directory if not exist yet
        if (!is_dir($path)) mkdir($path);

        // Open the socket
        $handle = fopen($path.$file, 'w+');

        // Validate handle
        if ($handle === false) {
            Mage::log('Failed to create CSV file: '. $path.$file, Zend_Log::ALERT, 'sapexport.log');
            // Notify admin contact
           // Mage::helper('inecom_sap/data')->notify('Failed to create CSV file: '. $path.$file , 'SAP update unable to create CSV to export products');
            return false;
        }

        //$csvHeader = array('sku','name','price','specialprice','status''custom_stock_status',
        //'preorder_calender','weight','qty','is_in_stock','short_description',
        //'overview_specifications','_store');
        $csvHeader = array('sku','name','price','special_price','status','custom_stock_status',
          'preorder_calender','weight','qty','is_in_stock','short_description',
            'overview_specifications');
        fputcsv( $handle, $csvHeader,"|");

        // Write line by line
        foreach ($rows as $line=>$row) {

            if ($line > 0) {
                foreach ($row as $key => $value) {
                     $row[$key] = $value."#@ @#";
                }
            }
            fputcsv($handle, $row,"|");
        }

        // Close handle
        fclose($handle);
        $contents = file_get_contents($path.$file);
        $contents = str_replace("#@ @#", "", $contents);
        $contents = str_replace('"', '', $contents);
        file_put_contents($path.$file, $contents);
         Mage::log('CSV file created: '. $path.$file, Zend_Log::INFO, 'sapexport.log');
        return $path.$file;
    }
     protected function getExportRow($product)
    {
        
        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);

        //$storeIds = $product->getStoreIds();

        $storeId = $product->getStoreIds();
       //echo "</br></br> Current store ids are : " + print_r($storeId);
       //echo "</br> Current store code is : " .$product->getStore()->getCode();
      // $psku = $product->getSku();
       //  echo "</br> Product Code : " .$psku; 
       //if (in_array('3', $storeId)) {
      //  echo "</br> Uk store found for : " .$psku; 
      // }

        if (in_array('3', $storeId)){
        
            $taxPercent = 6;
        } else {
            $taxPercent = 11;
        }
         // echo " Tax Percent is : " .$taxPercent;

         $currentPrice = number_format($product->getPrice()- $product->getPrice()/$taxPercent, 2,".","");
         $specPrice = number_format($product->getSpecialPrice()- $product->getSpecialPrice()/$taxPercent, 2,".","");

         $export_Line = array();
        $export_Line[] = $product->getSku();
        $export_Line[] = ((string) $product->getName());
        $export_Line[] =  $currentPrice;
        $export_Line[] =  $specPrice;
        $export_Line[] = ((string) $product->getStatus());
        $export_Line[] = ((string) str_replace(array("\r", "\n"), '',$product->getAttributeText('custom_stock_status')));//getCustomStockStatus());
        $export_Line[] = ((string) $product->getPreorderCalender());
        $export_Line[] = ((string) $product->getWeight());
        $export_Line[] = ((string) $stock->getQty());
        $export_Line[] = ((string) $stock->getIsInStock());
        $export_Line[] = ((string) str_replace(array("\r", "\n"), '',$product->getShortDescription()));
        $export_Line[] = $this->getSpec($product->getOverviewSpecifications());
       // $export_Line[] = ((string) $product->getStore());
   
        return $export_Line;
    }

    protected function getSpec($string)
    {
        $string = ' ' . $string;
        $ini = strpos($string, '<ul>');
        if ($ini == 0) return '';
        $len = strpos($string, '</ul>', $ini) - $ini;
        $len += strlen('</ul>');
        return str_replace(array("\r", "\n"), '',substr($string, $ini, $len));
    }
}

