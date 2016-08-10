<?php
/**
 * Remove html tag of custom status
 *
 * @package    Milandirect_Stockstatus
 * @author     Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2016 Balance
 */
class Milandirect_Stockstatus_Helper_Data extends Amasty_Stockstatus_Helper_Data
{
    public function getCustomStockStatusText($product) {
       if($this->_getCustomStockStatusText($product)!= false){
           return $this->_getCustomStockStatusText($product);
       }else{
           return '';
       }
    }
    public function getPreorderCalender($product) {
        if($product->getPreorderCalender()){
            return date('d/m/Y',strtotime($product->getPreorderCalender()));
        }else{
            return '';
        }
    }
}
