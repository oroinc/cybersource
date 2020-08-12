<?php

namespace Oro\Bundle\CyberSourceBundle\Method;

use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Executor\PaymentActionExecutorInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;

/**
 * Payment method class that describes main business logic of CyberSource payment method
 */
class CyberSourcePaymentMethod implements PaymentMethodInterface
{
    const PARAM_ORDER_ID = 'req_reference_number';

    /** @var CyberSourceConfigInterface */
    protected $config;

    /** @var PaymentActionExecutorInterface */
    protected $paymentActionExecutor;

    /**
     * @param CyberSourceConfigInterface $config
     * @param PaymentActionExecutorInterface $paymentActionExecutor
     */
    public function __construct(
        CyberSourceConfigInterface $config,
        PaymentActionExecutorInterface $paymentActionExecutor
    ) {
        $this->config = $config;
        $this->paymentActionExecutor = $paymentActionExecutor;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($action, PaymentTransaction $paymentTransaction)
    {
        return $this->paymentActionExecutor->execute($action, $this->config, $paymentTransaction);
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifier()
    {
        return $this->config->getPaymentMethodIdentifier();
    }

    /**
     * {@inheritDoc}
     */
    public function isApplicable(PaymentContextInterface $context)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($actionName)
    {
        return $this->paymentActionExecutor->supports($actionName);
    }
}
