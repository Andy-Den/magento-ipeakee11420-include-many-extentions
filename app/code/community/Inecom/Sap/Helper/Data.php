<?php

class inecom_Sap_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Instantiates Zend rest client using global configuration
     * and handles authentication
     * @return void
     */
    public function initHttpClient()
    {
        $config = array(
            'adapter' => 'Zend_Http_Client_Adapter_Curl',
            'curloptions' => array(
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST=> false,
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_TIMEOUT => 300,
            )
        );
        $client = new Zend_Http_Client(Mage::getStoreConfig('sap/settings/rest_uri').Mage::getStoreConfig('sap/settings/rest_uri_path'),$config);

        $client->setAuth(Mage::getStoreConfig('sap/settings/rest_username'), Mage::getStoreConfig('sap/settings/rest_password'), Zend_Http_Client::AUTH_BASIC);

        return $client;
    }

    /**
     *
     * @param string $data
     */
    public function notify($data, $title = 'SAP Observer CRON job failure')
    {
        $contacts = array(
            Mage::getStoreConfig('sap/notifications/name') => Mage::getStoreConfig('sap/notifications/email'),
            'Inecom' => 'cloud_aws@inecom.com.au'
        );
        // Mage::log('Sending email', Zend_Log::INFO, 'sap-notify.log');
         try {
        Mage::getModel('core/email_template')
            ->loadDefault('sap_notification_email')
            ->setSenderName(Mage::getStoreConfig('trans_email/ident_custom1/name'))
            ->setSenderEmail(Mage::getStoreConfig('trans_email/ident_custom1/email'))
            ->send(
                array_values($contacts),
                array_keys($contacts),
                array(
                    'result' => $data,
                    'title' => $title
                )
            );
         } catch (Exception $e) {
             $resulttxt = print_r($e->getMessage(), true);
             Mage::log('Error : ' .$resulttxt, Zend_Log::INFO, 'sap-notify.log');
         }
    }
}