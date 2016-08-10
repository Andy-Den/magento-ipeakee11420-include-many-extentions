<?php
/**
 * Sales Model
 *
 * @category    Exceedz
 * @package     Exceedz_Sales
 */
class Exceedz_Sales_Model_Quote_Item_Abstract extends Mage_Sales_Model_Quote_Item_Abstract
{
    public function getActualPrice()
    {
        $price = $this->_getData('actual_price');
        return $price;
    }

}