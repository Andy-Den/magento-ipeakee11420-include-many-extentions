<?php

/**
 * Override Milandirect_Review fix problem on date created
 *
 * @category  Milandirect
 * @package   Milandirect_Review
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2016 Balance
 */
class Milandirect_Review_Model_Resource_Review extends Balance_Varnish_Model_Resource_Review
{
    /**
     * Perform actions before object save
     *
     * @param Varien_Object $object review object
     * @return Mage_Review_Model_Resource_Review
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $createdAt = $object->getCreatedAt();
        if (!empty($createdAt)) {
            try {
                $locale = Mage::app()->getLocale();
                $format = $locale->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
                $time = $locale->date($createdAt, $format)->getTimestamp();
                $createdAt = Mage::getModel('core/date')->gmtDate(null, $time);
                $object->setCreatedAt($createdAt);
            } catch (Exception $e) {
                mage::log('Caught exception: '.  $e->getMessage(). "\n");
            }
        } else {
            $object->setCreatedAt(Mage::getSingleton('core/date')->gmtDate());
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
