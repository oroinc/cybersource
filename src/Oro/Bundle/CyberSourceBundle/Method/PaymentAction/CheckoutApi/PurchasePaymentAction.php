<?php

namespace Oro\Bundle\CyberSourceBundle\Method\PaymentAction\CheckoutApi;

use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\AbstractPaymentAction;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Handler\CheckoutApiHandler;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\PaymentActionApiHandleInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;

/**
 * Processing Purchase payment action.
 */
class PurchasePaymentAction extends AbstractPaymentAction implements PaymentActionApiHandleInterface
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
        $options = $this->optionProvider->getPurchaseOptions($cyberSourceConfig, $paymentTransaction);
        if (empty($options)) {
            return [
                'message' => sprintf(
                    'Order id "%s" authorize payment failed: could not get payment token from payment transaction',
                    $paymentTransaction->getEntityIdentifier()
                ),
                'successful' => false,
            ];
        }

        $result = $this->checkoutApiHandler->handlePurchase($cyberSourceConfig, $options);
        $paymentTransaction->setRequest($result->getRequest());
        $paymentTransaction->setResponse($result->getResponse());
        $paymentTransaction->setAction(PaymentMethodInterface::AUTHORIZE);

        if ($result->isSuccessfull()) {
            $paymentTransaction
                ->setReference($this->optionProvider->getClientReferenceCode($paymentTransaction))
                ->setActive(true)
                ->setSuccessful(true);

            return [
                'successful' => true
            ];
        } else {
            if ($cyberSourceConfig->getAuthReversal()) {
                $this->reverseAuthTransaction($cyberSourceConfig, $paymentTransaction);
            }
        }

        return [
            'message' => $result->getErrorMessage(),
            'successful' => false,
        ];
    }

    protected function reverseAuthTransaction(
        CyberSourceConfigInterface $cyberSourceConfig,
        PaymentTransaction $paymentTransaction
    ) {
        $response = $paymentTransaction->getResponse();
        $transactionId = $response[self::TRANSACTION_ID_KEY] ?? null;
        $status = $response['status'] ?? null;

        if (!empty($transactionId)
            && $status === CheckoutApiHandler::PAYMENT_AUTHORIZED_PENDING_REVIEW_RESPONSE_STATUS
        ) {
            $options = $this->optionProvider->getCancelOptions($cyberSourceConfig, $paymentTransaction);
            $this->checkoutApiHandler->handleCancel($cyberSourceConfig, $options, $transactionId);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return PaymentMethodInterface::PURCHASE;
    }
}
