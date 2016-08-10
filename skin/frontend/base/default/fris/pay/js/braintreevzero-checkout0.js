
if (typeof Fris === 'undefined') {
    Fris = {};
}
Fris.CheckoutFormHandler = Class.create();

Fris.CheckoutFormHandler.prototype = {

    initialize: function(paymentVerificationTime) {
        if (paymentVerificationTime === 'payment_button') {
            // NoOp.
            return;
        }
        // Avoiding $
        var braintreeDiv = document.getElementById('dropin-braintree');
        if (!braintreeDiv) {
            // Wrong place or wrong time, e.g. Mage core check out.
            return;
        }
        if (paymentVerificationTime === 'payment_mouseout') {
            braintreeDiv.observe('mouseout', this.submitInterceptor.bind(this));
            return;
        }
        var submit = this.findCheckoutFormSubmit(braintreeDiv);
        if (!submit) {
            alert('ERROR: unable to identify checkout form submit button.');
            return;
        }
        // Kill any 'onclick' handlers dead.
        this.buttonStopped = submit;
        this.buttonStoppedOnClick = this.buttonStopped.onclick;
        this.buttonStopped.onclick = null;
        var registry = Element.retrieve(this.buttonStopped, 'prototype_event_registry');
        if (registry) {
            var responders = registry.get('click')
            if (responders) {
                this.buttonStoppedHandler = responders[0].handler;
                this.buttonStopped.stopObserving('click');
            }
        }
        // Put ourselves in their place
        this.buttonStopped.observe('click', this.submitInterceptor.bind(this));
    },

    findCheckoutFormSubmit: function(braintreeDiv) {
        var form = braintreeDiv.up('form');
        if (form) {
            var buttons = form.getElementsBySelector('button', 'input[type="submit"]', 'input[type="button"]');
            if (buttons.length > 0) {
                // Assume submit is last on form.
                var submit = buttons[buttons.length - 1];
                if (submit.id !== 'braintree-button-submit') {
                    return submit;
                }
                // Try next level up (eg. IWD)
                var buttons = form.up(1).getElementsBySelector('button', 'input[type="submit"]', 'input[type="button"]');
                if (buttons.length > 0) {
                    return buttons[buttons.length - 1];
                }
            }
         }
         return null;
    },

    submitInterceptor: function(event) {
        event.stop();
        var braintreeRadio = document.getElementById('p_method_braintreevzero');
        if (braintreeRadio && braintreeRadio.checked) {
            var altSubmit = document.getElementById('braintree-button-submit');
            if (altSubmit) {
                altSubmit.click();
                return;
            }
        }
        // When we're not selected or Vault is on there is no nonce to request.
        this.placeOrder();
    },

    // Should be called when nonce is received.
    placeOrder: function() {
        var btn = this.buttonStopped;
        if (!btn) return;
        btn.stopObserving('click');
        if (this.buttonStoppedHandler) {
            btn.observe('click', this.buttonStoppedHandler).click();
            this.buttonStoppedHandler = null;
        }
        else {
            if (this.buttonStoppedOnClick) {
                btn.onclick = this.buttonStoppedOnClick;
                this.buttonStoppedOnClick = null;
            }
            btn.click();
        }
        // Click only once, avoid loops.
        this.buttonStopped = null;
    },

    notify: function(info) {
        if (typeof Fris.paymentVerificationStyle === 'string') {
            if (Fris.paymentVerificationStyle.indexOf('alert') >= 0) {
                var msg = '';
                if (Fris.paymentVerificationTime === 'order_submit') {
                    msg = Translator.translate('Verified. Placing order now.');
                }
                else {
                    msg = Translator.translate('Verified. You may place your order now.');
                }
                var account = !info ? '' : (info.type === 'PayPalAccount' ? info.details.email : info.details.cardType);
                alert(account + ' ' + msg.stripTags());
            }
            if (Fris.paymentVerificationStyle.indexOf('tick') >= 0) {
                if (Fris.paymentVerificationTime === 'payment_button') {
                    $('braintree-tick').addClassName('bt-validation-passed');
                    jQuery('.onestepcheckout-threecolumns.checkoutcontainer .onestepcheckout-column-right .onestepcheckout-place-order-wrapper .button.btn-checkout').css({'opacity':'1'});
                }
                else {
                    var throbber = $('review-please-wait');
                    if (throbber) {
                        var tick = '<div id="braintree-tick" class="bt-validation-passed">' +
                          '<div class="tick-circle"></div>' +
                          '<div class="tick-stem"></div>' +
                          '<div class="tick-kick"></div>' +
                        '</div>';
                        $(throbber).update(throbber.innerHTML + tick);
                    }
                }
            }
        }
    }
};

//document.observe('dom:loaded', function() { // too early for Idev, Iksanika
Event.observe(window, 'load', function() {
    // Must call this after the checkout form 'click' observers have been defined.
    Fris.checkoutFormHandler = new Fris.CheckoutFormHandler(Fris.paymentVerificationTime);
});