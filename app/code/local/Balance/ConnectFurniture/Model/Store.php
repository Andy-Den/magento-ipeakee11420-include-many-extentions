<?php
class Balance_ConnectFurniture_Model_Store {
    public function toOptionArray() {
        return Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true);
    }
}