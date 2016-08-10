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
class Fris_Pay_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Removes Magento-added transaction id suffix if applicable.
     * 
     * @param string $transactionId, possibly containing suffix
     * @return string
     */
    public function getTransactionIdBase($transId)
    {
        $suffixes = array(
            '-' . Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
            '-' . Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID,
        );
        foreach ($suffixes as $suffix) {
            if (strpos($transId, $suffix)) {
                return str_replace($suffix, '', $transId);
            }        
        }
        return $transId;
    }
}
