<?php

namespace Oro\Bundle\CyberSourceBundle\Method\Config;

use Oro\Bundle\PaymentBundle\Method\Config\PaymentConfigInterface;

/**
 * Interface that describes specific configuration for CyberSource payment method
 */
interface CyberSourceConfigInterface extends PaymentConfigInterface
{
    /**
     * @return bool
     */
    public function isTestMode();

    /**
     * @return string
     */
    public function getMerchantId();

    /**
     * @return string
     */
    public function getMerchantDescriptor();

    /**
     * @return string
     */
    public function getProfileId();

    /**
     * @return string
     */
    public function getAccessKey();

    /**
     * @return string
     */
    public function getApiKey();

    /**
     * @return string
     */
    public function getApiSecretKey();

    /**
     * @return string
     */
    public function getSecretKey();

    /**
     * @return string
     */
    public function getMethod();

    public function getIgnoreAvs();

    /**
     * @return bool
     */
    public function getIgnoreCvn();

    /**
     * @return bool
     */
    public function getAuthReversal();

    /**
     * @return bool
     */
    public function getDisplayErrors();
}
