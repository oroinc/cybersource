<?php

namespace Oro\Bundle\CyberSourceBundle\Method\Config\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CyberSourceBundle\Entity\CyberSourceSettings;
use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\CyberSourceBundle\Method\Config\Factory\CyberSourceConfigFactoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Allows to get configs of CyberSource payment method
 */
class CyberSourceConfigProvider implements CyberSourceConfigProviderInterface
{
    /** @var ManagerRegistry */
    protected $doctrine;

    /** @var CyberSourceConfigFactoryInterface */
    protected $configFactory;

    /** @var CyberSourceConfigInterface[] */
    protected $configs;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param ManagerRegistry                  $doctrine
     * @param LoggerInterface                  $logger
     * @param CyberSourceConfigFactoryInterface $configFactory
     */
    public function __construct(
        ManagerRegistry $doctrine,
        LoggerInterface $logger,
        CyberSourceConfigFactoryInterface $configFactory
    ) {
        $this->doctrine = $doctrine;
        $this->logger = $logger;
        $this->configFactory = $configFactory;
    }

    /**
     * @return array|CyberSourceConfigInterface[]
     */
    public function getPaymentConfigs()
    {
        $settings = $this->getEnabledIntegrationSettings();

        $configs = [];
        foreach ($settings as $cyberSourceSettings) {
            $config = $this->configFactory->create($cyberSourceSettings);
            $configs[$config->getPaymentMethodIdentifier()] = $config;
        }

        return $configs;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentConfig($identifier)
    {
        $paymentConfigs = $this->getPaymentConfigs();

        if ([] === $paymentConfigs || false === array_key_exists($identifier, $paymentConfigs)) {
            return null;
        }

        return $paymentConfigs[$identifier];
    }

    /**
     * @return array
     */
    public function getEnabledIntegrationSettings()
    {
        try {
            return $this->doctrine->getManagerForClass(CyberSourceSettings::class)
                ->getRepository(CyberSourceSettings::class)
                ->findEnabledSettings();
        } catch (\UnexpectedValueException $e) {
            $this->logger->error($e->getMessage());
        }
        return [];
    }

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function hasPaymentConfig($identifier)
    {
        return null !== $this->getPaymentConfig($identifier);
    }
}
