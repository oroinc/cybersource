<?php

namespace Oro\Bundle\CyberSourceBundle\CyberSource\Factory;

use CyberSource\ApiClient;
use CyberSource\Authentication\Core\MerchantConfiguration;
use CyberSource\Configuration;

/**
 * Interface for factory that creates instance of ApiClient.
 */
interface ApiClientFactoryInterface
{
    /**
     * @param Configuration         $config
     * @param MerchantConfiguration $merchantConfig
     *
     * @return ApiClient
     */
    public function create(Configuration $config, MerchantConfiguration $merchantConfig): ApiClient;
}
