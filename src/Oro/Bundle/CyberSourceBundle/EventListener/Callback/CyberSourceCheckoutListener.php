<?php

namespace Oro\Bundle\CyberSourceBundle\EventListener\Callback;

use Oro\Bundle\CyberSourceBundle\Method\CyberSourcePaymentMethod;
use Oro\Bundle\PaymentBundle\Event\AbstractCallbackEvent;
use Oro\Bundle\PaymentBundle\Method\Provider\PaymentMethodProviderInterface;
use Oro\Bundle\PaymentBundle\Provider\PaymentResultMessageProviderInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Handles a payment callback event triggered when CyberSource redirects a user after an attempt to make a payment.
 */
class CyberSourceCheckoutListener
{
    use LoggerAwareTrait;

    /** @var Session */
    protected $session;

    /** @var PaymentMethodProviderInterface */
    protected $paymentMethodProvider;

    /** @var PaymentResultMessageProviderInterface */
    protected $messageProvider;

    /**
     * @param Session $session
     * @param PaymentMethodProviderInterface $paymentMethodProvider
     * @param PaymentResultMessageProviderInterface $messageProvider
     */
    public function __construct(
        Session $session,
        PaymentMethodProviderInterface $paymentMethodProvider,
        PaymentResultMessageProviderInterface $messageProvider
    ) {
        $this->session = $session;
        $this->paymentMethodProvider = $paymentMethodProvider;
        $this->messageProvider = $messageProvider;
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

        $eventData = $event->getData();
        if (isset($eventData['message'])) {
            $this->logger->error(
                sprintf(
                    'Error on processing payment transaction "%s": %s',
                    $paymentTransaction->getId(),
                    $eventData['message']
                )
            );
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

            $flashBag = $this->session->getFlashBag();
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
            if ($this->logger) {
                // do not expose sensitive data in context
                $this->logger->error($e->getMessage(), []);
            }
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
}
