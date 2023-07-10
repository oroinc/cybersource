<?php

namespace Oro\Bundle\CyberSourceBundle\Method\PaymentAction;

use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Provider\PaymentActionOptionProviderInterface;

/**
 * Contains default implementation of payment actions.
 */
abstract class AbstractPaymentAction implements PaymentActionInterface
{
    const TRANSACTION_ID_KEY = 'transaction_id';

    /** @var PaymentActionOptionProviderInterface */
    protected $optionProvider;

    /**
     * @inheritDoc
     */
    public function setOptionProvider(PaymentActionOptionProviderInterface $optionProvider)
    {
        $this->optionProvider = $optionProvider;
    }
}
