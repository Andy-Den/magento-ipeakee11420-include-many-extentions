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

class Balance_ConfigurableSimplePriceOverride_Block_Adminhtml_Catalog_Product_Edit extends Mage_Adminhtml_Block_Catalog_Product_Edit {

    public function getHeader() {
        $header = '';
        if ($this->getProduct()->getId()) {
            $header = $this->htmlEscape($this->getProduct()->getName());
        } else {
            $header = Mage::helper('catalog')->__('New Product');
        }
        if ($setName = $this->getAttributeSetName()) {
            $header.= ' (' . $setName . ')';
        }
        $moduleGlobalStatus = Mage::helper('configurablesimplepriceoverride')->checkModuelGlobalStatus();
        if ($moduleGlobalStatus && $this->getProduct()->isConfigurable()) {
            return $header;
        } else {
            return $header . '<style type="text/css">#scpproductspecific{
                                    display: none;
                                }
                                label[for="scpproductspecific"]{
                                     display: none !important;
                                }
                                #scpproductspecific{
                                    display: none;
                                }</style>
                                 <script type="text/javascript">
                                    document.getElementById("scpproductspecific").parentNode.nextElementSibling.innerHTML ="";
                                 </script>
';
        }
    }

}

