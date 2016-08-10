<?php

interface Balance_Varnish_Model_Adapter_Abstract
{
    public function purge($url, $ip, $port);

    public function singleRequest($url, $options);

    public function multiRequest($urls, $options);
}