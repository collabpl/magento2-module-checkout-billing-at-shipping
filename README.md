# Magento 2 Checkout Billing at Shipping

The Collab_CheckoutBillingAtShipping module allows You to move the billing address form just below the shipping address form on the checkout page.

## Configuration
You can configure the module in the admin panel under `Stores > Configuration > Collab Extensions > Checkout Billing At Shipping`.

| Tab     | Config Field                                | Description                                                                                                                            |
|---------|---------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------|
| Checkout Billing At Shipping | Move Billing Below Shipping (bool: Select)  | Determines if we should show billing address form just under shipping address at 1st OPC step or we should keep it's native placement. |

## Why choose this extension over other solutions?
We don't believe in efficient modules which have tons of options - simple as that - modules which have multiple
options, are prepared for many integrations always have some performance footprint for application. Having this
in mind we are trying to provide simple, portable and independent modules which sometimes require some basic Magento 2 development
skills.

## Screenshots
| Guest                    | User                   |
|--------------------------|------------------------|
| ![Guest](docs/guest.gif) | ![User](docs/user.gif) |

## Installation details
```bash
composer req collab/module-checkout-billing-at-shipping
bin/magento setup:upgrade
```
