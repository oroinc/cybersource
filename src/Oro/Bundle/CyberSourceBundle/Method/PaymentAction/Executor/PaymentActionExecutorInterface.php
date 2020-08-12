<?php

namespace Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Executor;

use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\PaymentActionInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

/**
 * Interface for creating payment action executors
 */
interface PaymentActionExecutorInterface
{
    /**
     * @param PaymentActionInterface $paymentAction
     *
     * @return PaymentActionExecutorInterface
     */
    public function addPaymentAction(PaymentActionInterface $paymentAction);

    /**
     * @param string                $action
     * @param CyberSourceConfigInterface $cyberSourceConfig
     * @param PaymentTransaction    $paymentTransaction
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function execute(
        $action,
        CyberSourceConfigInterface
        $cyberSourceConfig,
        PaymentTransaction $paymentTransaction
    );

    /**
     * @param string $name
     *
     * @return bool
     */
    public function supports($name);
}
