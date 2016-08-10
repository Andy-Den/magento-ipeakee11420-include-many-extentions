<?php

class Inecom_Sap_Model_Observer
{
    /**
     * URL path the REST service is listening on
     * @var string
     */
    protected $path;

    /**
     * Daily product sync with SAP
     * This method is called from cron process, cron is working in UTC time
     *
     * @param   Mage_Cron_Model_Schedule $observer
     * @return  Inecom_Sap_Model_Observer
     */
    public function syncProducts($observer)
    {
        // Fire up an instance of Zend_Rest_client
        //$client = $this->initRestClient();
        $helper = Mage::helper('inecom_sap/import');

        // Get the data
        $xml = $helper->getProducts();

        // Process xml
        $helper->process($xml);

        // Re-build affected indexes
        $process = Mage::getModel('index/process')->load(2);
        $process->reindexAll();
        $process = Mage::getModel('index/process')->load(6);
        $process->reindexAll();
        $process = Mage::getModel('index/process')->load(7);
        $process->reindexAll();

        return $this;
    }


    public function exportProducts($observer)
    {
        ini_set('memory_limit', '2048M');
        // Fire up an instance of Zend_Rest_client
        //$client = $this->initRestClient();
        $helper = Mage::helper('inecom_sap/export');
        Mage::log("Starting Product export creation :", Zend_Log::INFO, 'sap.log');
        // Get the data
        $path = $helper->exportProducts();
       Mage::log("Exporting csv file to : " .$path, Zend_Log::INFO, 'sap.log');
        // Process xml
        $helper->sendExportFile($path);
         Mage::log("Export completed", Zend_Log::INFO, 'sap.log');
        return $this;
    }

    /**
     * Informs SAP web service of new orders
     *
     * @param Mage_Cron_Model_Schedule $observer
     * @return Inecom_Sap_Model_Observer
     */
    public function pushOrders($observer)
    {
        // Get helper
        $helper = Mage::helper('inecom_sap/order');

        // Prepare queue so we know what orders to report back to SAP
        $helper->populateQueue();

        // Now process the queue
        $helper->processQueue();

        return $this;
    }

    /**
     * Triggers failed order reporting
     *
     * @param Mage_Cron_Model_Schedule $observer
     * @return Inecom_Sap_Model_Observer
     */
    public function reportErrors($observer)
    {
        // Get helper
        $helper = Mage::helper('inecom_sap/order');

        $helper->reportFailedOrders();

        return $this;
    }

    /**
     * Prompts SAP REST service to deliver any available order updates
     *
     * @param Mage_Cron_Model_Schedule $observer
     * @return Inecom_Sap_Model_Observer
     */
    public function checkForOrderUpdates($observer)
    {
        // Get helper
        $helper = Mage::helper('inecom_sap/update');

        $xml = $helper->getUpdates();

        $helper->processUpdates($xml);

        return $this;
    }

}