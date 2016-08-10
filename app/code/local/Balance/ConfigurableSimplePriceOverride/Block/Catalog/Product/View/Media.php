<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Balance
 * @package    ConfigurableSimplePriceOverride
 * @copyright  Copyright (c) 2011 Balance
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Balance_ConfigurableSimplePriceOverride_Block_Catalog_Product_View_Media extends Mage_Catalog_Block_Product_View_Media {

    public function getGalleryUrl($image = null) {
      
        $moduleStatus = Mage::helper('configurablesimplepriceoverride')->checkModuelStatus($this->getProduct());
        if ($moduleStatus) {
            $params = array(
                'id' => $this->getProduct()->getId(),
                'pid' => $this->getProduct()->getCpid()
            );
            if ($image) {
                $params['image'] = $image->getValueId();
                return $this->getUrl('*/*/gallery', $params);
            }
            return $this->getUrl('*/*/gallery', $params);
        } else {
            return parent::getGalleryUrl($image = null);
        }
    }

}

