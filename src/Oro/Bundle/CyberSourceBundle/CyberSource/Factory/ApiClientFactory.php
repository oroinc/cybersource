<?php

namespace Oro\Bundle\CyberSourceBundle\CyberSource\Factory;

use CyberSource\ApiClient;
use CyberSource\Authentication\Core\MerchantConfiguration;
use CyberSource\Configuration;

/**
 * Factory to get instance of ApiClient.
 */
class ApiClientFactory implements ApiClientFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(Configuration $config, MerchantConfiguration $merchantConfig): ApiClient
    {
        return new ApiClient($config, $merchantConfig);
    }
}
