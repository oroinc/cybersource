<?php

namespace Oro\Bundle\CyberSourceBundle\Method\Config\Factory;

use Oro\Bundle\CyberSourceBundle\Entity\CyberSourceSettings;
use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;

/**
 * Interface for CyberSource payment method config factory
 */
interface CyberSourceConfigFactoryInterface
{
    /**
     * @param CyberSourceSettings $settings
     *
     * @return CyberSourceConfigInterface
     */
    public function create(CyberSourceSettings $settings);
}
