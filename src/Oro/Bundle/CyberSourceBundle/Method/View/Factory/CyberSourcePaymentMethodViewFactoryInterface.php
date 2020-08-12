<?php

namespace Oro\Bundle\CyberSourceBundle\Method\View\Factory;

use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Handler\CheckoutApiHandler;
use Oro\Bundle\PaymentBundle\Method\View\PaymentMethodViewInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Interface for creating views of CyberSource payment method
 */
interface CyberSourcePaymentMethodViewFactoryInterface
{
    /**
     * @param CyberSourceConfigInterface $config
     * @param FormFactoryInterface $formFactory
     * @param CheckoutApiHandler $checkoutApiHandler
     *
     * @return PaymentMethodViewInterface
     */
    public function create(
        CyberSourceConfigInterface $config,
        FormFactoryInterface $formFactory,
        CheckoutApiHandler $checkoutApiHandler
    );
}
