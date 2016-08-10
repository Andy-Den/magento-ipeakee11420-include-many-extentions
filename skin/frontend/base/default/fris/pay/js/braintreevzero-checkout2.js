
if (Payment !== undefined && Payment.prototype.init !== undefined) {

    // Near-identical override of skin/frontend/base/default/js/opcheckout.js
    Payment.prototype.init = function () {
        this.beforeInit();
        var paymentForm = $(this.form);
        if (paymentForm.length > 0) {
            paymentForm.setAttribute('autocomplete', 'off');
            // Braintree v.zero needs form to submit normally, so don't hijack it.
            //paymentForm.observe('submit', function(event){this.save();Event.stop(event);}.bind(this));
        }
        var elements = Form.getElements(this.form);
        var container = $('payment-buttons-container');
        var method = null;
        var btvzero = null;
        for (var i = 0; i < elements.length; i++) {
            var el = elements[i];
            el.setAttribute('autocomplete', 'off');
            if (el.name === 'payment[method]') {
                // Add listener to each Payment Method radio button.
                el.observe('change', function(event) {
                    if (event.target.value === 'braintreevzero') {
                        container.hide();
                    }
                    else {
                        container.show();
                    }
                });
                if (el.value === 'braintreevzero') {
                    btvzero = el;
                }
                if (el.checked) {
                    method = el.value;
                }
            } else {
                el.disabled = true;
            }
        }
        if (!method || method === 'braintreevzero') {
            method = 'braintreevzero';
            btvzero.checked = true;
            if (Fris.paymentVerificationTime === 'payment_button' && !Fris.useVault) {
                // Hide original Continue and <<Back buttons.
                container.hide();
            }
        }
        this.switchMethod(method);
        this.afterInit();
    };
}
