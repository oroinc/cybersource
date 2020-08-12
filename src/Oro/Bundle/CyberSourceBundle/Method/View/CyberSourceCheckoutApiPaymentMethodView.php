<?php

namespace Oro\Bundle\CyberSourceBundle\Method\View;

use Oro\Bundle\CyberSourceBundle\Form\Type\CreditCardType;
use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Handler\CheckoutApiHandler;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PaymentBundle\Method\View\PaymentMethodViewInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * View for CyberSource Checkout API payment method
 */
class CyberSourceCheckoutApiPaymentMethodView implements PaymentMethodViewInterface
{
    /** @var CyberSourceConfigInterface */
    protected $config;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var CheckoutApiHandler */
    protected $checkoutApiHandler;

    /**
     * @param CyberSourceConfigInterface $config
     * @param FormFactoryInterface $formFactory
     * @param CheckoutApiHandler $checkoutApiHandler
     */
    public function __construct(
        CyberSourceConfigInterface $config,
        FormFactoryInterface $formFactory,
        CheckoutApiHandler $checkoutApiHandler
    ) {
        $this->config = $config;
        $this->formFactory = $formFactory;
        $this->checkoutApiHandler = $checkoutApiHandler;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions(PaymentContextInterface $context)
    {
        $form = $this->formFactory->create(CreditCardType::class);

        $result = $this->checkoutApiHandler->handleGeneratePublicKey($this->config);
        if (!$result->isSuccessfull() || !isset($result->getResponse()['keyId'])) {
            return ['error' => true];
        }

        $paymentMethodId = $this->config->getPaymentMethodIdentifier();
        return [
            'formView'                   => $form->createView(),
            'creditCardComponentOptions' => [
                'captureContext' => $result->getResponse()['keyId'],
                'dynamicSelectors' => [
                    'securityCode' => sprintf('securityCode-container-%s', $paymentMethodId),
                    'number' => sprintf('number-container-%s', $paymentMethodId),
                ]
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getBlock()
    {
        return '_payment_methods_oro_cybersource_checkout_api_widget';
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
