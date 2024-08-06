/**
 * @category  Collab
 * @package   Collab\CheckoutBillingAtShipping
 * @author    Marcin Jędrzejewski <m.jedrzejewski@collab.pl>
 * @copyright 2024 Collab
 * @license   MIT
 */

define([
    'jquery',
    'Collab_CheckoutBillingAtShipping/js/model/address-validator',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/checkout-data',
    'uiRegistry'
], function(
    $,
    addressValidator,
    setShippingInformationAction,
    stepNavigator,
    checkoutData,
    registry
) {
    'use strict';

    return function (target) {
        return target.extend({
            defaults: {
                template: 'Collab_CheckoutBillingAtShipping/shipping-billing-combined'
            },
            renderBillingAtShipping: window.checkoutConfig.collab.checkout.billing_at_shipping.move || false,

            setShippingInformation: () => {
                if (!this.renderBillingAtShipping) {
                    this._super();
                    return;
                }

                if (
                    this.validateShippingInformation()
                    && addressValidator.validateBillingInformation(this.isFormInline, this.source)
                ) {
                    registry.async('checkoutProvider')(checkoutProvider => {
                        let shippingAddressData = checkoutData.getShippingAddressFromData();

                        if (shippingAddressData) {
                            checkoutProvider.set(
                                'shippingAddress',
                                $.extend(true, {}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                            );
                        }
                    });

                    setShippingInformationAction().done(
                        () => {
                            stepNavigator.next();
                        }
                    );
                }
            }
        });
    }
});
