<?php

namespace Oro\Bundle\CyberSourceBundle\Method\Config\Provider;

use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;

/**
 * Interface for config provider which allows to get configs based on payment method identifier
 */
interface CyberSourceConfigProviderInterface
{
    /**
     * @return CyberSourceConfigInterface[]
     */
    public function getPaymentConfigs();

    /**
     * @param string $identifier
     *
     * @return CyberSourceConfigInterface|null
     */
    public function getPaymentConfig($identifier);

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function hasPaymentConfig($identifier);
}
