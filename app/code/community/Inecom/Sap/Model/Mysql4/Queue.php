<?php

class Inecom_Sap_Model_Mysql4_Queue extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('sap/order_queue', 'queue_id');
    }

}