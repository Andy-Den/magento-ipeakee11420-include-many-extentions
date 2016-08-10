<?php

class Balance_Exclusion_Model_Mysql4_Exclusion_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('exclusion/exclusion');
    }
}