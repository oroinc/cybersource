<?php

namespace Oro\Bundle\CyberSourceBundle\Method\Factory;

use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;

/**
 * Interface of factories which create payment method instances based on configuration
 */
interface CyberSourcePaymentMethodFactoryInterface
{
    /**
     * @param CyberSourceConfigInterface $config
     *
     * @return PaymentMethodInterface
     */
    public function create(CyberSourceConfigInterface $config);
}
