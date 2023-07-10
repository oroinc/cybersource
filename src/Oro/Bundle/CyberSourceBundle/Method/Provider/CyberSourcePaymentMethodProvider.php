<?php

namespace Oro\Bundle\CyberSourceBundle\Method\Provider;

use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\CyberSourceBundle\Method\Config\Provider\CyberSourceConfigProviderInterface;
use Oro\Bundle\CyberSourceBundle\Method\Factory\CyberSourcePaymentMethodFactoryInterface;
use Oro\Bundle\PaymentBundle\Method\Provider\AbstractPaymentMethodProvider;

/**
 * Provider for retrieving configured payment method instances
 */
class CyberSourcePaymentMethodProvider extends AbstractPaymentMethodProvider
{
    /** @var CyberSourcePaymentMethodFactoryInterface */
    protected $factory;

    /** @var CyberSourceConfigProviderInterface */
    protected $configProvider;

    /**
     * @param CyberSourceConfigProviderInterface $configProvider
     * @param CyberSourcePaymentMethodFactoryInterface $factory
     */
    public function __construct(
        CyberSourceConfigProviderInterface $configProvider,
        CyberSourcePaymentMethodFactoryInterface $factory
    ) {
        parent::__construct();

        $this->configProvider = $configProvider;
        $this->factory = $factory;
    }

    /**
     * {@inheritDoc}
     */
    protected function collectMethods()
    {
        $configs = $this->configProvider->getPaymentConfigs();
        foreach ($configs as $config) {
            $this->addPaymentMethod($config);
        }
    }

    /**
     * @param CyberSourceConfigInterface $config
     */
    protected function addPaymentMethod(CyberSourceConfigInterface $config)
    {
        $this->addMethod(
            $config->getPaymentMethodIdentifier(),
            $this->factory->create($config)
        );
    }
}
