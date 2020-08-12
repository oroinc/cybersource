<?php

namespace Oro\Bundle\CyberSourceBundle\Method\Factory;

use Oro\Bundle\CyberSourceBundle\Entity\CyberSourceSettings;
use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\CyberSourceBundle\Method\CyberSourcePaymentMethod;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Executor\PaymentActionExecutorInterface;

/**
 * Factory creates payment method instances based on configuration
 */
class CyberSourcePaymentMethodFactory implements CyberSourcePaymentMethodFactoryInterface
{
    /** @var PaymentActionExecutorInterface */
    protected $hostedCheckoutPaymentActionExecutor;

    /** @var PaymentActionExecutorInterface */
    protected $checkoutApiPaymentActionExecutor;

    /**
     * @param PaymentActionExecutorInterface $hostedCheckoutPaymentActionExecutor
     * @param PaymentActionExecutorInterface $checkoutApiPaymentActionExecutor
     */
    public function __construct(
        PaymentActionExecutorInterface $hostedCheckoutPaymentActionExecutor,
        PaymentActionExecutorInterface $checkoutApiPaymentActionExecutor
    ) {
        $this->hostedCheckoutPaymentActionExecutor = $hostedCheckoutPaymentActionExecutor;
        $this->checkoutApiPaymentActionExecutor = $checkoutApiPaymentActionExecutor;
    }

    /**
     * {@inheritDoc}
     */
    public function create(CyberSourceConfigInterface $config)
    {
        if (CyberSourceSettings::HOSTED_CHECKOUT === $config->getMethod()) {
            return new CyberSourcePaymentMethod($config, $this->hostedCheckoutPaymentActionExecutor);
        }

        if (CyberSourceSettings::CHECKOUT_API === $config->getMethod()) {
            return new CyberSourcePaymentMethod($config, $this->checkoutApiPaymentActionExecutor);
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Unexpected integration method %s. Method should be one of the following: %s.',
                $config->getMethod(),
                implode(', ', CyberSourceSettings::METHODS)
            )
        );
    }
}
