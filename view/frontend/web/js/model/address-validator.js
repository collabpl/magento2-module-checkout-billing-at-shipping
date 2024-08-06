/**
 * @category  Collab
 * @package   Collab\CheckoutBillingAtShipping
 * @author    Marcin Jędrzejewski <m.jedrzejewski@collab.pl>
 * @copyright 2024 Collab
 * @license   MIT
 */

define([
    'Collab_CheckoutBillingAtShipping/js/view/billing-address',
    'ko',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/create-billing-address',
    'Magento_Checkout/js/action/select-billing-address',
    'underscore'
], function(
    billingAddress,
    ko,
    customer,
    addressList,
    addressConverter,
    quote,
    createBillingAddress,
    selectBillingAddress,
    _
) {
    'use strict';

    return {
        validateBillingInformation: (isFormInline, source) => {
            if (billingAddress().isAddressSameAsShipping()) {
                if (isFormInline) {
                    let shippingAddress = quote.shippingAddress(),
                        addressData = addressConverter.formAddressDataToQuoteAddress(
                            source.get('shippingAddress')
                        );

                    for (let field in addressData) {
                        // noinspection DuplicatedCode
                        if (addressData.hasOwnProperty(field) && //eslint-disable-line max-depth
                            shippingAddress.hasOwnProperty(field) &&
                            typeof addressData[field] != 'function' &&
                            _.isEqual(shippingAddress[field], addressData[field])
                        ) {
                            shippingAddress[field] = addressData[field];
                        } else if (typeof addressData[field] != 'function' &&
                            !_.isEqual(shippingAddress[field], addressData[field])) {
                            shippingAddress = addressData;
                            break;
                        }
                    }

                    if (customer.isLoggedIn()) {
                        shippingAddress.save_in_address_book = 1;
                    }
                } else {
                    selectBillingAddress(quote.shippingAddress());
                }

                return true;
            }

            let selectedAddress = document.querySelector(billingAddress().addressSelectSelector)?.value;
            if(selectedAddress) {
                return addressList.some(addressFromList => {
                    if (selectedAddress == addressFromList.customerAddressId) { //eslint-disable-line eqeqeq
                        selectBillingAddress(addressFromList);
                        return true;
                    }
                    return false;
                });
            }

            source.set('params.invalid', false);
            source.trigger('billingAddress.data.validate');

            if (source.get('params.invalid')) {
                return false;
            }

            let addressData = source.get('billingAddress'),
                newBillingAddress;

            newBillingAddress = createBillingAddress(addressData);
            selectBillingAddress(newBillingAddress);
            quote.shippingAddress().canUseForBilling(false);

            return true;
        }
    }
});
