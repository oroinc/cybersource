<?php

namespace Oro\Bundle\CyberSourceBundle\Tests\Behat\Mock\CyberSource\Factory;

use CyberSource\ApiClient;
use CyberSource\Authentication\Core\MerchantConfiguration;
use CyberSource\Configuration;
use Oro\Bundle\CyberSourceBundle\CyberSource\Factory\ApiClientFactoryInterface;
use Oro\Bundle\CyberSourceBundle\Tests\Behat\Mock\CyberSource\ApiClientMock;

class ApiClientFactoryMock implements ApiClientFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(Configuration $config, MerchantConfiguration $merchantConfig): ApiClient
    {
        return new ApiClientMock($config, $merchantConfig);
    }
}
