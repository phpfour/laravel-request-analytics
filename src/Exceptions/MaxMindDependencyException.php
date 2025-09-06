<?php

namespace MeShaon\RequestAnalytics\Exceptions;

/**
 * Exception thrown when required MaxMind dependencies are missing
 */
class MaxMindDependencyException extends GeolocationException
{
    public function __construct(
        protected string $dependency,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $message = $message ?: "Required dependency '{$this->dependency}' is not available. Please install it using composer.";
        parent::__construct($message, $code, $previous);
    }

    public function getDependency(): string
    {
        return $this->dependency;
    }
}
