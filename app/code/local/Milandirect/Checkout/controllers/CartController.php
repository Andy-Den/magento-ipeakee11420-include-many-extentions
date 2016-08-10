<?php

/**
 * Override Milandirect_Checkout to change response message
 *
 * @category  Milandirect
 * @package   Milandirect_Checkout
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */
require_once 'Exceedz/Checkout/controllers/CartController.php';
class Milandirect_Checkout_CartController extends Exceedz_Checkout_CartController
{
    protected $_redirectUrl = '';

    /**
     * Add product to shopping cart action
     *
     * @return void
     */
    public function addAction()
    {
        $userAgent = Mage::helper('shippingprotection/data')->getTargetPlatform();
        //if ($userAgent == 'mobile')
        // {
        //    return $this->addToCartAction();
        //}

        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();

        $this->_getSession()->setRedirectUrl($this->_getRefererUrl());

        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            } else {
                $params['qty'] = 1;
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

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();
            $this->_getSession()->setCartWasUpdated(true);

            Mage::getSingleton('checkout/session')->setBalanceLastAddedProductId(0);

            if (isset($params['super_attribute'])) {
                $configurableModel = Mage::getModel('catalog/product_type_configurable');
                $childProduct = $configurableModel->getProductByAttributes($params['super_attribute'], $product);
                Mage::getSingleton('checkout/session')->setBalanceLastAddedProductId($childProduct->getId());
            }

            /**
             * @todo remove wishlist observer processAddToCart
             */

            Mage::dispatchEvent(
                'checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            $message = $this->__(
                '<strong>%s</strong> was successfully added to your shopping cart.',
                Mage::helper('core')->htmlEscape(urldecode($product->getName()))
            );

            $this->loadLayout();

            $block = $this->getLayout()->getBlock('cart_sidebar');
            if (!is_object($block)) {
                $block = $this->getLayout()->getBlock('minicart_head');
            }
            $block = $block->toHtml();
            $sidebar = $this->getLayout()->getBlock('cart_widescreen_sidebar');
            if (!is_object($sidebar)) {
                $sidebar = $block;
            } else {
                $sidebar = $sidebar->toHtml();
            }

            $giftAmount = isset($params['custom_giftcard_amount']) ? $params['custom_giftcard_amount']: '';
            $customOption = (is_array($selectedCustomOptions) &&
                count($selectedCustomOptions)) ? implode(',', $selectedCustomOptions) : '';
            $config = Mage::getSingleton('tax/config');
            $skipTax = true;
            if ($config->displayCartSubtotalBoth() || $config->displayCartSubtotalInclTax()){
                $skipTax = false;
            }
            $blockSidebar = $this->getLayout()->getBlock('minicart_content');
            $result['cart'] = array(
                'items' => $block,
                'sidebar_items' => $sidebar,
                'products' => $cart->getQuote()->getItemsCollection()->count(),
                'qty' => $params['qty'],
                'custom_giftcard_amount' => $giftAmount,
                'giftcard_amount' => isset($params['giftcard_amount']) ? $params['giftcard_amount'] : '',
                'custom_options' => $customOption,
                'optionPrice' => ($optionPrice) ? $optionPrice : '',
                'price' => $this->_getLastAddedItemPrice(),
                'subTotal' => $blockSidebar->getSubtotal($skipTax),
                'subTotalPrice' => $blockSidebar->getSubtotal($skipTax),
                'message' => $message,
                'user_agent' => $userAgent
            );
            if (isset($params['return_url'])) {
                $result['cart']['return_url'] = $params['return_url'];
                $pos = strpos($params['return_url'], 'paypal');
                if ($pos !== false) {
                    $result['cart']['return_url'] = Mage::getUrl('checkout/cart');
                }

            } else {
                $result['cart']['return_url'] = '';
            }

            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
        catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $message = $e->getMessage();
                $result['cart'] = array('message' => $message);
                $result['cart']['return_url'] = '';
                $this->getResponse()->setBody(Zend_Json::encode($result));
            } else {
                $message = implode("\n", array_unique(explode("\n", $e->getMessage())));
                $result['cart'] = array('message' => $message);
                $result['cart']['return_url'] = '';
                $this->getResponse()->setBody(Zend_Json::encode($result));
            }
        }
        catch (Exception $e) {
            $message = $this->__('Can not add item to shopping cart');
            $result['cart'] = array('message' => $message);
            $result['cart']['return_url'] = '';
            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }

    /**
     * Get shopping cart last item price
     *
     * @return decimal
     */
    private function _getLastAddedItemPrice()
    {
        $items = array_reverse(
            Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems(),
            true
        );
        $price = 0;
        $checkoutHelper = Mage::helper('checkout');
        foreach ($items as $_item) {
            if (Mage::helper('tax')->displayCartPriceInclTax() || Mage::helper('tax')->displayCartBothPrices()) {
                $_incl = $checkoutHelper->getPriceInclTax($_item);
                if (Mage::helper('weee')->typeOfDisplay($_item, array(0, 1, 4), 'sales')) {
                    $price = $_incl + Mage::helper('weee')->getWeeeTaxInclTax($_item);
                } else {
                    $price = $_incl-$_item->getWeeeTaxDisposition();
                }
            } else {
                $price = $_item->getCalculationPrice();
            }
            break;
        }
        return $price;
    }

    /**
     * Checkout update post code
     *
     * @return string
     */
    public function updatepostcodeAction(){
        $country    = (string) $this->getRequest()->getParam('country_id');
        $postcode   = (string) $this->getRequest()->getParam('estimate_postcode');
        $city       = (string) $this->getRequest()->getParam('estimate_city');
        $regionId   = (string) $this->getRequest()->getParam('region_id');
        $region     = (string) $this->getRequest()->getParam('region');
        $split      = $this->getRequest()->getParam('split');
        $updateType = $this->getRequest()->getParam('type');
        $this->_getQuote()->getShippingAddress()
            ->setCountryId($country)
            ->setCity($city)
            ->setPostcode($postcode)
            ->setRegionId($regionId)
            ->setRegion($region)
            ->setCollectShippingRates(true);
        if ($updateType == 'billing') {
            $this->_getQuote()->getBillingAddress()
                ->setCountryId($country)
                ->setCity($city)
                ->setPostcode($postcode)
                ->setRegionId($regionId)
                ->setRegion($region);
        }
        $result = $this->_getQuote()->save();
        $code = (string) $this->getRequest()->getParam('estimate_method');
        if (!empty($code)) {
            $this->_getQuote()->getShippingAddress()->setShippingMethod($code)->save();
            $shippingAmount = $this->_getQuote()->getShippingAddress()->collectTotals()->getShippingAmount();
            if (!($shippingAmount > 0)) {
                $shippingDescription = Mage::getSingleton('checkout/session')->getQuote()
                    ->getShippingAddress()
                    ->getShippingDescription();
                if( $shippingDescription != 'Free Shipping') {
                    $this->_getSession()->addError(
                        $arrmsg['msg']  =   $this->__('An invalid postcode has been entered, please try again')
                    );
                }
            }
        }

        if ($split == 1) {
            $this->loadLayout();
            if ($updateType == 'billing') {
                $billing = $this->getLayout()->createBlock('checkout/onepage_billing');
                if (is_object($billing)) {
                    $arrmsg['suburb'] = $this->_getQuote()->getShippingAddress()->getPostCode();
                    $arrmsg['suburb'] .= $billing->setTemplate('onestepcheckout/update/billing/suburb.phtml')->toHtml();
                    $arrmsg['state'] = $billing->setTemplate('onestepcheckout/update/billing/state.phtml')->toHtml();
                }
            }

            if ($updateType == 'shipping') {
                $billing = $this->getLayout()->createBlock('checkout/onepage_shipping');
                if (is_object($billing)) {
                    $arrmsg['suburb'] = $billing->setTemplate('onestepcheckout/update/shipping/suburb.phtml')->toHtml();
                    $arrmsg['state'] = $billing->setTemplate('onestepcheckout/update/shipping/state.phtml')->toHtml();
                }
            }
        } else {
            $this->loadLayout();
            $block  =   $this->getLayout()->createBlock('checkout/onepage_billing');
            if (is_object($block)) {
                $arrmsg['dataupdate']   =   $block->setTemplate('checkout/updatepostcode.phtml')->toHtml();
            }
        }

        echo json_encode($arrmsg);
        exit;
    }

    /**
     * Update product configuration for a cart item
     */
    public function updateItemOptionsAction()
    {
        $cart   = $this->_getCart();
        $id = (int) $this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();

        if (!isset($params['options'])) {
            $params['options'] = array();
        }
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $quoteItem = $cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                Mage::throwException($this->__('Quote item is not found.'));
            }

            $item = $cart->updateItem($id, new Varien_Object($params));
            if (is_string($item)) {
                Mage::throwException($item);
            }
            if ($item->getHasError()) {
                Mage::throwException($item->getMessage());
            }

            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            Mage::dispatchEvent('checkout_cart_update_item_complete',
                array('item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );
            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $result['message'] = $this->__('%s was updated in your shopping cart.', Mage::helper('core')->escapeHtml($item->getProduct()->getName()));
                }

            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $result['message'] = $e->getMessage();
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $result['message'] = $message;
                }
            }
        } catch (Exception $e) {
            $result['message'] = $this->__('Cannot update the item.');
        }
        $result['update'] = 1;
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }
}
