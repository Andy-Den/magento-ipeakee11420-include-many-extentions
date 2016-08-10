<?php
/**
 * Review resource model
 *
 * @category    Exceedz
 * @package     Exceedz_Review
 */
class Exceedz_Review_Model_Resource_Review extends Mage_Review_Model_Resource_Review
{
    /**
     * Perform actions before object save
     *
     * @param Varien_Object $object
     * @return Mage_Review_Model_Resource_Review
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $createdAt = $object->getCreatedAt();
        if(!empty($createdAt)) {
            try {
                $parseDate = explode(' ', $createdAt);
                $date = Mage::app()->getLocale()->date($createdAt, Zend_Date::DATE_MEDIUM); 
                $createdAt = $date->toString('YYYY-MM-dd') . ' ' . $parseDate[1];
                $object->setCreatedAt($createdAt);
            } catch (Exception $e) {
                mage::log('Caught exception: '.  $e->getMessage(). "\n");
            }
        } else {
            $object->setCreatedAt(Mage::getSingleton('core/date')->gmtDate()); 
        }
        
        if (!$object->getId()) {            
        }
        if ($object->hasData('stores') && is_array($object->getStores())) {
            $stores = $object->getStores();
            $stores[] = 0;
            $object->setStores($stores);
        } elseif ($object->hasData('stores')) {
            $object->setStores(array($object->getStores(), 0));
        }
        return $this;
    }    
}
