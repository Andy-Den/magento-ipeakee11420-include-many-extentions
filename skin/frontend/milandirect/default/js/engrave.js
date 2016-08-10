/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    design
 * @package     base_default
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */
if(typeof Product=='undefined') {
    var Product = {};
}
var current_engrave = {};
/**************************** BUNDLE PRODUCT **************************/
Product.Engrave = Class.create();
Product.Engrave.prototype = {
    initialize: function(config){
        this.config = config;
		this.onComplete = this.setConfiguration.bindAsEventListener(this);
        this.reloadPrice();
		this.spConfig = '';
		this.productId = '';
		this.spConfigIndex = 0;
		this.containerSuffix = '';
		this.error = false;
		this.personalize = false;
		
    },
	_disableEnableAll: function(element, isDisabled) {
        var descendants = element.descendants();
        for (var k in descendants) {
            descendants[k].disabled = isDisabled;
        }
        element.disabled = isDisabled;
    },
	setLoadWaiting: function(enable) {
		if (enable) {
			var container = $('productView'+this.containerSuffix);
            container.setStyle({opacity:.9});
            this._disableEnableAll(container, true);
            Element.show('loadingmask');
        }
		else {
			if(!this.error){
				var container = $('productView'+this.containerSuffix);
				container.setStyle({opacity:1});
				this._disableEnableAll(container, false);
				Element.hide('loadingmask');
			}else{
				var container = $('productView'+this.containerSuffix);
				container.setStyle({opacity:.5});
				this._disableEnableAll(container, true);
				Element.hide('loadingmask');
			}
        }
    },
    changeSelection: function(selection, part1, part2){
    },
	setConfiguration: function(transport){
		current_engrave = this;
		this.setLoadWaiting(false);
		this.spConfig = new Product.Config(this.config);
		this.spConfig.settings[0].config.defaultoption = this.spConfig.settings[0].config.options[0].id
		this.setDefaultConfigOptions();
		if (!this.productId) {
			return false;
		}
		var currentAction = $('product_addtocart_form').action;
		
		newcurrentAction = currentAction.sub(/product\/\d+\//, 'product/' + this.productId + '/');
		$('product_addtocart_form').action = newcurrentAction;
		$('product_addtocart_form').product.value = this.productId;
		$('personalize').checked = this.personalize;
		$('customize').show();
		$('no-customize').hide();
		this.reloadSelectionPrice();
		this.spConfigIndex = 0;
    },
	setDefaultConfigOptions: function(){ 
		if (this.spConfigIndex >= this.spConfig.settings.length)
		{
			return; // stop
		}
		var spi = this.spConfigIndex;
		var obj = this.spConfig.settings[this.spConfigIndex];
		if (this.spConfig.settings[spi].config.defaultoption)
		{
			var defaultValue = this.spConfig.settings[spi].config.defaultoption;
			for (var spj=0; spj<this.spConfig.settings[spi].options.length; ++spj)
			{
				if (this.spConfig.settings[spi].options[spj].value == defaultValue)
				{
					this.spConfig.settings[spi].selectedIndex = spj;
					Event.observe(obj, "change", function(){});
					
					if (document.createEventObject)
					{
						// Dispatch for IE
						var evt = document.createEventObject();
						obj.fireEvent("onchange", evt);
					}
					else
					{
						// Dispatch for firefox + others
						var evt = document.createEvent("HTMLEvents");
						evt.initEvent("change", true, true ); // event type, bubbling, cancelable
						!obj.dispatchEvent(evt);
					}
					break;
				}
			}
		}
		//++this.spConfigIndex;
		//window.setTimeout("this.setDefaultConfigOptions()", 1); // Add a small delay before moving onto the next option
    },
	loadSelection: function(productId){
		
		this.productId = productId;
		var coUrl = this.config.engraveBaseUrl + "co/?id=" + productId;
		//var coUrl = "http:\/\/64.39.29.8\/silvergallery\/index.php\/gc\/ajax\/" + "co/?id=" + productId;
		/*
		if ($('GCproductattributeDiv')==null) {
		  return;
		}
		
		Effect.Fade('GCproductattributeDiv', { duration: 0.5, from: 1, to: 0.5 });
		
		if(productId) {
			//Uncomment the line below if you want an ajax loader to appear while any custom
			//options are being loaded.
			Element.show('loadingmask');
			new Ajax.Updater('GCproductattributeDiv', coUrl, {
			  method: 'get',
			  evalScripts: true,
			  //insertion: Insertion.After,
			  onComplete: function() {
				  Element.hide('loadingmask');
				  Effect.Fade('GCproductattributeDiv', { duration: 0.5, from: 0.5, to: 1 });
			  }
			});
		} else {
			$('GCproductattributeDiv').innerHTML = '';
			window.opConfig = new Product.Options([]);
		}
		*/
		//var spConfig = new Product.Config(this.config);
		/*Please note that the URL is created in reloadurl. Also see that the response text will be echoed in div with id=output-div*/
		//var reloadurl = '<?php echo $this->getUrl('router/controller/action') ?>';
		this.setLoadWaiting(true);
		new Ajax.Request(coUrl, {
			method: 'post',
			parameters: productId,
			evalJS: 'force',
			onComplete: this.onComplete,
			onSuccess: function(response){
				$('GCproductattributeDiv').innerHTML = "";
				$('GCproductattributeDiv').innerHTML = response.responseText;
			}
		});
		
	},

    reloadPrice: function() { 
        var calculatedPrice = 0;
        var dispositionPrice = 0;
        for (var option in this.config.selected) {
            if (this.config.options[option]) {
                for (var i=0; i < this.config.selected[option].length; i++) {
                    var prices = this.selectionPrice(option, this.config.selected[option][i]);
                    calculatedPrice += Number(prices[0]);
                    dispositionPrice += Number(prices[1]);
                }
            }
        }

        optionsPrice.changePrice('bundle', calculatedPrice);
        optionsPrice.changePrice('nontaxable', dispositionPrice);
        optionsPrice.reload();
        return calculatedPrice;
    },

    reloadSelectionPrice: function() { 
		if (this.config.basePrice) {
			var priceformat = this.spConfig.formatPrice(this.config.basePrice, false);
			$$('div.price-box-bundle').each(function(label) {
				label.innerHTML = '<span class="left"><strong>Item Price:&nbsp;</strong></span><div class="price-box"><span class="price">'+priceformat+'<span></div>';
			});
		}
    },

    selectionPrice: function(optionId, selectionId) {
        if (selectionId == '' || selectionId == 'none') {
            return 0;
        }
        var qty = null;
        if (this.config.options[optionId].selections[selectionId].customQty == 1 && !this.config['options'][optionId].isMulti) {
            if ($('bundle-option-' + optionId + '-qty-input')) {
                qty = $('bundle-option-' + optionId + '-qty-input').value;
            } else {
                qty = 1;
            }
        } else {
            qty = this.config.options[optionId].selections[selectionId].qty;
        }

        if (this.config.priceType == '0') {
            price = this.config.options[optionId].selections[selectionId].price;
            tierPrice = this.config.options[optionId].selections[selectionId].tierPrice;

            for (var i=0; i < tierPrice.length; i++) {
                if (Number(tierPrice[i].price_qty) <= qty && Number(tierPrice[i].price) <= price) {
                    price = tierPrice[i].price;
                }
            }
        } else {
            selection = this.config.options[optionId].selections[selectionId];
            if (selection.priceType == '0') {
                price = selection.priceValue;
            } else {
                price = (this.config.basePrice*selection.priceValue)/100;
            }
        }
        //price += this.config.options[optionId].selections[selectionId].plusDisposition;
        //price -= this.config.options[optionId].selections[selectionId].minusDisposition;
        //return price*qty;
        var disposition = this.config.options[optionId].selections[selectionId].plusDisposition +
            this.config.options[optionId].selections[selectionId].minusDisposition;

        if (this.config.specialPrice) {
            newPrice = (price*this.config.specialPrice)/100;
            newPrice = (Math.round(newPrice*100)/100).toString();
            price = Math.min(newPrice, price);
        }
        var result = new Array(price*qty, disposition*qty);
        return result;
    },

    populateQty: function(optionId, selectionId){
        if (selectionId == '' || selectionId == 'none') {
            this.showQtyInput(optionId, '0', false);
            return;
        }
        if (this.config.options[optionId].selections[selectionId].customQty == 1) {
            this.showQtyInput(optionId, this.config.options[optionId].selections[selectionId].qty, true);
        } else {
            this.showQtyInput(optionId, this.config.options[optionId].selections[selectionId].qty, false);
        }
    },

    showQtyInput: function(optionId, value, canEdit) {
        elem = $('bundle-option-' + optionId + '-qty-input');
        elem.value = value;
        elem.disabled = !canEdit;
        if (canEdit) {
            elem.removeClassName('qty-disabled');
        } else {
            elem.addClassName('qty-disabled');
        }
    },

    changeOptionQty: function (element, event) {
        var checkQty = true;
        if (typeof(event) != 'undefined') {
            if (event.keyCode == 8 || event.keyCode == 46) {
                checkQty = false;
            }
        }
        if (checkQty && (Number(element.value) == 0 || isNaN(Number(element.value)))) {
            element.value = 1;
        }
        parts = element.id.split('-');
        optionId = part2;
        if (!this.config['options'][optionId].isMulti) {
            selectionId = this.config.selected[optionId][0];
            this.config.options[optionId].selections[selectionId].qty = element.value*1;
            this.reloadPrice();
        }
    },

    validationCallback: function (elmId, result){
        if (elmId == undefined || $(elmId) == undefined) {
            return;
        }
        var container = $(elmId).up('ul.options-list');
        if (typeof container != 'undefined') {
            if (result == 'failed') {
                container.removeClassName('validation-passed');
                container.addClassName('validation-failed');
            } else {
                container.removeClassName('validation-failed');
                container.addClassName('validation-passed');
            }
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
	
		for(var s=0, len= spConfig.settings.length-1; s<=len; s++) {
			if (spConfig.settings[s].selectedIndex <= 0){
				break;
			}
			var selected = spConfig.settings[s].options[spConfig.settings[s].selectedIndex];
			if (s==0 && allowedProducts.length == 0){
				allowedProducts = selected.config.allowedProducts;
			} else {
			   // alert("merging: " + allowedProducts.inspect() + " with: " + selected.config.allowedProducts.inspect());
				allowedProducts = allowedProducts.intersect(selected.config.allowedProducts).uniq();
			   // alert("to give: " + allowedProducts.inspect());
			}
		}
	
		//If we can't find any products (because nothing's been selected most likely)
		//then just use all product ids.
		if ((typeof allowedProducts == 'undefined') || (allowedProducts.length == 0)) {
			productIds = Object.keys(childProducts);
		} else {
			productIds = allowedProducts;
		}
		return productIds;
	}
	
}
