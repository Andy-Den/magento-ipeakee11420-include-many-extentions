<?php

class Balance_Varnish_Model_Adapter_Default implements Balance_Varnish_Model_Adapter_Abstract
{
    protected $processor = null;
    protected $helper = null;

    function __construct()
    {
        if (null == $this->processor) {
            $this->processor = new Varien_Http_Adapter_Curl();
        }
        if (null == $this->helper) {
            $this->helper = Mage::helper('varnish');
        }
    }


    public function purge($url, $ip, $port)
    {
        if (empty($url) || empty($ip)) {
            return false;
        }

        try {
            $parse = parse_url($url);
            $ch = curl_init('http://' . $ip . '/');
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                array(
                    'X-Purge-Regex: ' . $parse['path'],
                    'X-Purge-Host: ' . $parse['host']
                )
            );
            curl_setopt($ch, CURLOPT_PORT, $port);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PURGE');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            $this->helper->debug(
                array(
                    'urls'    => $url,
                    'message' => 'VARNISH PURGED: ' . $url . ' ON ' . $ip . ":" . $port . "\n"
                )
            );
        } catch (Exception $e) {
            $this->helper->debug(
                array(
                    'urls'  => $url,
                    'error' => $e->getMessage()
                )
            );
        }

        return true;
    }

    public function singleRequest($url, $options)
    {
        $url = is_array($url) ? $url : array($url);
        $this->multiRequest($url, $options);
    }

    public function multiRequest($urls, $options)
    {
        $this->processor->multiRequest($urls, $options);
    }
}