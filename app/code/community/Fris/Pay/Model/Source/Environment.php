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
class Fris_Pay_Model_Source_Environment
{
    /**
     * Returns a list of supported environments for use on the config page.
     * 
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'sandbox',
                'label' => 'Sandbox',
            ),
            array(
                'value' => 'production',
                'label' => 'Production',
            )
        );
    }
}
