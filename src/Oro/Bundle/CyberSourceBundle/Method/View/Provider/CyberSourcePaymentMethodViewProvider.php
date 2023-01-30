<?php

namespace Oro\Bundle\CyberSourceBundle\Method\View\Provider;

use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\CyberSourceBundle\Method\Config\Provider\CyberSourceConfigProviderInterface;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Handler\CheckoutApiHandler;
use Oro\Bundle\CyberSourceBundle\Method\View\Factory\CyberSourcePaymentMethodViewFactoryInterface;
use Oro\Bundle\PaymentBundle\Method\View\AbstractPaymentMethodViewProvider;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Provider for retrieving payment method view instances
 */
class CyberSourcePaymentMethodViewProvider extends AbstractPaymentMethodViewProvider
{
    /** @var CyberSourcePaymentMethodViewFactoryInterface */
    protected $factory;

    /** @var CyberSourceConfigProviderInterface */
    protected $configProvider;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var CheckoutApiHandler */
    protected $checkoutApiHandler;

    /**
     * @param CyberSourceConfigProviderInterface $configProvider
     * @param CyberSourcePaymentMethodViewFactoryInterface $factory
     * @param FormFactoryInterface $formFactory
     * @param CheckoutApiHandler $checkoutApiHandler
     */
    public function __construct(
        CyberSourceConfigProviderInterface $configProvider,
        CyberSourcePaymentMethodViewFactoryInterface $factory,
        FormFactoryInterface $formFactory,
        CheckoutApiHandler $checkoutApiHandler
    ) {
        $this->factory = $factory;
        $this->configProvider = $configProvider;
        $this->formFactory = $formFactory;
        $this->checkoutApiHandler = $checkoutApiHandler;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function buildViews()
    {
        $configs = $this->configProvider->getPaymentConfigs();
        foreach ($configs as $config) {
            $this->addPaymentMethodView($config);
        }
    }

    /**
     * @param CyberSourceConfigInterface $config
     */
    protected function addPaymentMethodView(CyberSourceConfigInterface $config)
    {
        $this->addView(
            $config->getPaymentMethodIdentifier(),
            $this->factory->create(
                $config,
                $this->formFactory,
                $this->checkoutApiHandler
            )
        );
    }
}
