<?php
/**
 * Exceedz_Checkout_Block_Cart_Item_Renderer_Configurable
 * @author Balance Internet
 * REF: MID-347
 */
class Exceedz_Checkout_Block_Cart_Item_Renderer_Configurable extends Mage_Checkout_Block_Cart_Item_Renderer_Configurable
{
    public function getProductCustomOptions()
    {
        $selectHtml = '<ul class="custom-options">';

        $product = $this->getProduct();

        $store = $product->getStore();
        $selectedProductOptions = $this->getSelectedProductCustomOptions();
        foreach ($product->getOptions() as $_option) {
            $type = $_option->getType();
            $optionValues = $_option->getValues();
            $count = 0;
            foreach ($optionValues as $_value) {
                $count++;
                $htmlValue = $_value->getOptionTypeId();

                $priceStr = $this->_formatPrice(array(
                    'is_percent' => ($_value->getPriceType() == 'percent') ? true : false,
                    'pricing_value' => $_value->getPrice(true)
                ));

                switch($type) {
                    case Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO :

                        $selectHtml .= '<li><input type="'.$type.'" name="cart['.$this->getItem()->getId().'][options]['.$_option->getId().']"'.
                            ' value="' . $htmlValue . '" ' . (in_array($_value->getTitle(), $selectedProductOptions)?'checked':'') .
                            ' price="' . $this->helper('core')->currencyByStore($_value->getPrice(true), $store, false) . '" />' .
                            '<label for="options_'.$_option->getId().'_'.$count.'">'.$_value->getTitle().
                            '</label>&nbsp;<span class="sub-text">('. (empty($priceStr) ? $this->__('Standard') : $priceStr) . ')</span></li>';

                        break;
                }

            }
        }
        $selectHtml .= '</ul>';
        return $selectHtml;
    }
    protected function _formatPrice($value, $flag=true)
    {
        if ($value['pricing_value'] == 0) {
            return '';
        }

        $taxHelper = Mage::helper('tax');
        $store = $this->getProduct()->getStore();

        $sign = '+';
        if ($value['pricing_value'] < 0) {
            $sign = '-';
            $value['pricing_value'] = 0 - $value['pricing_value'];
        }

        $priceStr = $sign;
        $_priceInclTax = $this->getPrice($value['pricing_value'], true);
        $_priceExclTax = $this->getPrice($value['pricing_value']);
        if ($taxHelper->displayPriceIncludingTax()) {
            $priceStr .= $this->helper('core')->currencyByStore($_priceInclTax, $store, true, $flag);
        } elseif ($taxHelper->displayPriceExcludingTax()) {
            $priceStr .= $this->helper('core')->currencyByStore($_priceExclTax, $store, true, $flag);
        } elseif ($taxHelper->displayBothPrices()) {
            $priceStr .= $this->helper('core')->currencyByStore($_priceExclTax, $store, true, $flag);
            if ($_priceInclTax != $_priceExclTax) {
                $priceStr .= ' ('.$sign.$this->helper('core')
                        ->currencyByStore($_priceInclTax, $store, true, $flag).' '.$this->__('Incl. Tax').')';
            }
        }

        if ($flag) {
            $priceStr = '<span class="price-notice">'.$priceStr.'</span>';
        }

        return $priceStr;
    }
    public function getSelectedProductCustomOptions()
    {
        $_options = $this->getOptionList();
        $selectedOptions = array();
        foreach ($_options as $_option) {
            $selectedOptions[] = $_option['value'];
        }
        return $selectedOptions;
    }
    public function getPrice($price, $includingTax = null)
    {
        if (!is_null($includingTax)) {
            $price = Mage::helper('tax')->getPrice($this->getProduct(), $price, true);
        } else {
            $price = Mage::helper('tax')->getPrice($this->getProduct(), $price);
        }
        return $price;
    }
}
