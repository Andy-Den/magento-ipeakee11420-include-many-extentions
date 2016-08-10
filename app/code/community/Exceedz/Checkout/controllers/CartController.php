<?php
require_once 'Mage/Checkout/controllers/CartController.php';
/**
 * Shopping cart controller
 */
class Exceedz_Checkout_CartController extends Mage_Checkout_CartController
{
    protected $_redirectUrl = '';

    /**
     * Add product to shopping cart action
     *
     * @return Mage_Core_Controller_Varien_Action
     * @throws Exception
     */
    public function addToCartAction()
    {
        // if (!$this->_validateFormKey()) {
        //     $this->_goBack();
        //     return;
        // }

        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->_goBack();
                return;
            }

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                    $this->_getSession()->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
            $this->_goBack();
        }
    }

    /**
     * Add product to shopping cart action
     */
    public function addAction()
    {
        $userAgent = Mage::helper('shippingprotection/data')->getTargetPlatform();
        if ($userAgent == 'mobile')
        {
            return $this->addToCartAction();
        }

        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();

        $this->_getSession()->setRedirectUrl($this->_getRefererUrl());

        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }
            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');
            $optionPrice = $this->getRequest()->getParam('option-price');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->_goBack();
                return;
            }

            /**
            * BOF :: Code for Custom Options
            */
            if(!isset($params['options'])) {
                if(count($product->getOptions()) > 0){
                    foreach($product->getOptions() as $_option) {
                        foreach($_option->getValues() as $_value) {
                            $params['options'][$_option->getId()] = $_value->getOptionTypeId();
                            break;
                        }
                    }
                }
            }

            $selectedCustomOptions = array();
            if(count($product->getOptions())>0){
                foreach ($product->getOptions() as $_option) {
                    if(strcasecmp($_option->getTitle(),'custom_option_data') == 0){
                        $params['options'][$_option->getId()] = serialize($params['options']);
                    }

                    if(array_key_exists($_option->getId(), $params['options'])) {
                        foreach($_option->getValues() as $_value) {

                            if(in_array($_value->getOptionTypeId(), $params['options'])) {
                                $selectedCustomOptions[] = $_value->getTitle();
                            }
                        }
                    }
                }
            }
            /**
             * EOF :: Code for Custom Options
             */

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();
            $this->_getSession()->setCartWasUpdated(true);

            /* BEGIN REF: MID-336, MID-337 */
            Mage::getSingleton('checkout/session')->setBalanceLastAddedProductId(0);

            if (isset($params['super_attribute'])) {
                $childProduct = Mage::getModel('catalog/product_type_configurable')->getProductByAttributes($params['super_attribute'], $product);
                Mage::getSingleton('checkout/session')->setBalanceLastAddedProductId($childProduct->getId());
            }

            /* END REF: MID-336, MID-337 */
            /**
             * @todo remove wishlist observer processAddToCart
             */

            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            $message = $this->__("<strong>%s</strong> was successfully added to your shopping cart.", Mage::helper('core')->htmlEscape(urldecode($product->getName())));

            $this->loadLayout();

            $block = $this->getLayout()->getBlock('cart_sidebar')->toHtml();

            $result['cart'] = array(
                'items' => $block,
                'sidebar_items' => $this->getLayout()->getBlock('cart_widescreen_sidebar')->toHtml(),
                'products' => $cart->getQuote()->getItemsCollection()->count(),
                'qty' => $params['qty'],
            	'custom_giftcard_amount' => isset($params['custom_giftcard_amount']) ? $params['custom_giftcard_amount']: '',
            	'giftcard_amount' => isset($params['giftcard_amount']) ? $params['giftcard_amount'] : '',
                'custom_options' => (is_array($selectedCustomOptions) && count($selectedCustomOptions)) ? implode(',', $selectedCustomOptions) : '',
                'optionPrice' => ($optionPrice) ? $optionPrice : '',
            	'price' => $this->_getLastAddedItemPrice(),
                'subTotal' => $cart->getQuote()->getSubtotal(),
                'subTotalPrice' => $cart->getQuote()->getSubtotal(),
                'message' => $message
            );

            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
        catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $message = $e->getMessage();
                $result['cart'] = array('message' => $message);
                $this->getResponse()->setBody(Zend_Json::encode($result));
            } else {
               $message = implode("\n",array_unique(explode("\n", $e->getMessage())));
               $result['cart'] = array('message' => $message);
               $this->getResponse()->setBody(Zend_Json::encode($result));
            }
        }
        catch (Exception $e) {
            $message = $this->__('Can not add item to shopping cart');
            $result['cart'] = array('message' => $message);
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }

    public function getCartAction()
    {
        $this->loadLayout()->renderLayout();
    }

    //Delete Action
    public function deleteAction()
    {
        $this->_redirectUrl = $this->_getRefererUrl();

        if(strpos($this->_getRefererUrl(), 'checkout/cart/add/')){
            $this->_redirectUrl = $this->_getSession()->getRedirectUrl();
            $this->_getSession()->setRedirectUrl('');
        }
        elseif(strpos($this->_getRefererUrl(), 'checkout/cart/getCart/'))
        {
            $this->_redirectUrl = Mage::getUrl('checkout/cart/');
        }

        $id = (int) $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->_getCart()->removeItem($id);
                $this->_getCart()->save();
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Cannot remove the item.'));
            }
        }
        $this->_redirectUrl($this->_redirectUrl);
    }

    /**
     * Initialize shipping information
     */
    public function estimatePostAction()
    {
        $country    = (string) $this->getRequest()->getParam('country_id');
        $postcode   = (string) $this->getRequest()->getParam('estimate_postcode');
        $city       = (string) $this->getRequest()->getParam('estimate_city');
        $regionId   = (string) $this->getRequest()->getParam('region_id');
        $region     = (string) $this->getRequest()->getParam('region');

        $this->_getQuote()->getShippingAddress()
            ->setCountryId($country)
            ->setCity($city)
            ->setPostcode($postcode)
            ->setRegionId($regionId)
            ->setRegion($region)
            ->setCollectShippingRates(true);
        $result = $this->_getQuote()->save();
        $code = (string) $this->getRequest()->getParam('estimate_method');
        if (!empty($code)) {
            $this->_getQuote()->getShippingAddress()->setShippingMethod($code)/*->collectTotals()*/->save();
            $shippingAmount = $this->_getQuote()->getShippingAddress()->collectTotals()->getShippingAmount();

            if($shippingAmount > 0) {
                $this->_getSession()->addSuccess(
                    $this->__('Estimated delivery cost calculated successfully.')
                );
            } else {
            	if(Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getShippingDescription() == 'Free Shipping') {
                  	$this->_getSession()->addSuccess(
                    	$this->__('Estimated delivery cost calculated successfully.')
                	);
            	} else {
                    //$error_message = '';
                    //$errorModel = Mage::getModel('shipping/rate_result_error');
                    if (Mage::getSingleton('checkout/session')->getErrorShippingMessage()) {
                        $this->_getSession()->addError(Mage::getSingleton('checkout/session')->getErrorShippingMessage());
                    } else {
                        $this->_getSession()->addError(
                            $this->__('An invalid postcode has been entered, please try again')
                        );
                    }
            	}
            }
        }
        $this->_goBack();
    }

    public function estimateUpdatePostAction()
    {
        $code = (string) $this->getRequest()->getParam('estimate_method');
        if (!empty($code)) {
            $this->_getQuote()->getShippingAddress()->setShippingMethod($code)/*->collectTotals()*/->save();
        }
        $this->_goBack();
    }

    /**
     * Update shoping cart data action
     */
    public function updatePostAction()
    {
        try {
            $cartData = $this->getRequest()->getParam('cart');
            if (is_array($cartData)) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    }

                    if(isset($cartData['options'])) {
                        $cartData[$index]['options'] = $cartData['options'];
                    }
                }
                $cart = $this->_getCart();


                if (! $cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                    $cart->getQuote()->setCustomerId(null);
                }

                $cartData = $cart->suggestItemsQty($cartData);
                $cart->updateItems($cartData)
                    ->save();
            }
            $this->_getSession()->setCartWasUpdated(true);
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update shopping cart.'));
            Mage::logException($e);
        }
        $this->_goBack();
    }

    /**
     * Get shopping cart last item price
     *
     * @return decimal
     */
    private function _getLastAddedItemPrice()
    {
        $items = array_reverse(Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems(),true);
        $price = 0;
        foreach($items as $_item)
        {
            if (Mage::helper('weee')->typeOfDisplay($_item, array(0, 1, 4), 'sales'))
                $price = $_item->getCalculationPrice()+$_item->getWeeeTaxAppliedAmount()+$_item->getWeeeTaxDisposition();
            else
                $price = $_item->getCalculationPrice();
            break;
        }
		return $price;
    }

    public function updatepostcodeAction(){
        $country    = (string) $this->getRequest()->getParam('country_id');
        $postcode   = (string) $this->getRequest()->getParam('estimate_postcode');
        $city       = (string) $this->getRequest()->getParam('estimate_city');
        $regionId   = (string) $this->getRequest()->getParam('region_id');
        $region     = (string) $this->getRequest()->getParam('region');

        $this->_getQuote()->getShippingAddress()
            ->setCountryId($country)
            ->setCity($city)
            ->setPostcode($postcode)
            ->setRegionId($regionId)
            ->setRegion($region)
            ->setCollectShippingRates(true);
        $result = $this->_getQuote()->save();
        $code = (string) $this->getRequest()->getParam('estimate_method');
        if (!empty($code)) {
            $this->_getQuote()->getShippingAddress()->setShippingMethod($code)/*->collectTotals()*/->save();
            $shippingAmount = $this->_getQuote()->getShippingAddress()->collectTotals()->getShippingAmount();
            if($shippingAmount > 0) {

            } else {
                if(Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getShippingDescription() == 'Free Shipping') {

                } else {
                    $this->_getSession()->addError(
                        $arrmsg['msg']  =   $this->__('An invalid postcode has been entered, please try again')
                    );
                }
            }
        }
        $this->loadLayout();
        $block  =   $this->getLayout()->createBlock('checkout/onepage_billing');
        if(is_object($block)){
            $arrmsg['dataupdate']   =   $block->setTemplate('checkout/updatepostcode.phtml')->toHtml();
        }
        echo json_encode($arrmsg);
        exit;
    }
}
