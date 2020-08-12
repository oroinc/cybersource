<?php

namespace Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Handler\DTO;

/**
 * Represents information on handling CyberSource Checkout Api.
 */
class ApiContextInfo
{
    /** @var array */
    protected $request;

    /** @var array */
    protected $response;

    /** @var boolean */
    protected $isSuccessfull;

    /** @var string */
    protected $errorMessage = '';

    /**
     * @return array
     */
    public function getRequest(): array
    {
        return $this->request;
    }

    /**
     * @param array $request
     */
    public function setRequest(array $request): void
    {
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    /**
     * @param array $response
     */
    public function setResponse(array $response): void
    {
        $this->response = $response;
    }

    /**
     * @return bool
     */
    public function isSuccessfull(): bool
    {
        return $this->isSuccessfull;
    }

    /**
     * @param bool $isSuccessfull
     */
    public function setIsSuccessfull(bool $isSuccessfull): void
    {
        $this->isSuccessfull = $isSuccessfull;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     */
    public function setErrorMessage(string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }
}
