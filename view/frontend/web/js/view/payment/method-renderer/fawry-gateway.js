define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/action/place-order'
    ],
    function (
        $,
        Component,
        quote,
        fullScreenLoader,
        setPaymentInformationAction,
        placeOrder
    ) {

        'use strict';
        return Component.extend({

            defaults: {
                template: 'Tiefanovic_FawryGateway/payment/fawry'
            },
            getCode: function() {
                return 'fawrygateway';
            },
            isActive: function() {
                return window.checkoutConfig.payment.fawryGateway.isActive;
            },
            context: function() {
                return this;
            },
            redirectAfterPlaceOrder: false,

            placeOrder: function () {

                var self = this;
                var paymentData = quote.paymentMethod();
                //delete title from paymentData if exist
                paymentData = JSON.parse(JSON.stringify(paymentData));
                delete paymentData['title'];
                
                var messageContainer = this.messageContainer;
                fullScreenLoader.startLoader();
                this.isPlaceOrderActionAllowed(false);
                $.when(setPaymentInformationAction(this.messageContainer, {
                    'method': self.getCode()
                })).done(function () {
                    $.when(placeOrder(paymentData, messageContainer)).done(function () {
                        $.mage.redirect(window.checkoutConfig.payment.fawryGateway.redirectUrl);
                    });
                }).fail(function () {
                    self.isPlaceOrderActionAllowed(true);
                }).always(function(){
                    fullScreenLoader.stopLoader();
                });
            },
            getInstructions: function () {
                return window.checkoutConfig.payment.fawryGateway.instructions;
            }
        });
    }
);
