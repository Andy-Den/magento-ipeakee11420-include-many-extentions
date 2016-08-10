<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */

class Amasty_SeoRichData_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isYotpoReviewsEnabled()
    {
        return Mage::helper('core')->isModuleEnabled('Yotpo_Yotpo');
    }
}
