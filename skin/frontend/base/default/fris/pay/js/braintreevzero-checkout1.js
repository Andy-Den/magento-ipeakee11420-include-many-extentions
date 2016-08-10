
function braintreeNonceReceived(info) {

    var hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'payment_method_nonce';
    hiddenInput.value = info.nonce;
    $(Fris.paymentFormId).appendChild(hiddenInput);

    if (typeof Lightcheckout !== 'undefined') {
        // GoMage
        $('submit-btn').removeClassName('disabled').disabled = false;
    }

    Fris.checkoutFormHandler.notify(info);
    if (Fris.paymentVerificationTime === 'order_submit') {
        Fris.checkoutFormHandler.placeOrder();
        return;
    }

    var isPlainCore = $('checkout-step-review');
    if (isPlainCore && (typeof payment !== 'undefined') && payment.save) {
        payment.save();
    }
}
