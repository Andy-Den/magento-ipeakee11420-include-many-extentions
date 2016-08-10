<?php

/**
 * Balance_Shippingest_IndexController
 *
 * @author Balance Internet
 */
class Balance_Shippingest_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        if($data = $this->getRequest()->getParam('data')){
            $post = array();
            parse_str($data, $post);
            $params = array();
            parse_str($this->getRequest()->getParam('product'), $params);
            $product = Mage::getModel('catalog/product')->load($post['product_id']);
            $inStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getIsInStock();
            if($inStock) {
                $quote = Mage::getModel('sales/quote');
                $quote->getShippingAddress()->setCountryId('AU')
                    ->setPostcode($post['postcode']);
                unset($params['form_key']);
                unset($params['related_product']);
                $params = new Varien_Object($params);
                $quote->addProduct($product,$params);
                $quote->getShippingAddress()->collectTotals();
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->getShippingAddress()->collectShippingRates();
                $rates = $quote->getShippingAddress()->getShippingRatesCollection();
                $rateHtml = '<ul>';
                $rateHtml .='<li>';
                $priceEstimate = 0;
                foreach ($rates as $rate) {
                    $priceEstimate +=$rate->getPrice();
                }
                $rateHtml .=Mage::helper('core')->formatPrice($priceEstimate, false).' Estimated delivery to '.$post['postcode'].' change?';
                $rateHtml.='</li>';
                $rateHtml.='</ul>';
                $result['estimate'] = $rateHtml;
                $this->getResponse()->setBody(Zend_Json::encode($result));
            }else{
                echo 'Product Out of stock';
            }
        }
    }
}
