<?php

class Inecom_Sap_Model_Queue extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('sap/queue');
    }

    /**
     * Set created_at parameter
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        $date = Mage::getModel('core/date')->gmtDate();
        if ($this->isObjectNew() && !$this->getCreatedAt()) {
            $this->setCreatedAt($date);
            $this->setUpdatedAt($date);
        } else {
            $this->setUpdatedAt($date);
        }
        return parent::_beforeSave();
    }
}