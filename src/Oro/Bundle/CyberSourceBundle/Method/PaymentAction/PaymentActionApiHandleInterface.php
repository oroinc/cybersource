<?php

namespace Oro\Bundle\CyberSourceBundle\Method\PaymentAction;

use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Handler\CheckoutApiHandler;

/**
 * Interface for creating payment actions supported API handling.
 */
interface PaymentActionApiHandleInterface
{
    /**
     * @param CheckoutApiHandler $checkoutApiHandler
     */
    public function setCheckoutApiHandler(CheckoutApiHandler $checkoutApiHandler);
}
