<?php

namespace Oro\Bundle\CyberSourceBundle\Tests\Behat\Mock\Method\View;

use Oro\Bundle\CyberSourceBundle\Method\View\CyberSourceCheckoutApiPaymentMethodView;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;

/**
 * Replaces flexJsUrl with stub.
 */
class CyberSourceCheckoutApiPaymentMethodViewMock extends CyberSourceCheckoutApiPaymentMethodView
{
    /**
     * {@inheritDoc}
     */
    public function getOptions(PaymentContextInterface $context)
    {
        $viewData = parent::getOptions($context);

        if (isset($viewData['creditCardComponentOptions'])) {
            $viewData['creditCardComponentOptions']['flexJsUrl'] = 'orocybersource/js/stubs/FlexStub';
        }

        return $viewData;
    }
}
