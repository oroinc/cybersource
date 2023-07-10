<?php

namespace Oro\Bundle\CyberSourceBundle\Method\PaymentAction\HostedCheckout;

use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\AbstractPaymentAction;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;

/**
 * Processing Purchase payment action.
 */
class PurchasePaymentAction extends AbstractPaymentAction
{
    const BASE_URL_PROD = 'https://secureacceptance.cybersource.com/';
    const BASE_URL_TEST = 'https://testsecureacceptance.cybersource.com/';

    /**
     * {@inheritDoc}
     */
    public function execute(CyberSourceConfigInterface $cyberSourceConfig, PaymentTransaction $paymentTransaction)
    {
        $options = $this->optionProvider->getPurchaseOptions($cyberSourceConfig, $paymentTransaction);

        $requestData = [
            'cyberSourceFormAction' => $this->getCyberSourcePayUrl($cyberSourceConfig->isTestMode()),
            'cyberSourceFormData' => $options,
        ];

        $paymentTransaction->setRequest($requestData);
        $paymentTransaction->setSuccessful(false);
        $paymentTransaction->setActive(true);

        return $requestData;
    }

    /**
     * @param $isTestMode
     *
     * @return string
     */
    protected function getCyberSourcePayUrl($isTestMode)
    {
        $url = self::BASE_URL_PROD;
        if ($isTestMode) {
            $url = self::BASE_URL_TEST;
        }

        return $url . 'pay';
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return PaymentMethodInterface::PURCHASE;
    }
}
