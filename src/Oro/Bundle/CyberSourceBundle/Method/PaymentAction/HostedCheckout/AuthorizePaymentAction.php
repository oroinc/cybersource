<?php

namespace Oro\Bundle\CyberSourceBundle\Method\PaymentAction\HostedCheckout;

use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\CyberSourceBundle\Method\CyberSourcePaymentMethod;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\AbstractPaymentAction;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;

/**
 * Processing Authorize payment action.
 */
class AuthorizePaymentAction extends AbstractPaymentAction
{
    /**
     * {@inheritDoc}
     */
    public function execute(CyberSourceConfigInterface $cyberSourceConfig, PaymentTransaction $paymentTransaction)
    {
        $response = $paymentTransaction->getResponse();

        // AUTHORIZE transaction holds CyberSourceOrderId in reference property.
        $paymentTransaction->setReference($response[CyberSourcePaymentMethod::PARAM_ORDER_ID]);

        $paymentTransaction->setAction(PaymentMethodInterface::AUTHORIZE);

        $paymentTransaction->setSuccessful(true);

        // Transaction is awaiting for payment capture.
        $paymentTransaction->setActive(true);

        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return PaymentMethodInterface::AUTHORIZE;
    }
}
