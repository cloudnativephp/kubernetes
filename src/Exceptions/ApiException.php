<?php

declare(strict_types=1);

namespace Kubernetes\Exceptions;

use Throwable;

/**
 * Exception thrown when an API request fails.
 *
 * This exception is thrown when the Kubernetes API returns an error response
 * or when there are network-related issues during API communication.
 */
class ApiException extends KubernetesException
{
    /**
     * Create a new API exception.
     *
     * @param string          $message    The error message
     * @param int             $statusCode The HTTP status code
     * @param Throwable|null $previous   The previous exception
     */
    public function __construct(string $message, int $statusCode = 0, ?Throwable $previous = null)
    {
        parent::__construct("API Error: {$message}", $statusCode, $previous);
    }
}
