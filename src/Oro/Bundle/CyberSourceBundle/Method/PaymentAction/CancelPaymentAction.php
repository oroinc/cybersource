<?php

namespace Oro\Bundle\CyberSourceBundle\Method\PaymentAction;

use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Handler\CheckoutApiHandler;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;

/**
 * Processing Cancel payment action.
 */
class CancelPaymentAction extends AbstractPaymentAction implements PaymentActionApiHandleInterface
{
    /** @var CheckoutApiHandler */
    protected $checkoutApiHandler;

    /**
     * @inheritDoc
     */
    public function setCheckoutApiHandler(CheckoutApiHandler $checkoutApiHandler)
    {
        $this->checkoutApiHandler = $checkoutApiHandler;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(CyberSourceConfigInterface $cyberSourceConfig, PaymentTransaction $paymentTransaction)
    {
        $sourcePaymentTransaction = $paymentTransaction->getSourcePaymentTransaction();

        if ($sourcePaymentTransaction === null) {
            throw new \LogicException(
                'Cancel payment transaction for CyberSource should have source transaction'
            );
        }

        $response = $sourcePaymentTransaction->getResponse();
        $transactionId = $response[self::TRANSACTION_ID_KEY] ?? null;
        $options = $this->optionProvider->getCancelOptions($cyberSourceConfig, $paymentTransaction);
        $result = $this->checkoutApiHandler->handleCancel($cyberSourceConfig, $options, $transactionId);
        $paymentTransaction->setRequest($result->getRequest());

        if ($result->isSuccessfull()) {
            $paymentTransaction
                ->setReference($sourcePaymentTransaction->getReference())
                ->setActive(false)
                ->setResponse($result->getResponse())
                ->setSuccessful(true);

            return [];
        }

        return [
            'message' => $result->getErrorMessage(),
            'successful' => false
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return PaymentMethodInterface::CANCEL;
    }
}
