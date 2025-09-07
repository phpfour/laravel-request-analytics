<?php

namespace MeShaon\RequestAnalytics\Exceptions;

/**
 * Exception thrown when MaxMind is misconfigured
 */
class MaxMindConfigurationException extends GeolocationException
{
    public function __construct(
        protected string $configurationType,
        string $message = '',
        int|string $code = 0,
        ?\Throwable $previous = null
    ) {
        $message = $message ?: "MaxMind {$this->configurationType} configuration is invalid or missing";
        parent::__construct($message, $code, $previous);
    }

    public function getConfigurationType(): string
    {
        return $this->configurationType;
    }
}
