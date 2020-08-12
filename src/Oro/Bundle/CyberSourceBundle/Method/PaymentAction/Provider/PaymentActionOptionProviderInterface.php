<?php

namespace Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Provider;

use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

/**
 * Represents requirements for realization, that is able to
 * find/prepare array of options for passing them to CyberSource
 * during sending requests to API
 */
interface PaymentActionOptionProviderInterface
{
    /**
     * @param CyberSourceConfigInterface $config
     * @param PaymentTransaction $transaction
     *
     * @return array
     */
    public function getPurchaseOptions(CyberSourceConfigInterface $config, PaymentTransaction $transaction);

    /**
     * @param CyberSourceConfigInterface $config
     * @param PaymentTransaction $transaction
     *
     * @return array
     */
    public function getAuthorizeOptions(CyberSourceConfigInterface $config, PaymentTransaction $transaction);

    /**
     * @param CyberSourceConfigInterface $config
     * @param PaymentTransaction $transaction
     *
     * @return array
     */
    public function getCaptureOptions(CyberSourceConfigInterface $config, PaymentTransaction $transaction);

    /**
     * @param CyberSourceConfigInterface $config
     * @param PaymentTransaction $transaction
     *
     * @return array
     */
    public function getCancelOptions(CyberSourceConfigInterface $config, PaymentTransaction $transaction);

    /**
     * @param CyberSourceConfigInterface $config
     * @param PaymentTransaction $transaction
     *
     * @return array
     */
    public function getValidateOptions(CyberSourceConfigInterface $config, PaymentTransaction $transaction);
}
