<?php

namespace Oro\Bundle\CyberSourceBundle\Tests\Behat\Mock\CyberSource;

use CyberSource\ApiClient;
use CyberSource\ApiException;
use CyberSource\Model\CreatePaymentRequest;
use CyberSource\Model\FlexV1KeysPost200Response;
use CyberSource\Model\PtsV2PaymentsCapturesPost201Response;
use CyberSource\Model\PtsV2PaymentsPost201Response;
use CyberSource\Model\PtsV2PaymentsReversalsPost201Response;

class ApiClientMock extends ApiClient
{
    private const VALID_MERCHANT_ID = 'merchant_id_behat';
    private const VALID_FLEXIBLE_FORM_TOKEN = 'valid_flexible_form_token';

    /**
     * {@inheritDoc}
     */
    public function callApi(
        $resourcePath,
        $method,
        $queryParams,
        $postData,
        $headerParams,
        $responseType = null,
        $endpointPath = null
    ) {
        switch (trim($responseType, '\\')) {
            case PtsV2PaymentsPost201Response::class:
                $response = $this->getPurchaseResponse($postData);
                break;
            case FlexV1KeysPost200Response::class:
                $response = $this->getGeneratePublicKeyResponse();
                break;
            case PtsV2PaymentsCapturesPost201Response::class:
                $response = $this->getCaptureResponse();
                break;
            case PtsV2PaymentsReversalsPost201Response::class:
                $response = $this->getCancelResponse();
                break;
            default:
                $response = null;
        }

        return [$response, 200, []];
    }

    /**
     * @param CreatePaymentRequest $data
     *
     * @return \stdClass
     *
     * @throws ApiException
     */
    protected function getPurchaseResponse(CreatePaymentRequest $data): \stdClass
    {
        if (self::VALID_FLEXIBLE_FORM_TOKEN === $data->getTokenInformation()->getTransientTokenJwt()) {
            $response = new \stdClass();
            $response->id = 'id';

            return $response;
        }

        throw new ApiException('Could not authorize payment.');
    }

    /**
     * @return \stdClass
     *
     * @throws ApiException
     */
    protected function getGeneratePublicKeyResponse(): \stdClass
    {
        if (self::VALID_MERCHANT_ID === $this->merchantConfig->getMerchantID()) {
            $response = new \stdClass();
            $response->keyId = 'flex_key';

            return $response;
        }

        throw new ApiException('Could not generate public key.');
    }

    /**
     * @return \stdClass
     *
     * @throws ApiException
     */
    protected function getCaptureResponse(): \stdClass
    {
        if (self::VALID_MERCHANT_ID === $this->merchantConfig->getMerchantID()) {
            return new \stdClass();
        }

        $body = new \stdClass();
        $body->message = 'Could not capture authorization.';

        throw new ApiException('Could not capture authorization.', 401, [], $body);
    }

    /**
     * @return \stdClass
     *
     * @throws ApiException
     */
    protected function getCancelResponse(): \stdClass
    {
        if (self::VALID_MERCHANT_ID === $this->merchantConfig->getMerchantID()) {
            return new \stdClass();
        }

        $body = new \stdClass();
        $body->message = 'Could not cancel authorization.';

        throw new ApiException('Could not cancel authorization.', 401, [], $body);
    }
}
