<?php
/**
 * fris - smart commerce extensions for Magento
 *
 * @category  Fris
 * @package   Fris_Pay
 * @copyright Copyright (c) 2015 fris IT (http://fris.technology)
 * @license   http://fris.technology/license
 * @author    fris IT <support@fris.technology>
 */
class Fris_Pay_Model_Source_PaymentAction
{

    public function toOptionArray()
    {
        return array(
            array(
                'value' => Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE,
                'label' => 'Authorize'
            ),
            array(
                'value' => Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE,
                'label' => 'Authorize and Capture'
            ),
        );
    }
}
