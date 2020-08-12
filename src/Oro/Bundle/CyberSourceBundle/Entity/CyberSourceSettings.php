<?php

namespace Oro\Bundle\CyberSourceBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Entity with settings for CyberSource integration
 *
 * @ORM\Entity(
 *     repositoryClass="Oro\Bundle\CyberSourceBundle\Entity\Repository\CyberSourceSettingsRepository"
 * )
 */
class CyberSourceSettings extends Transport
{
    /**
     * General keys.
     */
    const LABELS_KEY = 'labels';
    const SHORT_LABELS_KEY = 'short_labels';

    /**
     * CyberSource specific keys.
     */
    const MERCHANT_ID_KEY = 'merchant_id';
    const MERCHANT_DESCRIPTOR_KEY = 'merchant_descriptor';
    const PROFILE_ID_KEY = 'profile_id';
    const ACCESS_KEY = 'access_key';
    const API_KEY = 'api_key';
    const API_SECRET_KEY = 'api_secret_key';
    const SECRET_KEY = 'secret_key';
    const TEST_MODE_KEY = 'test_mode';
    const METHOD = 'method';

    /**
     * CyberSource integration methods.
     */
    const HOSTED_CHECKOUT = 'hosted_checkout';
    const CHECKOUT_API = 'checkout_api';
    const METHODS = [
        self::HOSTED_CHECKOUT,
        self::CHECKOUT_API
    ];

    /**
     * @var Collection|LocalizedFallbackValue[]
     *
     * @ORM\ManyToMany(
     *      targetEntity="Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue",
     *      cascade={"ALL"},
     *      orphanRemoval=true
     * )
     * @ORM\JoinTable(
     *      name="oro_cbs_trans_label",
     *      joinColumns={
     *          @ORM\JoinColumn(name="transport_id", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="localized_value_id", referencedColumnName="id", onDelete="CASCADE", unique=true)
     *      }
     * )
     */
    private $labels;

    /**
     * @var Collection|LocalizedFallbackValue[]
     *
     * @ORM\ManyToMany(
     *      targetEntity="Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue",
     *      cascade={"ALL"},
     *      orphanRemoval=true
     * )
     * @ORM\JoinTable(
     *      name="oro_cbs_short_label",
     *      joinColumns={
     *          @ORM\JoinColumn(name="transport_id", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="localized_value_id", referencedColumnName="id", onDelete="CASCADE", unique=true)
     *      }
     * )
     */
    private $shortLabels;

    /** @var ParameterBag */
    private $settings;

    /**
     * @var bool
     *
     * @ORM\Column(name="cbs_test_mode", type="boolean", options={"default"=false})
     */
    private $cbsTestMode = false;

    /**
     * @var string
     *
     * @ORM\Column(name="cbs_merchant_id", type="string", length=255)
     */
    private $cbsMerchantId;

    /**
     * @var string
     *
     * @ORM\Column(name="cbs_merchant_descriptor", type="string", length=255)
     */
    private $cbsMerchantDescriptor;

    /**
     * @var string
     *
     * @ORM\Column(name="cbs_profile_id", type="string", length=255)
     */
    private $cbsProfileId;

    /**
     * @var string
     *
     * @ORM\Column(name="cbs_access_key", type="string", length=255)
     */
    private $cbsAccessKey;

    /**
     * @var string
     *
     * @ORM\Column(name="cbs_api_key", type="string", length=255)
     */
    private $cbsApiKey;

    /**
     * @var string
     *
     * @ORM\Column(name="cbs_api_secret_key", type="string", length=255)
     */
    private $cbsApiSecretKey;

    /**
     * @var string
     *
     * @ORM\Column(name="cbs_secret_key", type="text")
     */
    private $cbsSecretKey;

    /**
     * @var string
     *
     * @ORM\Column(name="cbs_method", type="string", length=255)
     */
    private $cbsMethod;

    public function __construct()
    {
        $this->labels = new ArrayCollection();
        $this->shortLabels = new ArrayCollection();
    }

    /**
     * @return Collection|LocalizedFallbackValue[]
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @param LocalizedFallbackValue $label
     *
     * @return CyberSourceSettings
     */
    public function addLabel(LocalizedFallbackValue $label)
    {
        if (!$this->labels->contains($label)) {
            $this->labels->add($label);
        }

        return $this;
    }

    /**
     * @param LocalizedFallbackValue $label
     *
     * @return CyberSourceSettings
     */
    public function removeLabel(LocalizedFallbackValue $label)
    {
        if ($this->labels->contains($label)) {
            $this->labels->removeElement($label);
        }

        return $this;
    }

    /**
     * @return Collection|LocalizedFallbackValue[]
     */
    public function getShortLabels()
    {
        return $this->shortLabels;
    }

    /**
     * @param LocalizedFallbackValue $label
     *
     * @return CyberSourceSettings
     */
    public function addShortLabel(LocalizedFallbackValue $label)
    {
        if (!$this->shortLabels->contains($label)) {
            $this->shortLabels->add($label);
        }

        return $this;
    }

    /**
     * @param LocalizedFallbackValue $label
     *
     * @return CyberSourceSettings
     */
    public function removeShortLabel(LocalizedFallbackValue $label)
    {
        if ($this->shortLabels->contains($label)) {
            $this->shortLabels->removeElement($label);
        }

        return $this;
    }

    /**
     * @return ParameterBag
     */
    public function getSettingsBag()
    {
        if (null === $this->settings) {
            $this->settings = new ParameterBag(
                [
                    self::LABELS_KEY => $this->getLabels(),
                    self::SHORT_LABELS_KEY => $this->getShortLabels(),
                    self::MERCHANT_ID_KEY => $this->getCbsMerchantId(),
                    self::MERCHANT_DESCRIPTOR_KEY => $this->getCbsMerchantDescriptor(),
                    self::PROFILE_ID_KEY => $this->getCbsProfileId(),
                    self::ACCESS_KEY => $this->getCbsAccessKey(),
                    self::API_KEY => $this->getCbsApiKey(),
                    self::API_SECRET_KEY => $this->getCbsApiSecretKey(),
                    self::SECRET_KEY => $this->getCbsSecretKey(),
                    self::METHOD => $this->getCbsMethod()
                ]
            );
        }

        return $this->settings;
    }

    /**
     * @return bool
     */
    public function getCbsTestMode()
    {
        return $this->cbsTestMode;
    }

    /**
     * @param bool $testMode
     *
     * @return CyberSourceSettings
     */
    public function setCbsTestMode($testMode)
    {
        $this->cbsTestMode = $testMode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCbsMerchantId()
    {
        return $this->cbsMerchantId;
    }

    /**
     * @param string $cbsMerchantId
     *
     * @return CyberSourceSettings
     */
    public function setCbsMerchantId($cbsMerchantId)
    {
        $this->cbsMerchantId = $cbsMerchantId;

        return $this;
    }

    /**
     * @return string
     */
    public function getCbsMerchantDescriptor()
    {
        return $this->cbsMerchantDescriptor;
    }

    /**
     * @param string $cbsMerchantDescriptor
     *
     * @return CyberSourceSettings
     */
    public function setCbsMerchantDescriptor($cbsMerchantDescriptor)
    {
        $this->cbsMerchantDescriptor = $cbsMerchantDescriptor;

        return $this;
    }

    /**
     * @return string
     */
    public function getCbsProfileId()
    {
        return $this->cbsProfileId;
    }

    /**
     * @param string $cbsProfileId
     *
     * @return CyberSourceSettings
     */
    public function setCbsProfileId($cbsProfileId)
    {
        $this->cbsProfileId = $cbsProfileId;

        return $this;
    }

    /**
     * @return string
     */
    public function getCbsAccessKey()
    {
        return $this->cbsAccessKey;
    }

    /**
     * @param string $cbsAccessKey
     *
     * @return CyberSourceSettings
     */
    public function setCbsAccessKey($cbsAccessKey)
    {
        $this->cbsAccessKey = $cbsAccessKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getCbsApiKey()
    {
        return $this->cbsApiKey;
    }

    /**
     * @param string $cbsApiKey
     *
     * @return CyberSourceSettings
     */
    public function setCbsApiKey($cbsApiKey)
    {
        $this->cbsApiKey = $cbsApiKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getCbsApiSecretKey()
    {
        return $this->cbsApiSecretKey;
    }

    /**
     * @param string $cbsApiSecretKey
     *
     * @return CyberSourceSettings
     */
    public function setCbsApiSecretKey($cbsApiSecretKey)
    {
        $this->cbsApiSecretKey = $cbsApiSecretKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getCbsSecretKey()
    {
        return $this->cbsSecretKey;
    }

    /**
     * @param string $cbsSecretKey
     *
     * @return CyberSourceSettings
     */
    public function setCbsSecretKey($cbsSecretKey)
    {
        $this->cbsSecretKey = $cbsSecretKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getCbsMethod()
    {
        return $this->cbsMethod;
    }

    /**
     * @param string $cbsMethod
     *
     * @return CyberSourceSettings
     */
    public function setCbsMethod($cbsMethod)
    {
        $this->cbsMethod = $cbsMethod;

        return $this;
    }
}
