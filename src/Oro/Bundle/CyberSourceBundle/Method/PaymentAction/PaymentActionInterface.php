<?php

namespace Oro\Bundle\CyberSourceBundle\Method\PaymentAction;

use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Provider\PaymentActionOptionProviderInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

/**
 * Interface for creating payment actions.
 */
interface PaymentActionInterface
{
    /**
     * Get payment action name.
     *
     * @return string
     */
    public function getName();

    /**
     * @param CyberSourceConfigInterface $cyberSourceConfig
     * @param PaymentTransaction $paymentTransaction
     *
     * @return array
     */
    public function execute(CyberSourceConfigInterface $cyberSourceConfig, PaymentTransaction $paymentTransaction);

    /**
     * @param PaymentActionOptionProviderInterface $optionProvider
     */
    public function setOptionProvider(PaymentActionOptionProviderInterface $optionProvider);
}
