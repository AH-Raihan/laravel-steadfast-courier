<?php

namespace AhRaihan\SteadfastCourier\Exceptions;

use Exception;

class SteadfastApiException extends Exception
{
    /**
     * HTTP status code.
     */
    protected int $statusCode = 0;

    /**
     * Response body if available.
     */
    protected ?array $responseBody = null;

    /**
     * Create a new exception instance.
     */
    public function __construct(string $message = '', int $statusCode = 0, ?array $responseBody = null, ?Exception $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
        $this->statusCode = $statusCode;
        $this->responseBody = $responseBody;
    }

    /**
     * Get HTTP status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get response body.
     */
    public function getResponseBody(): ?array
    {
        return $this->responseBody;
    }
}

