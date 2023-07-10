<?php

namespace Oro\Bundle\CyberSourceBundle\Method\Config\Factory;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\CyberSourceBundle\Entity\CyberSourceSettings;
use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfig;
use Oro\Bundle\IntegrationBundle\Generator\IntegrationIdentifierGeneratorInterface;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;
use Psr\Log\LoggerInterface;

/**
 * Creates instances of configurations for CyberSource payment method
 */
class CyberSourceConfigFactory implements CyberSourceConfigFactoryInterface
{
    /** @var LocalizationHelper */
    protected $localizationHelper;

    /** @var IntegrationIdentifierGeneratorInterface */
    protected $identifierGenerator;

    /** @var SymmetricCrypterInterface */
    protected $crypter;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param LocalizationHelper $localizationHelper
     * @param IntegrationIdentifierGeneratorInterface $identifierGenerator
     * @param SymmetricCrypterInterface $crypter
     * @param LoggerInterface $logger
     */
    public function __construct(
        LocalizationHelper $localizationHelper,
        IntegrationIdentifierGeneratorInterface $identifierGenerator,
        SymmetricCrypterInterface $crypter,
        LoggerInterface $logger
    ) {
        $this->localizationHelper = $localizationHelper;
        $this->identifierGenerator = $identifierGenerator;
        $this->crypter = $crypter;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function create(CyberSourceSettings $settings)
    {
        $params = [];
        $channel = $settings->getChannel();

        $params[CyberSourceConfig::FIELD_PAYMENT_METHOD_IDENTIFIER] =
            $this->identifierGenerator->generateIdentifier($channel);

        $params[CyberSourceConfig::FIELD_ADMIN_LABEL] = $channel->getName();
        $params[CyberSourceConfig::FIELD_LABEL] = $this->getLocalizedValue($settings->getLabels());
        $params[CyberSourceConfig::FIELD_SHORT_LABEL] = $this->getLocalizedValue($settings->getShortLabels());

        $params[CyberSourceConfig::MERCHANT_ID_KEY] = $settings->getCbsMerchantId();
        $params[CyberSourceConfig::MERCHANT_DESCRIPTOR_KEY] = $settings->getCbsMerchantDescriptor();
        $params[CyberSourceConfig::PROFILE_ID_KEY] = $settings->getCbsProfileId();
        $params[CyberSourceConfig::ACCESS_KEY] = $this->decryptData($settings->getCbsAccessKey());
        $params[CyberSourceConfig::API_KEY] = $this->decryptData($settings->getCbsApiKey());
        $params[CyberSourceConfig::API_SECRET_KEY] = $this->decryptData($settings->getCbsApiSecretKey());
        $params[CyberSourceConfig::SECRET_KEY] = $this->decryptData($settings->getCbsSecretKey());
        $params[CyberSourceConfig::TEST_MODE_KEY] = $settings->getCbsTestMode();
        $params[CyberSourceConfig::METHOD_KEY] = $settings->getCbsMethod();
        $params[CyberSourceConfig::IGNORE_AVS_KEY] = $settings->getCbsIgnoreAvs();
        $params[CyberSourceConfig::IGNORE_CVN_KEY] = $settings->getCbsIgnoreCvn();
        $params[CyberSourceConfig::AUTH_REVERSAL_KEY] = $settings->getCbsAuthReversal();
        $params[CyberSourceConfig::DISPLAY_ERRORS_KEY] = $settings->getCbsDisplayErrors();

        return new CyberSourceConfig($params);
    }

    /**
     * @param Collection $values
     *
     * @return string
     */
    protected function getLocalizedValue(Collection $values)
    {
        return (string)$this->localizationHelper->getLocalizedValue($values);
    }

    /**
     * @param string $data
     *
     * @return string
     */
    protected function decryptData($data)
    {
        try {
            return $this->crypter->decryptData($data);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            // Decryption failure, might be caused by invalid/malformed/not encrypted data.
            return '';
        }
    }
}
