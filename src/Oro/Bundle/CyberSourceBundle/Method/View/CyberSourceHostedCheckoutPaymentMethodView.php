<?php

namespace Oro\Bundle\CyberSourceBundle\Method\View;

use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\CyberSourceBundle\Method\CyberSourcePaymentMethod;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PaymentBundle\Method\View\PaymentMethodViewInterface;

/**
 * View for CyberSource Hosted Checkout payment method
 */
class CyberSourceHostedCheckoutPaymentMethodView implements PaymentMethodViewInterface
{
    /** @var CyberSourceConfigInterface */
    protected $config;

    /**
     * @param CyberSourceConfigInterface $config
     */
    public function __construct(CyberSourceConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions(PaymentContextInterface $context)
    {
        return [
            'componentOptions' => [
                'orderIdParamName' => CyberSourcePaymentMethod::PARAM_ORDER_ID,
                'testMode' => $this->config->isTestMode(),
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getBlock()
    {
        return '_payment_methods_oro_cybersource_hosted_checkout_widget';
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return $this->config->getLabel();
    }

    /**
     * {@inheritDoc}
     */
    public function getShortLabel()
    {
        return $this->config->getShortLabel();
    }

    /**
     * {@inheritDoc}
     */
    public function getAdminLabel()
    {
        return $this->config->getAdminLabel();
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentMethodIdentifier()
    {
        return $this->config->getPaymentMethodIdentifier();
    }
}
