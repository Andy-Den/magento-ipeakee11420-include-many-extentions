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

class Balance_ConfigurableSimplePriceOverride_Block_Catalog_Product_Price extends Mage_Catalog_Block_Product_Price
{
    
    public function _toHtml()
    {
        $_taxHelper   = $this->helper('tax');
        $moduleStatus = Mage::helper('configurablesimplepriceoverride')->checkModuelStatus($this->getProduct());
        if ($moduleStatus) {
            $htmlToInsertAfter = '<div class="price-box">';
            if ($this->getTemplate() == 'catalog/product/price.phtml') {
                $product = $this->getProduct();
                if (is_object($product) && $product->isConfigurable()) {
                    $extraHtml = '<span class="label" id="configurable-price-from-' . $product->getId() . $this->getIdSuffix() . '"><span class="configurable-price-from-label">';
                    
                    if (($product->getMaxPossibleFinalPrice() != $product->getFinalPrice()) && (!$_taxHelper->displayBothPrices())) {
                        $extraHtml .= $this->__('Price From:');
                        //$extraHtml  .= $product->getFinalPrice().$product->getPrice();
                    }
                    $categoryCheck = Mage::app()->getFrontController()->getRequest()->getControllerName();
                    
                    $extraHtml .= '</span></span>';
                    
                    if (($categoryCheck == 'product' || $categoryCheck == 'cart' || ($categoryCheck == 'index' && $product->getPrice() < $product->getFinalPrice())) && $product->getPrice() <= $product->getFinalPrice()) {
                        $extraHtml .= '<p class="was-old-price">
                <span class="price-label" style="display:none;">Regular Price:</span>
                <span class="price" id="old-price-' . $product->getId() . '"></span>
            </p>

                        <p class="special-price" style="display:none;">
                <span class="price-label" style="">Special Price:</span>
                <span class="price" id="product-price-' . $product->getId() . '"></span>
            </p>';
                        
                        $extraHtml .= '<style type="text/css">
                span#product-price-' . $product->getId() . '{
                    display:none;
                    }
                .special-price .price{
                    display:block !important;
                    }
                 
            </style>
           <script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.min.js"></script>  
            <script type="text/javascript">
            var jQuery_west_1 = jQuery.noConflict(true);
                jQuery_west_1( document ).ready(function() {
                
                                var existVal = jQuery_west_1("#old-price-' . $product->getId() . '").html();
                                var newVal = jQuery_west_1(".product-options-bottom #old-price-' . $product->getId() . '").html();
                                var newspeVal = jQuery_west_1(".product-options-bottom #old-price-' . $product->getId() . '_clone").html();
                                var regStats = jQuery_west_1(".product-options-bottom #was-old-price .price-label");
                                var regStats2 = jQuery_west_1(".product-options-bottom #old-price .price-label");
                                            
                                     if( !regStats.is(":visible") ) {
                                             //alert("dsdsd");
                                       jQuery_west_1(".product-options-bottom .was-old-price #old-price-' . $product->getId() . '").css("display","none");
                                    }else{
                                       jQuery_west_1(".product-options-bottom .was-old-price #old-price-' . $product->getId() . '").css("display","block");
                                    }                    

                                  
                                  if((existVal!=newspeVal)){
                             
                                   jQuery_west_1(".product-options-bottom #old-price-' . $product->getId() . '").html(existVal);
                                  }else{
                                  jQuery_west_1(".product-options-bottom #old-price-' . $product->getId() . '").html("");
                                  }
                                  
                        jQuery_west_1(".super-attribute-select").change(function() {

                            var existVal = jQuery_west_1("#old-price-' . $product->getId() . '").html();
                            var newVal = jQuery_west_1(".product-options-bottom #old-price-' . $product->getId() . '").html();
                            var newspeVal = jQuery_west_1(".product-options-bottom #old-price-' . $product->getId() . '_clone").html();
                            var regStats = jQuery_west_1(".product-options-bottom #was-old-price .price-label");
                            var regStats2 = jQuery_west_1(".product-options-bottom #old-price .price-label");
                                  
                        
                                if( !regStats.is(":visible") ) {
                                            jQuery_west_1(".product-options-bottom .was-old-price #old-price-' . $product->getId() . '").css("display","none");
                                    }else{
                                       jQuery_west_1(".product-options-bottom .was-old-price #old-price-' . $product->getId() . '").css("display","block");
                                    }    
                                        //alert(existVal);
                                     jQuery_west_1(".product-options-bottom .old-price #old-price-' . $product->getId() . '").css("display","block");
                                     jQuery_west_1(".product-options-bottom .old-price #old-price-' . $product->getId() . '").html(existVal);
                                     
                        
                                  if((existVal!=newspeVal)){
                             
                                   jQuery_west_1(".product-options-bottom #old-price-' . $product->getId() . '").html(existVal);
                                  }else{
                                  jQuery_west_1(".product-options-bottom #old-price-' . $product->getId() . '").html("");
                                  }
                            });
                       
                    });
            
            
            </script>
            
            
                    ';
                        
                        if (($categoryCheck == 'index') && $product->getPrice() >= $product->getFinalPrice()) {
                            echo '
            <script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.min.js"></script>  
            <script type="text/javascript">
            var jQuery_west_1 = jQuery.noConflict(true);
                jQuery_west_1( document ).ready(function() {
                
                var wishlistCheck = jQuery_west_1(".was-old-price #old-price-' . $product->getId() . '").html();
                    
                if(wishlistCheck==""){
                //alert(wishlistCheck);
                    jQuery_west_1(".regular-price").css("display","block");
                    }
                    
                });
            
            
            </script>
            
            ';
                        }
                        
                        $priceHtml = parent::_toHtml();
                        
                        
                return substr_replace($priceHtml, $extraHtml, strpos($priceHtml, $htmlToInsertAfter) + strlen($htmlToInsertAfter), 0);
                    }
                }
                return parent::_toHtml();
            } else {
                return parent::_toHtml();
            }
        }
    }
    
}