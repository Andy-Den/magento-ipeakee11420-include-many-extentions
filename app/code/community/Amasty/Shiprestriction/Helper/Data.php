<?php
/**
* @copyright Amasty.
*/ 
class Amasty_Shiprestriction_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getAllGroups()
    {
        $customerGroups = Mage::getResourceModel('customer/group_collection')
            ->load()->toOptionArray();

        $found = false;
        foreach ($customerGroups as $group) {
            if ($group['value']==0) {
                $found = true;
            }
        }
        if (!$found) {
            array_unshift($customerGroups, array('value'=>0, 'label'=>Mage::helper('salesrule')->__('NOT LOGGED IN')));
        } 
        
        return $customerGroups;
    }
    
    public function getAllCarriers()
    {
        $carriers = array();
        foreach (Mage::getStoreConfig('carriers') as $code=>$config){
            if (!empty($config['title'])){
                $carriers[] = array('value'=>$code, 'label'=>$config['title']);
            }
        }  
        return $carriers;      
    }
    
    public function getStatuses()
    {
        return array(
                '1' => Mage::helper('salesrule')->__('Active'),
                '0' => Mage::helper('salesrule')->__('Inactive'),
            );       
    }
    
    public function getAllDays()
    {
        return array(
            array('value'=>'7', 'label' => $this->__('Sunday')),
            array('value'=>'1', 'label' => $this->__('Monday')),
            array('value'=>'2', 'label' => $this->__('Tuesday')),
            array('value'=>'3', 'label' => $this->__('Wednesday')),
            array('value'=>'4', 'label' => $this->__('Thursday')),
            array('value'=>'5', 'label' => $this->__('Friday')),
            array('value'=>'6', 'label' => $this->__('Saturday')),
        );             
    }    
}