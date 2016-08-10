<?php
class Milandirect_Wishlist_Model_Item extends Mage_Wishlist_Model_Item
{
    public function addToCart(Mage_Checkout_Model_Cart $cart, $delete = false)
    {
        $product = $this->getProduct();
        $storeId = $this->getStoreId();

        if ($product->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
            return false;
        }

        if (!$product->isVisibleInSiteVisibility()) {
            if ($product->getStoreId() == $storeId) {
                return false;
            }
        }

        if (!$product->isSalable()) {
            throw new Mage_Core_Exception(null, self::EXCEPTION_CODE_NOT_SALABLE);
        }


        $buyRequest = $this->getBuyRequest();
        $params = array();
        /**
         * BOF :: Code for Custom Options
         */
        if (!isset($params['options'])) {
            if (count($product->getOptions()) > 0) {
                foreach ($product->getOptions() as $_option) {
                    foreach ($_option->getValues() as $_value) {
                        $params['options'][$_option->getId()] = $_value->getOptionTypeId();
                        break;
                    }
                }
            }
        }

        $selectedCustomOptions = array();
        if (count($product->getOptions())>0) {
            foreach ($product->getOptions() as $_option) {
                if (strcasecmp($_option->getTitle(), 'custom_option_data') == 0) {
                    $params['options'][$_option->getId()] = serialize($params['options']);
                }

                if (array_key_exists($_option->getId(), $params['options'])) {
                    foreach ($_option->getValues() as $_value) {

                        if (in_array($_value->getOptionTypeId(), $params['options'])) {
                            $selectedCustomOptions[] = $_value->getTitle();
                        }
                    }
                }
            }
        }
        /**
         * EOF :: Code for Custom Options
         */
        $params['item'] = $buyRequest->item;
        $params['uenc'] = $buyRequest->uenc;
        $params['form_key'] = $buyRequest->form_key;
        $params['qty'] = $buyRequest->qty;
        $params['product'] = $buyRequest->product;
        $params['product'] = $buyRequest->product;
        $params['related_product'] = $buyRequest->related_product;
        $params['return_url'] = $buyRequest->return_url;
        $params['super_attribute'] = $buyRequest->super_attribute;
        $params['innobyte_product_questions_customer_name'] = $buyRequest->innobyte_product_questions_customer_name;
        $params['innobyte_product_questions_customer_email'] = $buyRequest->innobyte_product_questions_customer_email;
        $params['innobyte_product_questions_visibility'] = $buyRequest->innobyte_product_questions_visibility;
        $params['innobyte_product_questions_content'] = $buyRequest->innobyte_product_questions_content;
        $params =  new Varien_Object($params);
        $cart->addProduct($product, $params);
        if (!$product->isVisibleInSiteVisibility()) {
            $cart->getQuote()->getItemByProduct($product)->setStoreId($storeId);
        }

        if ($delete) {
            $this->delete();
        }

        return true;
    }
}
