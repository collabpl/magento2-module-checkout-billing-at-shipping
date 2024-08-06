<?php
/**
 * @category  Collab
 * @package   Collab\CheckoutBillingAtShipping
 * @author    Marcin Jędrzejewski <m.jedrzejewski@collab.pl>
 * @copyright 2024 Collab
 * @license   MIT
 */

declare(strict_types=1);

namespace Collab\CheckoutBillingAtShipping\Block\LayoutProcessor\Checkout;

use Collab\CheckoutBillingAtShipping\Api\Data\ConfigInterface;
use Magento\Checkout\Block\Checkout\AttributeMerger;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Customer\Model\Options;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Ui\Component\Form\AttributeMapper;

class BillingFormHandler implements LayoutProcessorInterface
{
    public function __construct(
        protected ConfigInterface $config,
        protected AttributeMetadataDataProvider $attributeMetadataDataProvider,
        protected AttributeMapper $attributeMapper,
        protected AttributeMerger $merger,
        protected CheckoutSession $checkoutSession,
        protected ArrayManager $arrayManager,
        protected Quote $quote,
        protected Options $options
    ) {
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function process($jsLayout): array
    {
        if (!$this->config->shouldMoveBillingUnderShipping() || $this->getQuote()->isVirtual()) {
            return $jsLayout;
        }

        $jsLayout = $this->cleanUpOldBillingForms($jsLayout);

        return $this->createAndInsertNewBillingForm($jsLayout);
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    protected function getQuote(): CartInterface|Quote
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }

        return $this->quote;
    }

    protected function cleanUpOldBillingForms(array $jsLayout): array
    {
        if ($this->arrayManager->exists(ConfigInterface::XML_PATH_NATIVE_BILLING_ADDRESS_FORM, $jsLayout)) {
            $jsLayout = $this->arrayManager->remove(
                ConfigInterface::XML_PATH_NATIVE_BILLING_ADDRESS_FORM, $jsLayout
            );
        }

        $billingForms = $this->arrayManager->get(ConfigInterface::XML_PATH_NATIVE_PAYMENT_LIST, $jsLayout);
        if ($billingForms) {
            foreach ($billingForms as $billingFormsKey => $billingForm) {
                if ($billingFormsKey != 'before-place-order') {
                    $jsLayout = $this->arrayManager->remove(
                        ConfigInterface::XML_PATH_NATIVE_PAYMENT_LIST . '/' . $billingFormsKey, $jsLayout
                    );
                }
            }
        }

        return $jsLayout;
    }

    /**
     * @throws LocalizedException
     */
    protected function createAndInsertNewBillingForm(array $jsLayout): array
    {
        $attributesToConvert = [
            'prefix' => [$this->options, 'getNamePrefixOptions'],
            'suffix' => [$this->options, 'getNameSuffixOptions'],
        ];

        $elements = $this->getAddressAttributes();
        $elements = $this->convertElementsToSelect($elements, $attributesToConvert);

        return $this->arrayManager->set(
            ConfigInterface::XML_PATH_BILLING_ADDRESS_DESTINATION,
            $jsLayout,
            $this->getCustomBillingAddressComponent($elements)
        );
    }

    /** @noinspection DuplicatedCode */
    private function convertElementsToSelect($elements, $attributesToConvert)
    {
        $codes = array_keys($attributesToConvert);
        foreach (array_keys($elements) as $code) {
            if (!in_array($code, $codes)) {
                continue;
            }
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $options = call_user_func($attributesToConvert[$code]);
            if (!is_array($options)) {
                continue;
            }
            $elements[$code]['dataType'] = 'select';
            $elements[$code]['formElement'] = 'select';

            foreach ($options as $value) {
                $elements[$code]['options'][] = [
                    'value' => $value,
                    'label' => $value,
                ];
            }
        }

        return $elements;
    }

    /**
     * @throws LocalizedException
     * @noinspection DuplicatedCode
     */
    protected function getAddressAttributes(): array
    {
        /** @var AttributeInterface[] $attributes */
        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer_address',
            'customer_register_address'
        );

        $elements = [];
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            if ($attribute->getIsUserDefined()) {
                continue;
            }
            $elements[$code] = $this->attributeMapper->map($attribute);
            if (isset($elements[$code]['label'])) {
                $label = $elements[$code]['label'];
                $elements[$code]['label'] = __($label);
            }
        }
        return $elements;
    }

    protected function getCustomBillingAddressComponent($elements): array
    {
        return [
            'component' => 'Collab_CheckoutBillingAtShipping/js/view/billing-address',
            'displayArea' => 'billing-address',
            'provider' => 'checkoutProvider',
            'deps' => ['checkoutProvider'],
            'dataScopePrefix' => 'billingAddress',
            'children' => [
                'form-fields' => [
                    'component' => 'uiComponent',
                    'displayArea' => 'additional-fieldsets',
                    'children' => $this->merger->merge(
                        $elements,
                        'checkoutProvider',
                        'billingAddress',
                        [
                            'country_id' => [
                                'sortOrder' => 85
                            ],
                            'region' => [
                                'visible' => false
                            ],
                            'region_id' => [
                                'component' => 'Magento_Ui/js/form/element/region',
                                'config' => [
                                    'template' => 'ui/form/field',
                                    'elementTmpl' => 'ui/form/element/select',
                                    'customEntry' => 'billingAddress.region'
                                ],
                                'validation' => [
                                    'required-entry' => true
                                ],
                                'filterBy' => [
                                    'target' => '${ $.provider }:${ $.parentScope }.country_id',
                                    'field' => 'country_id'
                                ]
                            ],
                            'postcode' => [
                                'component' => 'Magento_Ui/js/form/element/post-code',
                                'validation' => [
                                    'required-entry' => true
                                ]
                            ],
                            'company' => [
                                'validation' => [
                                    'min_text_length' => 0
                                ]
                            ]
                        ]
                    )
                ]
            ]
        ];
    }
}
