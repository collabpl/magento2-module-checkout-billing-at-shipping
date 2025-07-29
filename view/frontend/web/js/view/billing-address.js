/**
 * @category  Collab
 * @package   Collab\CheckoutBillingAtShipping
 * @author    Marcin Jędrzejewski <m.jedrzejewski@collab.pl>
 * @copyright 2024 Collab
 * @license   MIT
 */

define([
    'jquery',
    'ko',
    'Magento_Ui/js/form/form',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/checkout-data',
    'Magento_Customer/js/customer-data',
    'mage/translate',
    'underscore'
], function (
    $,
    ko,
    Component,
    customer,
    addressList,
    quote,
    selectBillingAddress,
    checkoutData,
    customerData,
    $t,
    _
) {
    'use strict';

    const newAddressOption = {
            getAddressInline: () => $t('New Address'),
            customerAddressId: null
        },
        countryData = customerData.get('directory-data'),
        addressOptions = addressList().filter(address => address.getType() === 'customer-address');

    addressOptions.push(newAddressOption);

    return Component.extend({
        defaults: {
            template: 'Collab_CheckoutBillingAtShipping/billing-address'
        },
        currentBillingAddress: quote.billingAddress,
        addressOptions: addressOptions,
        customerHasAddresses: addressOptions.length > 1,
        isAddressSameAsShipping: ko.observable(true),
        addressSelectSelector: '[name="billing_address_id"]',

        initialize: function() {
            this._super();
        },

        initObservable: function() {
            this._super()
                .observe({
                    selectedAddress: null,
                    isAddressFormVisible: false,
                    saveInAddressBook: 1,
                    isAddressFormListVisible: false
                });

            return this;
        },

        canUseShippingAddress: ko.computed(() => {
            return !quote.isVirtual() && quote.shippingAddress() && quote.shippingAddress().canUseForBilling();
        }),

        addressOptionsText: function(address) {
            return address.getAddressInline();
        },

        useShippingAddress: function() {
            if (this.isAddressSameAsShipping()) {
                selectBillingAddress(quote.shippingAddress());
                this.isAddressFormVisible(false);
                this.isAddressFormListVisible(false);
            } else {
                this.isAddressSameAsShipping(false);
                this.isAddressFormVisible(addressOptions.length === 1);
                this.isAddressFormListVisible(addressOptions.length > 1);
            }

            checkoutData.setSelectedBillingAddress(null);

            return true;
        },

        onAddressChange: function(address) {
            this.isAddressFormVisible(!address);
        },

        getCountryName: function(countryId) {
            return countryData()[countryId] ? countryData()[countryId].name : '';
        },

        getCode: function(parent) {
            return _.isFunction(parent.getCode) ? parent.getCode() : 'shared';
        }
    });
});
