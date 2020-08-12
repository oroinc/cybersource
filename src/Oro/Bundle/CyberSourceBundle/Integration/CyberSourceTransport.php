<?php

namespace Oro\Bundle\CyberSourceBundle\Integration;

use Oro\Bundle\CyberSourceBundle\Entity\CyberSourceSettings;
use Oro\Bundle\CyberSourceBundle\Form\Type\CyberSourceSettingsType;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Transport for CyberSource payment integration
 */
class CyberSourceTransport implements TransportInterface
{
    /** @var ParameterBag */
    protected $settings;

    /**
     * @param Transport $transportEntity
     */
    public function init(Transport $transportEntity)
    {
        $this->settings = $transportEntity->getSettingsBag();
    }

    /**
     * {@inheritDoc}
     */
    public function getSettingsFormType()
    {
        return CyberSourceSettingsType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getSettingsEntityFQCN()
    {
        return CyberSourceSettings::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'oro.cybersource.settings.label';
    }
}
