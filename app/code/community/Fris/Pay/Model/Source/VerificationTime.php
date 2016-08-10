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
class Fris_Pay_Model_Source_VerificationTime
{
    /**
     * Returns a list of supported verification times.
     * 
     * @return array
     */
    public function toOptionArray($isMultiselect = false)
    {
        return array(
            array(
                'value' => 'order_submit',
                'label' => 'When "Place Order" is pressed',
            ),
            array(
                'value' => 'payment_button',
                'label' => 'When "Verify" is pressed on the payment form',
            ),
            array(
                'value' => 'payment_mouseout',
                'label' => 'Upon mouse-out from the payment form',
            ),
        );
    }
}
