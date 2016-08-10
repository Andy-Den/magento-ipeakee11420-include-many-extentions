
if (typeof Review !== 'undefined' && typeof Review.prototype.save === 'function') {
    // Near-identical override of Mage core skin/frontend/base/default/js/opcheckout.js
    // Gets called when "Place order" button is pressed.

    Review.prototype._save = function () {
        if (checkout.loadWaiting !== false) return;
        checkout.setLoadWaiting('review');
        var params = Form.serialize(payment.form);
        if (this.agreementsForm) {
            params += '&'+Form.serialize(this.agreementsForm);
        }
        params.save = true;

        if (typeof Fris.paymentVerificationStyle === 'string') {
            if (Fris.paymentVerificationStyle.indexOf('throbber_authorize') >= 0) {
                var throbber = $('review-please-wait');
                if (throbber) {
                    throbber.update(throbber.innerHTML + Translator.translate('Authorizing... '));
                }
            }
        }
        var request = new Ajax.Request(
            this.saveUrl,
            {
                method: 'post',
                parameters: params,
                onComplete: this.onComplete,
                onSuccess: this.onSave,
                onFailure: checkout.ajaxFailure.bind(checkout)
            }
        );
    },

    Review.prototype.save = function () {
        if (Fris.paymentVerificationTime === 'order_submit') {
            $('braintree-button-submit').click();
            setTimeout(function() {
                review.resetLoadWaiting();
                review._save();
            }, 1000 * Fris.btVerificationTimeout);
        }
        else {
            this._save();
        }
    };
}
