<?php

class Balance_Exclusion_Model_Mysql4_Exclusion extends Mage_Core_Model_Mysql4_Abstract
{

    public function _construct()
    {
        $this->_init('exclusion/exclusion', 'id');
    }

    public function getTermsList()
    {

        $conn = Mage::getSingleton('core/resource')->getConnection('core_read');
        $termsList = $conn->fetchAll("SELECT * FROM {$this->getTable('exclusion/exclusion')}");

        return $termsList;
    }

}