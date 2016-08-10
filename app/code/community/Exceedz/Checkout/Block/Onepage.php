<?php
/**
 * one page checkout block
 *
 * @category    Exceedz
 * @package     Exceedz_Checkout
 */
class Exceedz_Checkout_Block_Onepage extends Mage_Checkout_Block_Onepage
{
	public function getSteps()
	{
		$steps = array();

		$stepCodes = array('cart', 'billing', 'shipping', 'payment');

        $this->getCheckout()->setStepData('cart', array(
            'label'     => Mage::helper('checkout')->__("You're purchasing"),
            'allow'     => 1
        ));

		$this->getCheckout()->setStepData('billing', array(
            'label'     => Mage::helper('checkout')->__('Billing &<br/> Delivery details'),
            'allow'     => 1,
            'is_show'   => $this->isShow()
        ));

		$this->getCheckout()->setStepData('payment', array(
            'label'     => $this->__('How would you<br/> like to pay for<br/> your order?'),
            'is_show'   => $this->isShow()
        ));

        parent::_construct();

		foreach ($stepCodes as $step) {
            $steps[$step] = $this->getCheckout()->getStepData($step);
		}
		return $steps;
	}

	/**
     * prepare breadcrumb
     */
    protected function __prepareLayout(){

         if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')){
            $breadcrumbs->addCrumb('home', array('label'=>__('Home'), 'title'=>__('Go to Home Page'), 'link'=>Mage::getBaseUrl()));
            $breadcrumbs->addCrumb('cart', array('label'=>'Your Cart', 'title'=>'Your Cart'));
         }
         return parent::_prepareLayout();
    }

    /**
     * set active step
     */
    public function getActiveStep()
    {
        return $this->isCustomerLoggedIn() ? 'billing' : 'cart';
    }
}
