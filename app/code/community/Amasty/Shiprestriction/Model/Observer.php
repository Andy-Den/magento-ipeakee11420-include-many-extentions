<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */
class Amasty_Shiprestriction_Model_Observer
{
    protected $_allRules = null;
    
    public function restrictRates($observer) 
    {
        $request = $observer->getRequest();
        $result  = $observer->getResult();
        $rates = $result->getAllRates();
        if (!count($rates)){
            return $this;
        }
            
        $rules = $this->_getRestrictionRules($request);
        if (!count($rules)){
             return $this;
        }
        
        $result->reset();

        $isEmptyResult = true;
        $lastError     = Mage::helper('amshiprestriction')->__('Sorry, no shipping quotes are available for the selected products and destination');
        $lastRate      = null;
        $lastRule      = null;
        $productRestricted = array();
        $country = false;

        foreach ($rates as $rate){
            $isValid = true;
            foreach ($rules as $rule){
                if ($rule->restrict($rate)){
                    $conditions = $rule->getConditions()->asArray();
                    if (isset($conditions['conditions'])) {
                        foreach($conditions['conditions'] as $condition) {
                            if ($condition['type'] == 'amshiprestriction/rule_condition_product_subselect'){
                                foreach($condition['conditions'] as $subCondition) {
                                    if ($subCondition['type'] == 'salesrule/rule_condition_product' && ($subCondition['operator']) == '()') {
                                        $inRestrictedProducts = $subCondition['value'];
                                        $inRestrictedProducts = explode(',', trim($inRestrictedProducts));
                                        $quoteItems = Mage::getSingleton('checkout/cart')->getQuote()->getAllItems();
                                        foreach($quoteItems as $item) {
                                            $product = $item->getProduct();
                                            $valid = in_array($product->getSku(), $inRestrictedProducts);
                                            if($valid){
                                                $productRestricted[$product->getId()] = $product->getName();
                                            }
                                        }
                                    }
                                }
                            }
                            if ($condition['type'] == 'amshiprestriction/rule_condition_address' &&
                                $condition['attribute'] =='country_id' &&
                                $condition['value'] == Mage::getSingleton('checkout/cart')->getQuote()->getShippingAddress()->getCountryId()){
                                $country = true;
                            }
                        }
                    }

                    if ($productRestricted && $country) {
                        $lastRule = $rule;
                        $lastRate  = $rate;
                        $lastError = '';
                        foreach ($productRestricted as $productName) {
                            $lastError .= "<span>Unfortunately, we don't ship ".$productName." to EU countries. Please deselect the item, if you wish to check out your other items</br></span>" ;
                        }
                        //$lastError = $rule->getMessage();
                        $isValid   = false;
                        break;
                    } else {
                        $lastRule = $rule;
                        $lastRate  = $rate;
                        $lastError = $rule->getMessage();
                        $isValid   = false;
                        break;
                    }
                }
            }
            if ($isValid){
                $result->append($rate);
                $isEmptyResult = false;                    
            }
        }
        if ($isEmptyResult){
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setRule($lastRule);
            $error->setCarrier($lastRate->getCarrier());
            $error->setCarrierTitle($lastRate->getMethodTitle());
            $error->setErrorMessage($lastError);
            Mage::getSingleton('checkout/session')->setErrorShippingMessage($lastError);
            $result->append($error);
        } else {
            Mage::getSingleton('checkout/session')->unsetData('error_shipping_message');
        }
        
        return $this;
    }
   
    protected function _getRestrictionRules($request)
    {
        $all = $request->getAllItems();
        if (!$all){
            return array();
        }
        $firstItem = current($all);

        $address = $firstItem->getAddress();
        if (!$address){
            $quote = $firstItem->getQuote();     
            if (!$quote) { return array(); } // we need it for true order editor

            $address = $quote->getShippingAddress(); 
        }
        $address->setItemsToValidateRestrictions($request->getAllItems());


        //multishipping optimization
        if (is_null($this->_allRules)){
            $this->_allRules = Mage::getModel('amshiprestriction/rule')
                ->getCollection()
                ->addAddressFilter($address)
            ;
            if ($this->_isAdmin()){
                $this->_allRules->addFieldToFilter('for_admin', 1);
            }                
            
            $this->_allRules->load();

            foreach ($this->_allRules as $rule){
                $rule->afterLoad(); 
            }
        }
        
        $hasBackOrders = false;
        foreach ($request->getAllItems() as $item){
            if ($item->getBackorders() > 0 ){
                $hasBackOrders = true;
                break;
            }
        }

	    //remember old
        $subtotal = $address->getSubtotal();
        $baseSubtotal = $address->getBaseSubtotal();
        // set new
        $this->_modifySubtotal($address);


        $validRules = array();
        foreach ($this->_allRules as $rule){
            $valid = $rule->getOutOfStock() ? $hasBackOrders : true;
            /*$valid = true;*/
            if ($valid && $rule->validate($address)){
                $validRules[] = $rule;
            }
        }
        // restore
        $address->setSubtotal($subtotal);
        $address->setBaseSubtotal($baseSubtotal);
        
        return $validRules;                
    } 


    protected function _modifySubtotal($address)
    {
        $subtotal = $address->getSubtotal();
        $baseSubtotal = $address->getBaseSubtotal();

        $includeTax = Mage::getStoreConfig('amshiprestriction/general/tax');
        if ($includeTax){
           $subtotal += $address->getTaxAmount();
           $baseSubtotal += $address->getBaseTaxAmount(); 
        }
        
        $includeDiscount = Mage::getStoreConfig('amshiprestriction/general/discount');
        if ($includeDiscount){
           $subtotal += $address->getDiscountAmount();
           $baseSubtotal += $address->getBaseDiscountAmount(); 
        } 
                 
        $address->setSubtotal($subtotal);
        $address->setBaseSubtotal($baseSubtotal);

	return true;
    }
 
    
    protected function _isAdmin()
    {
        if (Mage::app()->getStore()->isAdmin())
            return true;
        // for some reason isAdmin does not work here
        if (Mage::app()->getRequest()->getControllerName() == 'sales_order_create')
            return true;
            
        return false;
    }        

    
    /**
     * Append rule product attributes to select by quote item collection
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_SalesRule_Model_Observer
     */
    public function addProductAttributes(Varien_Event_Observer $observer)
    {
        // @var Varien_Object
        $attributesTransfer = $observer->getEvent()->getAttributes();

        $attributes = Mage::getResourceModel('amshiprestriction/rule')->getAttributes();
        
        $result = array();
        foreach ($attributes as $code) {
            $result[$code] = true;
        }
        $attributesTransfer->addData($result);
        
        return $this;
    }
     
     /**
     * Adds new conditions
     * @param   Varien_Event_Observer $observer
     */
    public function handleNewConditions($observer)
    {
        $transport = $observer->getAdditional();
        $cond = $transport->getConditions();
        if (!is_array($cond)){
            $cond = array();
        }
        
        $types = array(
            'customer' => 'Customer attributes',
        );
        foreach ($types as $typeCode => $typeLabel){
            $condition           = Mage::getModel('amshiprestriction/rule_condition_' . $typeCode);
            $conditionAttributes = $condition->loadAttributeOptions()->getAttributeOption();
            
            $attributes = array();
            foreach ($conditionAttributes as $code=>$label) {
                $attributes[] = array(
                    'value' => 'amshiprestriction/rule_condition_'.$typeCode.'|' . $code, 
                    'label' => $label,
                );
            }         
            $cond[] = array(
                'value' => $attributes, 
                'label' => Mage::helper('amshiprestriction')->__($typeLabel), 
            );            
        }

        $transport->setConditions($cond);
        
        return $this; 
    }             
    
}