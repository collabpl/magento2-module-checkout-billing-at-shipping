<?php
/**
 * @category  Collab
 * @package   Collab\CheckoutBillingAtShipping
 * @author    Marcin Jędrzejewski <m.jedrzejewski@collab.pl>
 * @copyright 2024 Collab
 * @license   MIT
 */

declare(strict_types=1);

namespace Collab\CheckoutBillingAtShipping\Model\Data;

use Collab\CheckoutBillingAtShipping\Api\Data\ConfigInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config implements ConfigInterface, ConfigProviderInterface
{
    public function __construct(
        private ScopeConfigInterface $scopeConfig
    ) {
    }

    public function shouldMoveBillingUnderShipping(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SHOULD_MOVE_BILLING, ScopeInterface::SCOPE_STORE);
    }

    public function getConfig(): array
    {
        return [
            'collab' => [
                'checkout' => [
                    'billing_at_shipping' => [
                        'move' => $this->shouldMoveBillingUnderShipping()
                    ]
                ]
            ]
        ];
    }
}
