<?php

class Magestore_Megamenu_Model_Core_Url extends Mage_Core_Model_Url
{
    /**
     * Get current store for the url instance
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        /* if (!$this->hasData('store')) { */
            $this->setStore(null);
        /* } */
        return $this->_getData('store');
    }
}