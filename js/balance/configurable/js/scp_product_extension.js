
Product.Config.prototype.getMatchingSimpleProduct = function(){
    var inScopeProductIds = this.getInScopeProductIds();
    if ((typeof inScopeProductIds != 'undefined') && (inScopeProductIds.length == 1)) {
        return inScopeProductIds[0];
    }
    return false;
};

Product.Config.prototype.getInScopeProductIds = function(optionalAllowedProducts) {

    var childProducts = this.config.childProducts;
    var allowedProducts = [];

    if ((typeof optionalAllowedProducts != 'undefined') && (optionalAllowedProducts.length > 0)) {
       // alert("starting with: " + optionalAllowedProducts.inspect());
        allowedProducts = optionalAllowedProducts;
    }

    for(var s=0, len=this.settings.length-1; s<=len; s++) {
        if (this.settings[s].selectedIndex <= 0){
            break;
        }
        var selected = this.settings[s].options[this.settings[s].selectedIndex];
        if (s==0 && allowedProducts.length == 0){
            allowedProducts = selected.config.allowedProducts;
        } else {
           // alert("merging: " + allowedProducts.inspect() + " with: " + selected.config.allowedProducts.inspect());
            allowedProducts = allowedProducts.intersect(selected.config.allowedProducts).uniq();
           // alert("to give: " + allowedProducts.inspect());
        }
    }


    if ((typeof allowedProducts == 'undefined') || (allowedProducts.length == 0)) {
        productIds = Object.keys(childProducts);
    } else {
        productIds = allowedProducts;
    }
    return productIds;
};


Product.Config.prototype.getProductIdOfCheapestProductInScope = function(priceType, optionalAllowedProducts) {

    var childProducts = this.config.childProducts;
    var productIds = this.getInScopeProductIds(optionalAllowedProducts);

    var minPrice = Infinity;
    var lowestPricedProdId = false;

    //Get lowest price from product ids.
    for (var x=0, len=productIds.length; x<len; ++x) {
        var thisPrice = Number(childProducts[productIds[x]][priceType]);
        if (thisPrice < minPrice) {
            minPrice = thisPrice;
            lowestPricedProdId = productIds[x];
        }
    }
    return lowestPricedProdId;
};


Product.Config.prototype.getProductIdOfMostExpensiveProductInScope = function(priceType, optionalAllowedProducts) {

    var childProducts = this.config.childProducts;
    var productIds = this.getInScopeProductIds(optionalAllowedProducts);

    var maxPrice = 0;
    var highestPricedProdId = false;

    //Get highest price from product ids.
    for (var x=0, len=productIds.length; x<len; ++x) {
        var thisPrice = Number(childProducts[productIds[x]][priceType]);
        if (thisPrice > maxPrice) {
            maxPrice = thisPrice;
            highestPricedProdId = productIds[x];
        }
    }
    return highestPricedProdId;
};



Product.Config.prototype.updateFormProductId = function(productId){
//    if (!productId) {
//        return false;
//    }
//    var currentAction = $('product_addtocart_form').action;
//    newcurrentAction = currentAction.sub(/product\/\d+\//, 'product/' + productId + '/');
//    $('product_addtocart_form').action = newcurrentAction;
//    $('product_addtocart_form').product.value = productId;
};


Product.Config.prototype.addParentProductIdToCartForm = function(parentProductId) {
    if (typeof $('product_addtocart_form').cpid != 'undefined') {
        return; //don't create it if we have one..
    }
    var el = document.createElement("input");
    el.type = "hidden";
    el.name = "cpid";
    el.value = parentProductId.toString();
    $('product_addtocart_form').appendChild(el);
};



Product.OptionsPrice.prototype.updateSpecialPriceDisplay = function(price, finalPrice) {

    var prodForm = $('product_addtocart_form');

    var specialPriceBox = prodForm.select('p.special-price');
    var oldPricePriceBox = prodForm.select('p.old-price, p.was-old-price');
    var magentopriceLabel = prodForm.select('span.price-label');

    if (price == finalPrice) {
        specialPriceBox.each(function(x) {x.hide();});
        magentopriceLabel.each(function(x) {x.hide();});
        oldPricePriceBox.each(function(x) {
            x.removeClassName('old-price');
            x.addClassName('was-old-price');
        });
    }else{
        specialPriceBox.each(function(x) {x.show();});
        magentopriceLabel.each(function(x) {x.show();});
        oldPricePriceBox.each(function(x) {
            x.removeClassName('was-old-price');
            x.addClassName('old-price');
        });
    }
};
Product.Config.prototype.updatePriceHtml = function(finalPrice, price){
    oldPrice = '';
    specialPrice = '';
    regularPrice = '';
    if (parseFloat(finalPrice) < parseFloat(price)){
        oldPrice = '<p class="old-price"><span class="price-label"></span><span id="old-price-'+ this.config.productId +'" class="price"></span></p>';
        specialPrice = '<p class="special-price"><span class="price-label"></span><span id="product-price-'+ this.config.productId +'" class="price"></span></p>';
    } else {
        regularPrice = '<span class="regular-price" id="product-price-'+ this.config.productId +'"><span class="price"></span></span>';
    }
    jQuery('.product-view .price-box').html('');
    jQuery('.product-view .price-box').append(oldPrice+specialPrice+regularPrice);
};
Product.Config.prototype.reloadPrice = function() {
    var childProductId = this.getMatchingSimpleProduct();
    var childProducts = this.config.childProducts;
    jQuery('.product-view .product-custom-stock-status').hide();
    var notSelect = false;
    jQuery('#product-options-wrapper select.required-entry').each(function(){
       if (jQuery(this).val() == '' || jQuery(this).val() == null){
           notSelect = true;
       }
    });
    if (childProductId && notSelect == false){
        jQuery.uniform.update();
        jQuery('.product-price-' + this.config.productId+'-range').hide();
        var price = childProducts[childProductId]["price"];
        var finalPrice = childProducts[childProductId]["finalPrice"];
        this.updatePriceHtml(finalPrice, price);
        stockHtml = childProducts[childProductId]["stockHtml"];
        if (childProducts[childProductId]["saleAble"] != 1) {
            jQuery('#product_addtocart_form .btn-cart').prop('disabled', true);
            if (stockHtml == '') {
                stockHtml = '<span class="icon-stock icon-out-stock"></span><span class="stock-status">Out of Stock</span>';
            }
        } else {
            jQuery('#product_addtocart_form .btn-cart').prop('disabled', false);
        }
        jQuery('.product-view .product-custom-stock-status').html(stockHtml);
        jQuery('.product-view .product-custom-stock-status').show();
        optionsPrice.productPrice = finalPrice;
        optionsPrice.productOldPrice = price;
        optionsPrice.reload();
        optionsPrice.reloadPriceLabels(true);
        optionsPrice.updateSpecialPriceDisplay(price, finalPrice);

        this.updateProductShortDescription(childProductId);
        this.updateProductDescription(childProductId);
        this.updateProductName(childProductId);
        this.updateProductAttributes(childProductId);
        this.updateFormProductId(childProductId);
        this.addParentProductIdToCartForm(this.config.productId);
        //this.showCustomOptionsBlock(childProductId, this.config.productId);

    } else {
        jQuery('#product-options-wrapper select.required-entry').each(function(){
            if (jQuery(this).val() == null) {
                jQuery(this).val('');
                jQuery(this).find('option:first').prop("selected", true);
                jQuery.uniform.update(this);
                jQuery('.not-available').removeClass('not-available');
                jQuery('.product-view .price-box').html('');
                jQuery('.product-view .price-box-configurable').show();
            }
        });
    }
};



Product.Config.prototype.updateProductImage = function(productId) {
    var imageUrl = this.config.imageUrl;
    if(productId && this.config.childProducts[productId].imageUrl) {
        imageUrl = this.config.childProducts[productId].imageUrl;
    }

    if (!imageUrl) {
        return;
    }

    if($('image')) {
        $('image').src = imageUrl;
    } else {
        $$('#product_addtocart_form p.product-image img').each(function(el) {
            var dims = el.getDimensions();
            el.src = imageUrl;
            el.width = dims.width;
            el.height = dims.height;
        });
    }
};

Product.Config.prototype.updateProductName = function(productId) {
    var productName = this.config.productName;
    if (productId && this.config.childProducts[productId].productName) {
        productName = this.config.childProducts[productId].productName;
    }
    $$('#product_addtocart_form div.product-name h1').each(function(el) {
        el.innerHTML = productName;
    });
};

Product.Config.prototype.updateProductShortDescription = function(productId) {
    var shortDescription = this.config.shortDescription;
    if (productId && this.config.childProducts[productId].shortDescription) {
        shortDescription = this.config.childProducts[productId].shortDescription;
    }
    $$('#product_addtocart_form div.short-description div.std').each(function(el) {
        el.innerHTML = shortDescription;
    });
};

Product.Config.prototype.updateProductDescription = function(productId) {
    var description = this.config.description;
    if (productId && this.config.childProducts[productId].description) {
        description = this.config.childProducts[productId].description;
    }
    $$('div.box-description div.std').each(function(el) {
        el.innerHTML = description;
    });
};

Product.Config.prototype.updateProductAttributes = function(productId) {
    var productAttributes = this.config.productAttributes;
    if (productId && this.config.childProducts[productId].productAttributes) {
        productAttributes = this.config.childProducts[productId].productAttributes;
    }
    $$('div.product-collateral div.box-additional').each(function(el) {
        el.innerHTML = productAttributes;
        decorateTable('product-attribute-specs-table');
    });
};

Product.Config.prototype.showCustomOptionsBlock = function(productId, parentId) {
    var coUrl = this.config.ajaxBaseUrl + "co/?id=" + productId + '&pid=' + parentId;
    var prodForm = $('product_addtocart_form');

   if ($('SCPcustomOptionsDiv')==null) {
      return;
   }

    Effect.Fade('SCPcustomOptionsDiv', { duration: 0.5, from: 1, to: 0.5 });
    if(productId) {

        new Ajax.Updater('SCPcustomOptionsDiv', coUrl, {
          method: 'get',
          evalScripts: true,
          onComplete: function() {
              $$('span.scp-please-wait').each(function(el) {el.hide()});
              Effect.Fade('SCPcustomOptionsDiv', { duration: 0.5, from: 0.5, to: 1 });
              //prodForm.getElements().each(function(el) {el.enable()});
          }
        });
    } else {
        $('SCPcustomOptionsDiv').innerHTML = '';
        window.opConfig = new Product.Options([]);
    }
};


Product.Config.prototype.showFullImageDiv = function(productId, parentId) {
    var imgUrl = this.config.ajaxBaseUrl + "image/?id=" + productId + '&pid=' + parentId;
    var prodForm = $('product_addtocart_form');
    var destElement = false;
    var defaultZoomer = this.config.imageZoomer;

    prodForm.select('div.product-img-box').each(function(el) {
        destElement = el;
    });

    if(productId) {
        new Ajax.Updater(destElement, imgUrl, {
            method: 'get',
            evalScripts: false,
            onComplete: function() {

                if ($('image')){
                    var imgObj = new Image();
                    imgObj.src = $('image').src;
                    imgObj.onload = function() {product_zoom = new Product.Zoom('image', 'track', 'handle', 'zoom_in', 'zoom_out', 'track_hint'); };
                } else {
                    destElement.innerHTML = defaultZoomer;
                    product_zoom = new Product.Zoom('image', 'track', 'handle', 'zoom_in', 'zoom_out', 'track_hint')
                }
          }
        });
    } else {
        destElement.innerHTML = defaultZoomer;
        product_zoom = new Product.Zoom('image', 'track', 'handle', 'zoom_in', 'zoom_out', 'track_hint');
    }
};



Product.OptionsPrice.prototype.reloadPriceLabels = function(productPriceIsKnown) {
    var priceFromLabel = '';
    var prodForm = $('product_addtocart_form');

    if (!productPriceIsKnown && typeof spConfig != "undefined") {
        priceFromLabel = spConfig.config.priceFromLabel;
    }

    var priceSpanId = 'configurable-price-from-' + this.productId;
    var duplicatePriceSpanId = priceSpanId + this.duplicateIdSuffix;

    if($(priceSpanId) && $(priceSpanId).select('span.configurable-price-from-label'))
        $(priceSpanId).select('span.configurable-price-from-label').each(function(label) {
        label.innerHTML = priceFromLabel;
    });

    if ($(duplicatePriceSpanId) && $(duplicatePriceSpanId).select('span.configurable-price-from-label')) {
        $(duplicatePriceSpanId).select('span.configurable-price-from-label').each(function(label) {
            label.innerHTML = priceFromLabel;
        });
    }
};




Product.Config.prototype.configureElement = function(element) {
    this.reloadOptionLabels(element);
    if(element.value){
        this.state[element.config.id] = element.value;
        if(element.nextSetting){
            element.nextSetting.disabled = false;
            this.fillSelect(element.nextSetting);
            this.reloadOptionLabels(element.nextSetting);
            this.resetChildren(element.nextSetting);
        }
    }
    else {
        this.resetChildren(element);
    }
    this.reloadPrice();
};



Product.Config.prototype.reloadOptionLabels = function(element){
    var selectedPrice;
    var childProducts = this.config.childProducts;

    //Don't update elements that have a selected option
    if(element.options[element.selectedIndex].config){
        return;
    }

    for(var i=0;i<element.options.length;i++){
        if(element.options[i].config){
            var cheapestPid = this.getProductIdOfCheapestProductInScope("finalPrice", element.options[i].config.allowedProducts);
            var mostExpensivePid = this.getProductIdOfMostExpensiveProductInScope("finalPrice", element.options[i].config.allowedProducts);
            var cheapestFinalPrice = childProducts[cheapestPid]["finalPrice"];
            var mostExpensiveFinalPrice = childProducts[mostExpensivePid]["finalPrice"];
            element.options[i].text = this.getOptionLabel(element.options[i].config, cheapestFinalPrice, mostExpensiveFinalPrice);
        }
    }
};


Product.Config.prototype.getOptionLabel = function(option, lowPrice, highPrice){

    var str = option.label;

    if (!this.config.showPriceRangesInOptions) {
        return str;
    }

    var to = ' ' + this.config.rangeToLabel + ' ';
    var separator = ': ';

    lowPrices = this.getTaxPrices(lowPrice);
    highPrices = this.getTaxPrices(highPrice);

    if(lowPrice && highPrice){
        if (lowPrice != highPrice) {
            if (this.taxConfig.showBothPrices) {
                str+= separator + this.formatPrice(lowPrices[2], false) + ' (' + this.formatPrice(lowPrices[1], false) + ' ' + this.taxConfig.inclTaxTitle + ')';
                str+= to + this.formatPrice(highPrices[2], false) + ' (' + this.formatPrice(highPrices[1], false) + ' ' + this.taxConfig.inclTaxTitle + ')';
            } else {
                str+= separator + this.formatPrice(lowPrices[0], false);
                str+= to + this.formatPrice(highPrices[0], false);
            }
        } else {
            if (this.taxConfig.showBothPrices) {
                str+= separator + this.formatPrice(lowPrices[2], false) + ' (' + this.formatPrice(lowPrices[1], false) + ' ' + this.taxConfig.inclTaxTitle + ')';
            } else {
                str+= separator + this.formatPrice(lowPrices[0], false);
            }
        }
    }
    return str;
};



Product.Config.prototype.getTaxPrices = function(price) {
    var price = parseFloat(price);

    if (this.taxConfig.includeTax) {
        var tax = price / (100 + this.taxConfig.defaultTax) * this.taxConfig.defaultTax;
        var excl = price - tax;
        var incl = excl*(1+(this.taxConfig.currentTax/100));
    } else {
        var tax = price * (this.taxConfig.currentTax / 100);
        var excl = price;
        var incl = excl + tax;
    }

    if (this.taxConfig.showIncludeTax || this.taxConfig.showBothPrices) {
        price = incl;
    } else {
        price = excl;
    }

    return [price, incl, excl];
};



document.observe("dom:loaded", function() {

    $('product_addtocart_form').getElements().each(function(el) {
        if(el.type == 'select-one') {
            if(el.options && (el.options.length > 1)) {
                el.options[0].selected = true;
                spConfig.reloadOptionLabels(el);
            }
        }
    });
});
