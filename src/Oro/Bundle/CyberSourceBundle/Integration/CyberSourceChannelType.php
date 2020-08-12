<?php

namespace Oro\Bundle\CyberSourceBundle\Integration;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;

/**
 * Integration channel type for CyberSource payment integration
 */
class CyberSourceChannelType implements ChannelInterface, IconAwareIntegrationInterface
{
    const TYPE = 'oro_cybersource';

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'oro.cybersource.channel_type.label';
    }

    /**
     * {@inheritDoc}
     */
    public function getIcon()
    {
        return 'bundles/orocybersource/img/cybersource-logo.png';
    }
}
