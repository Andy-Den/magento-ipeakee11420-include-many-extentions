<?php
/**
 * @author  Balance Internet
 */
class Balance_Addressbypostcode_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getAddressByPostCode($pcode)
    {
        $collection = Mage::getModel('addressbypostcode/address')->getCollection()->addFieldToFilter('pcode', $pcode);
        if ($collection->getSize() > 0) {
            $addressInfo = array("pcode"  => $pcode,
                                 "state"  => $collection->getFirstItem()->getState(),
                                 "suburb" => array()
            );
            foreach ($collection as $info) {
                $addressInfo["suburb"][] = ucwords(strtolower($info->getLocality()));
            }
        } else {
            $addressInfo = false;
        }

        return $addressInfo;
    }
}