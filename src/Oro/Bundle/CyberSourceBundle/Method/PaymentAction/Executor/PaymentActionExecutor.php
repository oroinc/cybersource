<?php

namespace Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Executor;

use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Handler\CheckoutApiHandler;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\PaymentActionApiHandleInterface;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\PaymentActionInterface;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Provider\PaymentActionOptionProviderInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

/**
 * Class for processing payment actions
 */
class PaymentActionExecutor implements PaymentActionExecutorInterface
{
    /** @var PaymentActionInterface[] */
    protected $actions = [];

    /** @var PaymentActionOptionProviderInterface */
    protected $optionProvider;

    /** @var CheckoutApiHandler */
    protected $checkoutApiHandler;

    /**
     * {@inheritDoc}
     */
    public function addPaymentAction(PaymentActionInterface $paymentAction)
    {
        $this->actions[$paymentAction->getName()] = $paymentAction;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(
        $action,
        CyberSourceConfigInterface $cyberSourceConfig,
        PaymentTransaction $paymentTransaction
    ) {
        $paymentAction = $this->getPaymentAction($action);

        if ($this->optionProvider) {
            $paymentAction->setOptionProvider($this->optionProvider);
        }

        if ($this->checkoutApiHandler && $paymentAction instanceof PaymentActionApiHandleInterface) {
            $paymentAction->setCheckoutApiHandler($this->checkoutApiHandler);
        }

        return $paymentAction->execute($cyberSourceConfig, $paymentTransaction);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($name)
    {
        return array_key_exists($name, $this->actions);
    }

    /**
     * @param string $name
     *
     * @return PaymentActionInterface
     *
     * @throws \InvalidArgumentException
     */
    protected function getPaymentAction($name)
    {
        if ($this->supports($name)) {
            return $this->actions[$name];
        }

        throw new \InvalidArgumentException(
            sprintf('Payment action with name "%s" is not supported', $name)
        );
    }

    /**
     * @param PaymentActionOptionProviderInterface $optionProvider
     */
    public function setOptionProvider(PaymentActionOptionProviderInterface $optionProvider)
    {
        $this->optionProvider = $optionProvider;
    }

    /**
     * @param CheckoutApiHandler $checkoutApiHandler
     */
    public function setCheckoutApiHandler(CheckoutApiHandler $checkoutApiHandler)
    {
        $this->checkoutApiHandler = $checkoutApiHandler;
    }
}
