<?php
class Exceedz_Customer_Model_Address_Abstract extends Mage_Customer_Model_Address_Abstract
{
    /**
     * Validate address attribute values
     *
     * @return bool
     */
    public function validate()
    {
		echo "in function";
        $errors = array();
        $helper = Mage::helper('customer');
        $this->implodeStreetAddress();
        if (!Zend_Validate::is($this->getFirstname(), 'NotEmpty')) {
            $errors[] = $helper->__('Please enter the first name.');
        }

        if (!Zend_Validate::is($this->getLastname(), 'NotEmpty')) {
            $errors[] = $helper->__('Please enter the last name.');
        }

        if (!Zend_Validate::is($this->getStreet(1), 'NotEmpty')) {
            $errors[] = $helper->__('Please enter the street.');
        }

        if (!Zend_Validate::is($this->getCity(), 'NotEmpty')) {
            //$errors[] = $helper->__('Please enter the city.');
        }

        if (!Zend_Validate::is($this->getTelephone(), 'NotEmpty')) {
            $errors[] = $helper->__('Please enter the telephone number.');
        }

        $_havingOptionalZip = Mage::helper('directory')->getCountriesWithOptionalZip();
        if (!in_array($this->getCountryId(), $_havingOptionalZip) && !Zend_Validate::is($this->getPostcode(), 'NotEmpty')) {
            $errors[] = $helper->__('Please enter the zip/postal code.');
        }

        if (!Zend_Validate::is($this->getCountryId(), 'NotEmpty')) {
            $errors[] = $helper->__('Please enter the country.');
        }

        if ($this->getCountryModel()->getRegionCollection()->getSize()
               && !Zend_Validate::is($this->getRegionId(), 'NotEmpty')) {
            $errors[] = $helper->__('Please enter the state/province.');
        }

        if (empty($errors) || $this->getShouldIgnoreValidation()) {
            return true;
        }
        return $errors;
    }
}