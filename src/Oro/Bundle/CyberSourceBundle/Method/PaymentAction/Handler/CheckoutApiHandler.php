<?php

namespace Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Handler;

use CyberSource\Api\CaptureApi;
use CyberSource\Api\KeyGenerationApi;
use CyberSource\Api\PaymentsApi;
use CyberSource\Api\ReversalApi;
use CyberSource\ApiClient;
use CyberSource\ApiException;
use CyberSource\Authentication\Core\MerchantConfiguration;
use CyberSource\Authentication\Util\GlobalParameter;
use CyberSource\Configuration;
use CyberSource\Model\AuthReversalRequest;
use CyberSource\Model\CapturePaymentRequest;
use CyberSource\Model\CreatePaymentRequest;
use CyberSource\Model\FlexV1KeysPost200Response;
use CyberSource\Model\GeneratePublicKeyRequest;
use CyberSource\Model\PtsV2PaymentsCapturesPost201Response;
use CyberSource\Model\PtsV2PaymentsPost201Response;
use CyberSource\Model\PtsV2PaymentsReversalsPost201Response;
use Oro\Bundle\CyberSourceBundle\CyberSource\Factory\ApiClientFactoryInterface;
use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\AbstractPaymentAction;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Handler\DTO\ApiContextInfo;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Resolver\WebsiteUrlResolver;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Handles all actions for API calls in one place.
 */
class CheckoutApiHandler implements LoggerAwareInterface
{
    const ENCRYPTION_TYPE = 'RsaOaep256';
    const FORMAT = 'JWT';
    const PAYMENT_AUTHORIZED_RESPONSE_STATUS = 'AUTHORIZED';
    const ENV_PROD = 'api.cybersource.com';
    const ENV_TEST = 'apitest.cybersource.com';

    use LoggerAwareTrait;

    /** @var WebsiteUrlResolver */
    protected $websiteUrlResolver;

    /** @var WebsiteManager */
    protected $websiteManager;

    /** @var ApiClientFactoryInterface */
    protected $apiClientFactory;

    /**
     * @param WebsiteUrlResolver $urlResolver
     * @param WebsiteManager $websiteManager
     * @param ApiClientFactoryInterface $apiClientFactory
     */
    public function __construct(
        WebsiteUrlResolver $urlResolver,
        WebsiteManager $websiteManager,
        ApiClientFactoryInterface $apiClientFactory
    ) {
        $this->websiteUrlResolver = $urlResolver;
        $this->websiteManager = $websiteManager;
        $this->apiClientFactory = $apiClientFactory;
    }

    /**
     * @param CyberSourceConfigInterface $config
     *
     * @return ApiClient
     */
    protected function getApiClient(CyberSourceConfigInterface $config)
    {
        $merchantConfig = new MerchantConfiguration();

        $merchantConfig->setAuthenticationType(GlobalParameter::HTTP_SIGNATURE);
        $merchantConfig->setRunEnvironment($config->isTestMode() ? self::ENV_TEST : self::ENV_PROD);
        $merchantConfig->setMerchantID($config->getMerchantId());
        $merchantConfig->setApiKeyID($config->getApiKey());
        $merchantConfig->setSecretKey($config->getApiSecretKey());

        $merchantConfig->validateMerchantData();

        $config = new Configuration();
        $config->setHost($merchantConfig->getHost());
        $config->setSSLVerification(true);

        return $this->apiClientFactory->create($config, $merchantConfig);
    }

    /**
     * @param CyberSourceConfigInterface $config
     * @param $options
     *
     * @return ApiContextInfo
     */
    public function handlePurchase(CyberSourceConfigInterface $config, $options)
    {
        $result = new ApiContextInfo();

        $request = new CreatePaymentRequest($options);
        $result->setRequest(json_decode((string)$request, true));

        $paymentApi = new PaymentsApi($this->getApiClient($config));
        try {
            [$response, $statusCode, $httpHeader] = $paymentApi->createPayment($request);

            if ($response instanceof PtsV2PaymentsPost201Response) {
                if ($response->getStatus() === self::PAYMENT_AUTHORIZED_RESPONSE_STATUS) {
                    $result->setIsSuccessfull(true);
                }
                $response = json_decode((string)$response, true);
                $response[AbstractPaymentAction::TRANSACTION_ID_KEY] = $response['id'] ?: '';
                $result->setResponse($response);
            }
        } catch (ApiException $e) {
            $message = sprintf(
                'Authorize payment transaction failed. Exception message "%s".',
                $e->getMessage()
            );
            if ($e->getResponseBody() instanceof \stdClass) {
                $message .= ' API call response body: ' . json_encode($e->getResponseBody());
                if (isset($e->getResponseBody()->message)) {
                    $result->setErrorMessage($e->getResponseBody()->message);
                }
            }
            $this->logger->error($message);
        }

        return $result;
    }

    /**
     * @param CyberSourceConfigInterface $config
     * @param array $options
     * @param string $transactionId
     *
     * @return ApiContextInfo
     */
    public function handleCancel(CyberSourceConfigInterface $config, $options, $transactionId)
    {
        $result = new ApiContextInfo();

        $request = new AuthReversalRequest($options);
        $result->setRequest(json_decode((string)$request, true));

        $cancelApi = new ReversalApi($this->getApiClient($config));
        try {
            [$response, $statusCode, $httpHeader] = $cancelApi->authReversal($transactionId, $request);

            if ($response instanceof PtsV2PaymentsReversalsPost201Response) {
                $result->setIsSuccessfull(true);
                $result->setResponse(json_decode((string)$response, true));
            }
        } catch (ApiException $e) {
            $message = sprintf(
                'Canceling payment transaction with id "%s" failed. Exception message "%s".',
                $transactionId,
                $e->getMessage()
            );
            if ($e->getResponseBody() instanceof \stdClass) {
                $message .= ' API call response body: ' . json_encode($e->getResponseBody());
                if (isset($e->getResponseBody()->message)) {
                    $result->setErrorMessage($e->getResponseBody()->message);
                }
            }
            $this->logger->error($message);
        }

        return $result;
    }

    /**
     * @param CyberSourceConfigInterface $config
     * @param $options
     * @param $transactionId
     *
     * @return ApiContextInfo
     */
    public function handleCapture(CyberSourceConfigInterface $config, $options, $transactionId)
    {
        $result = new ApiContextInfo();

        $request = new CapturePaymentRequest($options);
        $result->setRequest(json_decode((string)$request, true));

        $captureApi = new CaptureApi($this->getApiClient($config));
        try {
            [$response, $statusCode, $httpHeader] = $captureApi->capturePayment($request, $transactionId);

            if ($response instanceof PtsV2PaymentsCapturesPost201Response) {
                $result->setIsSuccessfull(true);
                $result->setResponse(json_decode((string)$response, true));
            }
        } catch (ApiException $e) {
            $message = sprintf(
                'Capturing payment transaction with id "%s" failed. Exception message "%s".',
                $transactionId,
                $e->getMessage()
            );
            if ($e->getResponseBody() instanceof \stdClass) {
                $message .= ' API call response body: ' . json_encode($e->getResponseBody());
                if (isset($e->getResponseBody()->message)) {
                    $result->setErrorMessage($e->getResponseBody()->message);
                }
            }
            $this->logger->error($message);
        }

        return $result;
    }

    /**
     * @param CyberSourceConfigInterface $config
     *
     * @return ApiContextInfo
     */
    public function handleGeneratePublicKey(CyberSourceConfigInterface $config)
    {
        $result = new ApiContextInfo();

        $currentWebsite = $this->websiteManager->getCurrentWebsite();
        $requestArray = [
            'encryptionType' => self::ENCRYPTION_TYPE,
            'targetOrigin'   => $this->websiteUrlResolver->getWebsiteSecureUrl($currentWebsite, true),
        ];
        $request = new GeneratePublicKeyRequest($requestArray);
        $result->setRequest(json_decode((string)$request, true));

        $keyGenerationApi = new KeyGenerationApi($this->getApiClient($config));
        try {
            [$response, $statusCode, $httpHeader] = $keyGenerationApi->generatePublicKey(
                self::FORMAT,
                $request
            );

            if ($response instanceof FlexV1KeysPost200Response) {
                $result->setIsSuccessfull(true);
                $result->setResponse(json_decode((string)$response, true));
            }
        } catch (ApiException $e) {
            $this->logger->error('Could not generate public key.', ['exception' => $e]);
        }

        return $result;
    }
}
