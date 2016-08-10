<?php

/**
 * Override Milandirect_OneStepCheckout to change default shipping method
 *
 * @category  Milandirect
 * @package   Milandirect_OneStepCheckout
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */
require_once "Idev/OneStepCheckout/controllers/IndexController.php";
class Milandirect_OneStepCheckout_IndexController extends Idev_OneStepCheckout_IndexController
{
    /**
     * Override to change shipping method default
     *
     * @return void
     */
    public function indexAction()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $protection = $quote->getBiIsProtection();

        if (!($protection == 1 || $protection == 2)) {
            $this->_redirect('checkout/cart', array('_query'=>'protection=1'));
            return;
        }

        $quote->getShippingAddress()
            ->setPostcode($quote->getShippingAddress()->getPostcode())
            ->setCollectShippingRates(true);
        $quote->getShippingAddress()->setShippingMethod('tablerate_bestway')->save();
        $shippingAmount = $quote->getShippingAddress()->collectTotals()->getShippingAmount();
        $freeDelivery = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getFreeMethodWeight();
        if (!($shippingAmount > 0) && $freeDelivery > 0) {
            if (Mage::getSingleton('checkout/session')->getQuote()
                    ->getShippingAddress()->getShippingDescription() != 'Free Shipping') {
                $this->_redirect('checkout/cart', array('_query'=>'postcode=1'));
                return;
            }
        }

        if ($this->getRequest()->isPost()) {
            if ($this->getRequest()->getParam('shipping_method') == null) {
                $rates = $this->getOnepage()->getQuote()->getShippingAddress()->getGroupedAllShippingRates();
                $shippingMethod = '';
                $shippingPrice  = '';
                foreach ($rates as $_code => $_rates) {
                    foreach ($_rates as $rate) {
                        $price = number_format($rate->getPrice(), 2);
                        if ($shippingPrice == '') {
                            $shippingPrice = $price;
                            $shippingMethod = $rate->getCode();
                        } elseif ($shippingPrice < $price) {
                            $shippingPrice = $price;
                            $shippingMethod = $rate->getCode();
                        }

                    }
                }
                $this->getRequest()->setPost('shipping_method', $shippingMethod);
            }
        }

        $routeName = $this->getRequest()->getRouteName();

        if (!Mage::helper('onestepcheckout')->isRewriteCheckoutLinksEnabled() && $routeName != 'onestepcheckout') {
            $this->_redirect('checkout/onepage', array('_secure'=>true));
        }

        $quote = $this->getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->_redirect('checkout/cart');
            return;
        }
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message');
            Mage::getSingleton('checkout/session')->addError($error);
            $this->_redirect('checkout/cart');
            return;
        }

        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        //@TODO: validate the necessity of this clause

        $this->loadLayout();

        if (Mage::helper('onestepcheckout')->isEnterprise() && Mage::helper('customer')->isLoggedIn()) {

            $customerBalanceBlock = $this->getLayout()->createBlock(
                'enterprise_customerbalance/checkout_onepage_payment_additional',
                'customerbalance',
                array('template'=>'onestepcheckout/customerbalance/payment/additional.phtml')
            );
            $customerBalanceBlockScripts = $this->getLayout()->createBlock(
                'enterprise_customerbalance/checkout_onepage_payment_additional',
                'customerbalance_scripts',
                array('template'=>'onestepcheckout/customerbalance/payment/scripts.phtml')
            );

            $rewardPointsBlock = $this->getLayout()->createBlock(
                'enterprise_reward/checkout_payment_additional',
                'reward.points',
                array('template'=>'onestepcheckout/reward/payment/additional.phtml', 'before' => '-')
            );
            $rewardPointsBlockScripts = $this->getLayout()->createBlock(
                'enterprise_reward/checkout_payment_additional',
                'reward.scripts',
                array('template'=>'onestepcheckout/reward/payment/scripts.phtml', 'after' => '-')
            );

            $this->getLayout()->getBlock('choose-payment-method')
                ->append($customerBalanceBlock)
                ->append($customerBalanceBlockScripts)
                ->append($rewardPointsBlock)
                ->append($rewardPointsBlockScripts)
            ;
        }

        if (is_object(Mage::getConfig()->getNode('global/models/googleoptimizer')) &&
            Mage::getStoreConfigFlag('google/optimizer/active')) {
            $googleOptimizer = $this->getLayout()->createBlock(
                'googleoptimizer/code_conversion',
                'googleoptimizer.conversion.script',
                array('after'=>'-')
            );
            $googleOptimizer->setScriptType('conversion_script')
                ->setPageType('checkout_onepage_success');
            $this->getLayout()->getBlock('before_body_end')
                ->append($googleOptimizer);
        }

        $this->renderLayout();
    }

    /**
     * Make sure customer is valid, if logged in
     * By default will add error messages and redirect to customer edit form
     *
     * @param bool $redirect  - stop dispatch and redirect?
     * @param bool $addErrors - add error messages?
     * @return bool
     */
    protected function _preDispatchValidateCustomer($redirect = true, $addErrors = true)
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($customer && $customer->getId()) {
            $validationResult = $customer->validate();
            if ((true !== $validationResult) && is_array($validationResult)) {
                if ($addErrors) {
                    foreach ($validationResult as $error) {
                        Mage::getSingleton('customer/session')->addError($error);
                    }
                }
                if ($redirect) {
                    $this->_redirect('customer/account/edit');
                    $this->setFlag('', self::FLAG_NO_DISPATCH, true);
                }
                return false;
            }
        }
        return true;
    }
}
