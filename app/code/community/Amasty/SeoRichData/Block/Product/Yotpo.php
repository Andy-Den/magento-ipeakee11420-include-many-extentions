<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */


class Amasty_SeoRichData_Block_Product_Yotpo extends Mage_Core_Block_Template
{
    protected function _toHtml()
    {
        if (Mage::getStoreConfigFlag('amseorichdata/yotpo/enabled') &&
            Mage::helper('amseorichdata')->isYotpoReviewsEnabled()) {
            return parent::_toHtml();
        }
        else
            return '';
    }
}
