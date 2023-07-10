<?php

namespace Oro\Bundle\CyberSourceBundle\Method\View\Factory;

use Oro\Bundle\CyberSourceBundle\Entity\CyberSourceSettings;
use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Handler\CheckoutApiHandler;
use Oro\Bundle\CyberSourceBundle\Method\View\CyberSourceCheckoutApiPaymentMethodView;
use Oro\Bundle\CyberSourceBundle\Method\View\CyberSourceHostedCheckoutPaymentMethodView;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Factory for creating views of CyberSource payment method
 */
class CyberSourcePaymentMethodViewFactory implements CyberSourcePaymentMethodViewFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(
        CyberSourceConfigInterface $config,
        FormFactoryInterface $formFactory,
        CheckoutApiHandler $checkoutApiHandler
    ) {
        if (CyberSourceSettings::HOSTED_CHECKOUT === $config->getMethod()) {
            return new CyberSourceHostedCheckoutPaymentMethodView($config);
        }

        if (CyberSourceSettings::CHECKOUT_API === $config->getMethod()) {
            return new CyberSourceCheckoutApiPaymentMethodView(
                $config,
                $formFactory,
                $checkoutApiHandler
            );
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
