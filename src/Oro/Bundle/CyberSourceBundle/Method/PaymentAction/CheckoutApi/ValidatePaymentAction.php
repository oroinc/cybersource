<?php

namespace Oro\Bundle\CyberSourceBundle\Method\PaymentAction\CheckoutApi;

use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\AbstractPaymentAction;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;

/**
 * It is needed for correct work of js component which requires redirect to order review step instead of ajax reload.
 * Supporting of validate action allows this redirect.
 */
class ValidatePaymentAction extends AbstractPaymentAction
{
    /**
     * {@inheritDoc}
     */
    public function execute(CyberSourceConfigInterface $cyberSourceConfig, PaymentTransaction $paymentTransaction)
    {
        $paymentTransaction
            ->setAmount(0)
            ->setCurrency('')
            ->setAction(PaymentMethodInterface::VALIDATE)
            ->setActive(true)
            ->setSuccessful(true);

        return ['successful' => true];
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return PaymentMethodInterface::VALIDATE;
    }
}
