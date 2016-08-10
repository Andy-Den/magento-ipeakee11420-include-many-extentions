<?php
/**
 * Override Mage_Paypal_Model_Api_Standard
 * @category    Exceedz
 * @package     Exceedz_Paypal
 */

/**
 * PayPal Standard checkout request API
 */
class Exceedz_Paypal_Model_Api_Standard extends Mage_Paypal_Model_Api_Standard
{
    /**
     * Import address object, if set, to the request
     *
     * @param array $request
     */
    protected function _importAddress(&$request)
    {
        $address = $this->getAddress();
        if (!$address) {
            if ($this->getNoShipping()) {
                $request['no_shipping'] = 1;
            }
            return;
        }

        $request = Varien_Object_Mapper::accumulateByMap($address, $request, array_flip($this->_addressMap));

        // Address may come without email info (user is not always required to enter it), so add email from order
        if (!$request['email']) {
            $order = $this->getOrder();
            if ($order) {
                $request['email'] = $order->getCustomerEmail();
            }
        }

        $regionCode = $this->_lookupRegionCodeFromAddress($address);
        if ($regionCode) {
            $request['state'] = $regionCode;
        }
        $this->_importStreetFromAddress($address, $request, 'address1', 'address2');
        $this->_applyCountryWorkarounds($request);

        $request['address_override'] = 0;
    }
}
