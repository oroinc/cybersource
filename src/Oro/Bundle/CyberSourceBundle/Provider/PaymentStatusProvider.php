<?php

namespace Oro\Bundle\CyberSourceBundle\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\CyberSourceBundle\Method\CyberSourcePaymentMethod;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Provider\PaymentStatusProvider as BaseProvider;

/**
 * Provider for CyberSource canceled payment status
 */
class PaymentStatusProvider extends BaseProvider
{
    const CANCELED = 'cybersource_canceled';

    /**
     * {@inheritdoc}
     */
    public function getPaymentStatus($entity)
    {
        $paymentTransactions = new ArrayCollection($this->paymentTransactionProvider->getPaymentTransactions($entity));

        if ($this->hasCanceledTransactions($paymentTransactions)) {
            return self::CANCELED;
        }

        return parent::getPaymentStatus($entity);
    }

    /**
     * @param ArrayCollection $paymentTransactions
     *
     * @return bool
     */
    protected function hasCanceledTransactions(ArrayCollection $paymentTransactions)
    {
        return false === $paymentTransactions
            ->filter(
                function (PaymentTransaction $paymentTransaction) {
                    if ($paymentTransaction->isClone()) {
                        return false;
                    }

                    return $paymentTransaction->isSuccessful()
                    && $paymentTransaction->getAction() === CyberSourcePaymentMethod::CANCEL;
                }
            )
            ->isEmpty();
    }
}
