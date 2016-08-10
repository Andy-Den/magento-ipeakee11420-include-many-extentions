Product.OptionsPrice.prototype.updateSpecialPriceDisplay = function(price, finalPrice) {
    var prodForm = $('product_addtocart_form'+listProductId);

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
Product.OptionsPrice.prototype.changeContainer = function(productId, addition){
    this.productId = productId;
    this.productId = this.productId+addition;

    this.containers[0] = 'product-price-' + this.productId;
    this.containers[1] = 'bundle-price-' + this.productId;
    this.containers[2] = 'price-including-tax-' + this.productId;
    this.containers[3] = 'price-excluding-tax-' + this.productId;
    this.containers[4] = 'old-price-' + this.productId;

}
Product.OptionsPrice.prototype.resetContainer = function(productId){
    this.productId = productId;
    this.containers[0] = 'product-price-' + this.productId;
    this.containers[1] = 'bundle-price-' + this.productId;
    this.containers[2] = 'price-including-tax-' + this.productId;
    this.containers[3] = 'price-excluding-tax-' + this.productId;
    this.containers[4] = 'old-price-' + this.productId;
}
if(typeof Milan_Product =='undefined') {
    var Milan_Product  = {};
}

/**************************** CONFIGURABLE PRODUCT **************************/
Milan_Product.Config = Class.create();
Milan_Product.Config.prototype = {
    initialize: function(config){
        this.config     = config;
        this.taxConfig  = this.config.taxConfig;
        var settingsClassToSelect = '.super-attribute-select_'+this.config.productId;
        this.settings   = $$(settingsClassToSelect);
        this.state      = new Hash();
        this.priceTemplate = new Template(this.config.template);
        this.prices     = config.prices;

        this.settings.each(function(element){
            Event.observe(element, 'change', this.configure.bind(this))
        }.bind(this));

        // fill state
        this.settings.each(function(element){
            var attributeId = element.id.replace(/[a-z]*/, '');
            attributeId = attributeId.replace(/_.*/, '');
            if(attributeId && this.config.attributes[attributeId]) {
                element.config = this.config.attributes[attributeId];
                element.attributeId = attributeId;
                this.state[attributeId] = false;
            }
        }.bind(this))

        // Init settings dropdown
        var childSettings = [];
        for(var i=this.settings.length-1;i>=0;i--){
            var prevSetting = this.settings[i-1] ? this.settings[i-1] : false;
            var nextSetting = this.settings[i+1] ? this.settings[i+1] : false;
            if(i==0){
                this.fillSelect(this.settings[i])
            }
            else {
                this.settings[i].disabled=true;
            }
            $(this.settings[i]).childSettings = childSettings.clone();
            $(this.settings[i]).prevSetting   = prevSetting;
            $(this.settings[i]).nextSetting   = nextSetting;
            childSettings.push(this.settings[i]);
        }

        // Set default values - from config and overwrite them by url values
        if (config.defaultValues) {
            this.values = config.defaultValues;
        }

        var separatorIndex = window.location.href.indexOf('#');
        if (separatorIndex != -1) {
            var paramsStr = window.location.href.substr(separatorIndex+1);
            var urlValues = paramsStr.toQueryParams();
            if (!this.values) {
                this.values = {};
            }
            for (var i in urlValues) {
                this.values[i] = urlValues[i];
            }
        }

        this.configureForValues();
        document.observe("dom:loaded", this.configureForValues.bind(this));
    },

    configureForValues: function () {
        if (this.values) {
            this.settings.each(function(element){
                var attributeId = element.attributeId;
                element.value = (typeof(this.values[attributeId]) == 'undefined')? '' : this.values[attributeId];
                this.configureElement(element);
            }.bind(this));
        }
    },

    configure: function(event){
        var element = Event.element(event);
        this.configureElement(element);
    },

    configureElement : function(element) {
        this.reloadOptionLabels(element);
        if(element.value){
            this.state[element.config.id] = element.value;
            if(element.nextSetting){
                element.nextSetting.disabled = false;
                this.fillSelect(element.nextSetting);
                this.resetChildren(element.nextSetting);
            }
        }
        else {
            this.resetChildren(element);
        }
    },

    reloadOptionLabels: function(element){
        var selectedPrice;
        if(element.options[element.selectedIndex].config){
            selectedPrice = parseFloat(element.options[element.selectedIndex].config.price)
        }
        else{
            selectedPrice = 0;
        }
        for(var i=0;i<element.options.length;i++){
            if(element.options[i].config){
                element.options[i].text = this.getOptionLabel(element.options[i].config, element.options[i].config.price-selectedPrice);
            }
        }
    },

    resetChildren : function(element){
        if(element.childSettings) {
            for(var i=0;i<element.childSettings.length;i++){
                element.childSettings[i].selectedIndex = 0;
                element.childSettings[i].disabled = true;
                if(element.config){
                    this.state[element.config.id] = false;
                }
            }
        }
    },

    fillSelect: function(element){
        // change to loadOptions function
        this.settings.each(function(element){
            element.disabled = false;
            element.options[0] = new Option(this.config.chooseText, '');
            var attributeId = element.id.replace(/[a-z]*/, '');
            var options = this.getAttributeOptions(attributeId);
            if(options) {
                var index = 1;
                for(var i=0;i<options.length;i++){
                    options[i].allowedProducts = options[i].products.clone();
                    element.options[index] = new Option(this.getOptionLabel(options[i], options[i].price), options[i].id);
                    if (typeof options[i].price != 'undefined') {
                        element.options[index].setAttribute('price', options[i].price);
                    }
                    element.options[index].setAttribute('data-label', options[i].label.toLowerCase());
                    element.options[index].config = options[i];
                    index++;
                }
            }
            this.reloadOptionLabels(element);
        }.bind(this));
    },

    getOptionLabel: function(option, price){
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

        var str = option.label;
        if(price){
            if (this.taxConfig.showBothPrices) {
                str+= ' ' + this.formatPrice(excl, true) + ' (' + this.formatPrice(price, true) + ' ' + this.taxConfig.inclTaxTitle + ')';
            } else {
                str+= ' ' + this.formatPrice(price, true);
            }
        }
        return str;
    },

    formatPrice: function(price, showSign){
        var str = '';
        price = parseFloat(price);
        if(showSign){
            if(price<0){
                str+= '-';
                price = -price;
            }
            else{
                str+= '+';
            }
        }

        var roundedPrice = (Math.round(price*100)/100).toString();

        if (this.prices && this.prices[roundedPrice]) {
            str+= this.prices[roundedPrice];
        }
        else {
            str+= this.priceTemplate.evaluate({price:price.toFixed(2)});
        }
        return str;
    },

    clearSelect: function(element){
        for(var i=element.options.length-1;i>=0;i--){
            element.remove(i);
        }
    },

    getAttributeOptions: function(attributeId){
        if(this.config.attributes[attributeId]){
            return this.config.attributes[attributeId].options;
        }
    },
    getMatchingSimpleProduct: function(){
        var inScopeProductIds = this.getInScopeProductIds();
        if ((typeof inScopeProductIds != 'undefined') && (inScopeProductIds.length == 1)) {
            return inScopeProductIds[0];
        }
        return false;
    },
    getInScopeProductIds: function(optionalAllowedProducts) {
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
    },
    updatePriceHtml: function(finalPrice, price){
        oldPrice = '';
        specialPrice = '';
        regularPrice = '';
        if (parseFloat(finalPrice) < parseFloat(price)){
            oldPrice = '<p class="old-price"><span class="price-label"></span><span id="old-price-'+this.config.productId+'-list" class="price"></span></p>';
            specialPrice = '<p class="special-price"><span class="price-label"></span><span id="product-price-'+this.config.productId+'-list" class="price"></span></p>';
        } else {
            regularPrice = '<span class="regular-price" id="product-price-'+this.config.productId+'-list"><span class="price"></span></span>';
        }
        jQuery('#product_price_configurable_' + this.config.productId + ' .price-box').html('');
        jQuery('#product_price_configurable_' + this.config.productId + ' .price-box').append(oldPrice+specialPrice+regularPrice);

        jQuery('#product_price_configurable_' + this.config.productId).prev().remove();
        jQuery('#product_price_configurable_' + this.config.productId).show();
        setTimeout(alignGrid, 300);
    },
    addParentProductIdToCartForm: function(parentProductId) {
        if (typeof $('product_addtocart_form'+this.config.productId).cpid != 'undefined') {
            return; //don't create it if we have one..
        }
        var el = document.createElement("input");
        el.type = "hidden";
        el.name = "cpid";
        el.value = parentProductId.toString();
        $('product_addtocart_form'+this.config.productId).appendChild(el);
    },
    reloadPrice: function(){
        var childProductId = this.getMatchingSimpleProduct();
        var childProducts = this.config.childProducts;
        var notSelect = false;
        jQuery('#product_addtocart_form'+this.config.productId + ' select.required-entry').each(function(){
            if (jQuery(this).val() == '' || jQuery(this).val() == null){
                notSelect = true;
            }
        });
        if (childProductId && notSelect == false){
            var price = childProducts[childProductId]["price"];
            var finalPrice = childProducts[childProductId]["finalPrice"];
            jQuery('.product-price-' + this.config.productId +'-list').hide();
            this.updatePriceHtml(finalPrice, price);
            stockHtml = childProducts[childProductId]["stockHtml"];
            if (childProducts[childProductId]["saleAble"] != 1) {
                jQuery('#product_addtocart_form'+ this.config.productId +' .btn-cart').prop('disabled', true);
                if (stockHtml == '') {
                    stockHtml = '<span class="icon-stock icon-out-stock"></span><span class="stock-status">Out of Stock</span>';
                }
            } else {
                jQuery('#product_addtocart_form'+ this.config.productId +' .btn-cart').prop('disabled', false);
            }
            jQuery('.custom-stock-' + this.config.productId).html(stockHtml);
            var productId = this.config.ProductId;
            optionsPrice.changeContainer(this.config.productId, '-list');
            optionsPrice.productPrice = finalPrice;
            optionsPrice.productOldPrice = price;
            optionsPrice.reload();
            optionsPrice.updateSpecialPriceDisplay(price, finalPrice);
            this.addParentProductIdToCartForm(this.config.productId);
            optionsPrice.resetContainer(productId);
        }
    },

    reloadOldPrice: function(){
        if ($('old-price-'+this.config.productId)) {

            var price = parseFloat(this.config.oldPrice);
            for(var i=this.settings.length-1;i>=0;i--){
                var selected = this.settings[i].options[this.settings[i].selectedIndex];
                if(selected.config){
                    price+= parseFloat(selected.config.price);
                }
            }
            if (price < 0)
                price = 0;
            price = this.formatPrice(price);

            if($('old-price-'+this.config.productId)){
                $('old-price-'+this.config.productId).innerHTML = price;
            }

        }
    }
};

/** Update on quickview **/

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

Product.Config.prototype.updatePriceHtml = function(finalPrice, price){
    oldPrice = '';
    specialPrice = '';
    regularPrice = '';
    if (finalPrice < price){
        oldPrice = '<p class="old-price"><span class="price-label"></span><span id="old-price-'+ this.config.productId +'" class="price"></span></p>';
        specialPrice = '<p class="special-price"><span class="price-label"></span><span id="product-price-'+ this.config.productId +'" class="price"></span></p>';
    } else {
        regularPrice = '<span class="regular-price" id="product-price-'+ this.config.productId +'"><span class="price"></span></span>';
    }
    jQuery('.product-price-' + this.config.productId + '-range').hide();
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
