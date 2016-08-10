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
class Fris_Pay_Model_Source_VerificationStyle
{
    /**
     * Returns a list of supported verification styles.
     * 
     * @return array
     */
    public function toOptionArray($isMultiselect = false)
    {
        return array(
            array(
                'value' => 'alert',
                'label' => 'Pop up an alert when verified',
            ),
            array(
                'value' => 'tick',
                'label' => 'Display a tick next to Verify button (if present)',
            ),
            array(
                'value' => 'throbber_authorize',
                'label' => 'When authorizing show throbber + "Authorize..."',
            )
        );
    }
}
