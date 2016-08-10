<?php

/**
 * Shipping estimate
 *
 * @category  Milandirect
 * @package   Milandirect_Shippingest
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */
class Milandirect_Shippingest_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Estimate action
     *
     * @return Mage_Core_Controller_Varien_Action|void
     */
    public function indexAction()
    {
        if ($data = $this->getRequest()->getParam('data')) {
            $post = array();
            parse_str($data, $post);
            $params = array();
            parse_str($this->getRequest()->getParam('product'), $params);
            $product = Mage::getModel('catalog/product')->load($post['product_id']);
            $item = Mage::getModel('sales/quote_item')->setProduct($product)->setQty(1);
            $store = Mage::app()->getStore();

            $request = Mage::getModel('shipping/rate_request')
                ->setAllItems(array($item))
                ->setDestCountryId(Mage::getStoreConfig('general/country/default'))
                ->setDestPostcode($post['postcode'])
                ->setPackageValue($product->getFinalPrice())
                ->setPackageValueWithDiscount($product->getFinalPrice())
                ->setPackageWeight($product->getWeight())
                ->setPackageQty(1)
                ->setPackagePhysicalValue($product->getFinalPrice())
                ->setFreeMethodWeight(0)
                ->setStoreId($store->getId())
                ->setWebsiteId($store->getWebsiteId())
                ->setFreeShipping(0)
                ->setBaseCurrency($store->getBaseCurrency())
                ->setBaseSubtotalInclTax($product->getFinalPrice());

            $model = Mage::getModel('shipping/shipping')->collectRates($request);
            $rateHtml = '<ul>';
            $rateHtml .='<li>';
            $priceEstimate = 0;
            $arrayRate = array();
            foreach ($model->getResult()->getAllRates() as $rate) {
                $arrayRate[] = $rate->getCarrier();
                if ($rate->getPrice()>$priceEstimate) {
                    $priceEstimate = $rate->getPrice();

                }
            }

            $rateHtml .= Mage::helper('core')->formatPrice($priceEstimate, false).
                ' Estimated delivery to '.$post['postcode'].
                ' <span class="change-delivery" onclick="changePostcode()">'.$this->__('change').'</span>?<br/>'.
                '<span>'.$this->__('(Estimated shipping costs are based on the currently viewed product.)').'</span>';
            $rateHtml.='</li>';
            $rateHtml.='</ul>';
            /* Allow zero shipping amount since for some products we offer free shipping offer */
            if (!in_array('tablerate', $arrayRate)) {
                $rateHtml = '<ul class="messages"><li class="error-msg"><ul><li><span>'.
                    $this->__('An invalid postcode has been entered, please try again').
                    '</span></li></ul></li></ul>';
            }
            $result['estimate'] = $rateHtml;
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }
}
