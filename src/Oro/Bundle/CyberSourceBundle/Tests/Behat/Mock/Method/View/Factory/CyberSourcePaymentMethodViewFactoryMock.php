<?php

namespace Oro\Bundle\CyberSourceBundle\Tests\Behat\Mock\Method\View\Factory;

use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Handler\CheckoutApiHandler;
use Oro\Bundle\CyberSourceBundle\Method\View\CyberSourceCheckoutApiPaymentMethodView;
use Oro\Bundle\CyberSourceBundle\Method\View\Factory\CyberSourcePaymentMethodViewFactory;
use Oro\Bundle\CyberSourceBundle\Tests\Behat\Mock\Method\View\CyberSourceCheckoutApiPaymentMethodViewMock;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Replaces view CyberSourceCheckoutApiPaymentMethodView with view adjusted for Behat tests.
 */
class CyberSourcePaymentMethodViewFactoryMock extends CyberSourcePaymentMethodViewFactory
{
    /**
     * {@inheritDoc}
     */
    public function create(
        CyberSourceConfigInterface $config,
        FormFactoryInterface $formFactory,
        CheckoutApiHandler $checkoutApiHandler
    ) {
        $view = parent::create($config, $formFactory, $checkoutApiHandler);

        if ($view instanceof CyberSourceCheckoutApiPaymentMethodView) {
            return new CyberSourceCheckoutApiPaymentMethodViewMock(
                $config,
                $formFactory,
                $checkoutApiHandler
            );
        }

        return $view;
    }
}
