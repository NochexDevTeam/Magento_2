/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
[
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/action/select-payment-method',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/payment/additional-validators',
    'mage/url',
],
function (
    $,
    Component,
    placeOrderAction,
    selectPaymentMethodAction,
    customer,
    checkoutData,
    additionalValidators,
    url) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Nochexapc_Nochex/payment/nochex'
            }, 

            /** Returns send check to info */
            getMailingAddress: function() {
                return window.checkoutConfig.payment.nochex.mailingAddress;
            },

            /** Returns payable to info */
            getPayableTo: function() {
                return window.checkoutConfig.payment.nochex.payableTo;
            },
			
			afterPlaceOrder: function () {
                window.location.replace(url.build('Nochex/success/success'));
            },
        });
    }
);
