<?php

class Balance_Exclusion_Model_Exclusion extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('exclusion/exclusion');
    }

}
