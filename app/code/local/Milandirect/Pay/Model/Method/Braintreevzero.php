<?php

/**
 * Override Milandirect_Pay to map country address
 *
 * @category  Milandirect
 * @package   Milandirect_Pay
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2016 Balance
 */
class Milandirect_Pay_Model_Method_Braintreevzero extends Fris_Pay_Model_Method_Braintreevzero
{
    protected $_countryMaps = array('EW', 'ND', 'RI', 'OS');

    /**
     * Convert magento address to a Braintree-style array.
     *
     * @param Mage_Customer_Model_Address $address order address
     *
     * @return array
     */
    public function toBraintreeAddress($address)
    {
        $region = $address->getRegion();
        // PayPal: US & CA need to pass 2-letter region code, not region name.
        $regionId = $address->getData('region_id');
        $countryId = $address->getCountryId();
        if ($regionId && in_array($countryId, array('US', 'CA'))) {
            $regionModel = $address->getRegionModel($regionId);
            if ($regionModel && $regionModel->getCountryId() == $countryId) {
                $region = $regionModel->getCode();
            }
        }
        $countryCode = $address->getCountry();
        if (in_array($countryCode, $this->_countryMaps)) {
            $countryCode = 'GB';
        }
        return array(
            'company'           => $address->getCompany(),
            'firstName'         => $address->getFirstname(),
            'lastName'          => $address->getLastname(),
            'streetAddress'     => $address->getStreet(1),
            'extendedAddress'   => $address->getStreet(2),
            'locality'          => $address->getCity(),
            'region'            => $region,
            'postalCode'        => $address->getPostcode(),
            'countryCodeAlpha2' => $countryCode,
        );
    }
}
