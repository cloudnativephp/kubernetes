<?php

declare(strict_types=1);

namespace Kubernetes\Exceptions;

use Throwable;

/**
 * Exception thrown when authentication fails.
 *
 * This exception is thrown when there are issues with Kubernetes authentication,
 * including invalid credentials, missing files, or authentication method failures.
 */
class AuthenticationException extends KubernetesException
{
    /**
     * Create a new authentication exception.
     *
     * @param string          $message  The error message
     * @param int             $code     The error code
     * @param Throwable|null $previous The previous exception
     */
    public function __construct(string $message, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct("Authentication failed: {$message}", $code, $previous);
    }
}
