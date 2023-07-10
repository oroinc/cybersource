<?php

namespace Oro\Bundle\CyberSourceBundle\EventListener\Callback;

use Oro\Bundle\CyberSourceBundle\Method\Config\Provider\CyberSourceConfigProvider;
use Oro\Bundle\CyberSourceBundle\Method\CyberSourcePaymentMethod;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Event\AbstractCallbackEvent;
use Oro\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;
use Oro\Bundle\PaymentBundle\Provider\PaymentResultMessageProviderInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Handles a payment callback event triggered when CyberSource redirects a user after an attempt to make a payment.
 */
class CyberSourceCheckoutListener
{
    use LoggerAwareTrait;

    /**
     * @param PaymentMethodProviderInterface $paymentMethodProvider
     * @param PaymentResultMessageProviderInterface $messageProvider
     * @param RequestStack $requestStack
     */
    public function __construct(
        protected PaymentMethodProviderInterface $paymentMethodProvider,
        protected PaymentResultMessageProviderInterface $messageProvider,
        protected CyberSourceConfigProvider $configProvider,
        protected RequestStack $requestStack
    ) {
    }

    /**
     * @param AbstractCallbackEvent $event
     */
    public function onError(AbstractCallbackEvent $event)
    {
        $paymentTransaction = $event->getPaymentTransaction();

        if (!$this->isValidPaymentTransaction($paymentTransaction)) {
            return;
        }

        if (!$paymentTransaction->isSuccessful()) {
            $error = $this->getResponseError($paymentTransaction);
            if ($error) {
                if ($this->isDisplayErrorsEnabled($paymentTransaction)) {
                    $flashBag = $this->requestStack->getSession()->getFlashBag();
                    $flashBag->add('error', $error);
                }

                $this->logger->error(
                    sprintf(
                        'Error on processing payment transaction "%s": %s',
                        $paymentTransaction->getId(),
                        $error
                    )
                );
            }
        }

        $transactionOptions = $paymentTransaction->getTransactionOptions();
        $event->setResponse(new RedirectResponse($transactionOptions['failureUrl']));
    }

    /**
     * @param AbstractCallbackEvent $event
     */
    public function onReturn(AbstractCallbackEvent $event)
    {
        $paymentTransaction = $event->getPaymentTransaction();

        if (!$this->isValidPaymentTransaction($paymentTransaction)) {
            return;
        }

        $eventData = $event->getData();

        if ($eventData['decision'] === 'DECLINE' || $eventData['decision'] === 'ERROR') {
            $transactionOptions = $paymentTransaction->getTransactionOptions();
            $event->setResponse(new RedirectResponse($transactionOptions['failureUrl']));

            $invalid_fields = isset($eventData['invalid_fields']) ? ': ' . $eventData['invalid_fields'] : '';
            $this->logger->error(
                sprintf(
                    'Error on processing payment transaction "%s". %s%s',
                    $paymentTransaction->getId(),
                    $eventData['message'],
                    $invalid_fields
                )
            );

            $flashBag = $this->requestStack->getSession()->getFlashBag();
            if (!$flashBag->has('error')) {
                $flashBag->add('error', $this->messageProvider->getErrorMessage($paymentTransaction));
            }

            return;
        }

        $responseDataFilledWithEventData = array_replace($paymentTransaction->getResponse(), $eventData);
        $paymentTransaction->setResponse($responseDataFilledWithEventData);

        try {
            $paymentMethod = $this->paymentMethodProvider->getPaymentMethod($paymentTransaction->getPaymentMethod());
            $paymentMethod->execute(CyberSourcePaymentMethod::AUTHORIZE, $paymentTransaction);

            $event->markSuccessful();
        } catch (\InvalidArgumentException $e) {
            $this->logger->error($e->getMessage()); // do not expose sensitive data in context
        }
    }

    /**
     * @param $paymentTransaction
     *
     * @return bool
     */
    protected function isValidPaymentTransaction($paymentTransaction)
    {
        if (!$paymentTransaction) {
            return false;
        }

        $paymentMethodId = $paymentTransaction->getPaymentMethod();

        return $this->paymentMethodProvider->hasPaymentMethod($paymentMethodId);
    }

    /**
     * @param PaymentTransaction $paymentTransaction
     *
     * @return null|string
     */
    protected function getResponseError(PaymentTransaction $paymentTransaction)
    {
        $error = null;
        $response = $paymentTransaction->getResponse();
        if (isset($response['errorInformation'], $response['errorInformation']['message'])) {
            $error = $response['errorInformation']['message'];
        }

        return $error;
    }

    /**
     * @param PaymentTransaction $paymentTransaction
     * @return bool
     */
    protected function isDisplayErrorsEnabled(PaymentTransaction $paymentTransaction): bool
    {
        $paymentConfiguration = $this->configProvider->getPaymentConfig($paymentTransaction->getPaymentMethod());
        if ($paymentConfiguration) {
            return $paymentConfiguration->getDisplayErrors();
        }

        return false;
    }
}
