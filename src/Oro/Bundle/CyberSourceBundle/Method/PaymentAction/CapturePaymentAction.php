<?php

namespace Oro\Bundle\CyberSourceBundle\Method\PaymentAction;

use CyberSource\Model\Ptsv2paymentsProcessingInformation;
use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Handler\CheckoutApiHandler;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;

/**
 * Processing Capture payment action.
 */
class CapturePaymentAction extends AbstractPaymentAction implements PaymentActionApiHandleInterface
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
                'Capture payment transaction for CyberSource should have source transaction with Capture action'
            );
        }

        $response = $sourcePaymentTransaction->getResponse();
        $transactionId = $response[self::TRANSACTION_ID_KEY] ?? null;

        $options = $this->optionProvider->getCaptureOptions($cyberSourceConfig, $paymentTransaction);
        $requestPaymentMethod = $response['req_payment_method'] ?? '';
        if ($requestPaymentMethod === 'visacheckout') {
            $options['processingInformation'] = new Ptsv2paymentsProcessingInformation([
                'capture' => 'true',
                'paymentSolution' => 'visacheckout',
                'visaCheckoutId' => $transactionId
            ]);
        }

        $result = $this->checkoutApiHandler->handleCapture($cyberSourceConfig, $options, $transactionId);
        $paymentTransaction->setRequest($result->getRequest());

        if ($result->isSuccessfull()) {
            $paymentTransaction
                ->setReference($sourcePaymentTransaction->getReference())
                ->setResponse($result->getResponse())
                ->setActive(false)
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
        return PaymentMethodInterface::CAPTURE;
    }
}
