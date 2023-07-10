<?php

namespace Oro\Bundle\CyberSourceBundle\Method\Config;

use Oro\Bundle\PaymentBundle\Method\Config\ParameterBag\AbstractParameterBagPaymentConfig;

/**
 * Configuration class which is used to get specific configuration for CyberSource payment method
 */
class CyberSourceConfig extends AbstractParameterBagPaymentConfig implements CyberSourceConfigInterface
{
    const MERCHANT_ID_KEY = 'merchant_id';
    const MERCHANT_DESCRIPTOR_KEY = 'merchant_descriptor';
    const PROFILE_ID_KEY = 'profile_id';
    const ACCESS_KEY = 'access_key';
    const API_KEY = 'api_key';
    const API_SECRET_KEY = 'api_secret_key';
    const SECRET_KEY = 'secret_key';
    const TEST_MODE_KEY = 'test_mode';
    const METHOD_KEY = 'method';
    const IGNORE_AVS_KEY = 'ignore_avs';
    const IGNORE_CVN_KEY = 'ignore_cvn';
    const AUTH_REVERSAL_KEY = 'auth_reversal';
    const DISPLAY_ERRORS_KEY = 'display_errors';

    /**
     * @return bool
     */
    public function isTestMode()
    {
        return (bool)$this->get(self::TEST_MODE_KEY);
    }

    /**
     * @return string
     */
    public function getMerchantId()
    {
        return (string)$this->get(self::MERCHANT_ID_KEY);
    }

    /**
     * @return string
     */
    public function getMerchantDescriptor()
    {
        return (string)$this->get(self::MERCHANT_DESCRIPTOR_KEY);
    }

    /**
     * @return string
     */
    public function getProfileId()
    {
        return (string)$this->get(self::PROFILE_ID_KEY);
    }

    /**
     * @return string
     */
    public function getAccessKey()
    {
        return (string)$this->get(self::ACCESS_KEY);
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return (string)$this->get(self::API_KEY);
    }

    /**
     * @return string
     */
    public function getApiSecretKey()
    {
        return (string)$this->get(self::API_SECRET_KEY);
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        return (string)$this->get(self::SECRET_KEY);
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return (string)$this->get(self::METHOD_KEY);
    }

    /**
     * @return bool
     */
    public function getIgnoreAvs()
    {
        return (bool)$this->get(self::IGNORE_AVS_KEY);
    }

    /**
     * @return bool
     */
    public function getIgnoreCvn()
    {
        return (bool)$this->get(self::IGNORE_CVN_KEY);
    }

    /**
     * @return bool
     */
    public function getAuthReversal()
    {
        return (bool)$this->get(self::AUTH_REVERSAL_KEY);
    }

    public function getDisplayErrors()
    {
        return (bool)$this->get(self::DISPLAY_ERRORS_KEY);
    }
}
