<?php
/**
 * @category  Collab
 * @package   Collab\CheckoutBillingAtShipping
 * @author    Marcin Jędrzejewski <m.jedrzejewski@collab.pl>
 * @copyright 2024 Collab
 * @license   MIT
 */

declare(strict_types=1);

namespace Collab\CheckoutBillingAtShipping\Api\Data;

interface ConfigInterface
{
    public const XML_PATH_BILLING_ADDRESS_DESTINATION = 'components/checkout/children/steps/children/shipping-step/children/shippingAddress/children/billing-address';
    public const XML_PATH_NATIVE_BILLING_ADDRESS_FORM = 'components/checkout/children/steps/children/billing-step/children/payment/children/afterMethods/children/billing-address-form';
    public const XML_PATH_NATIVE_PAYMENT_LIST = 'components/checkout/children/steps/children/billing-step/children/payment/children/payments-list/children';
    public const XML_PATH_SHOULD_MOVE_BILLING = "collab_checkout_configuration/billing_at_shipping/move";

    public function shouldMoveBillingUnderShipping(): bool;
}
