<?php

declare(strict_types=1);

namespace Kubernetes\Client\Auth;

use Kubernetes\Exceptions\AuthenticationException;

/**
 * Interface for Kubernetes authentication implementations.
 *
 * Defines the contract for authentication methods used to connect to the Kubernetes API.
 * Supports various authentication mechanisms including kubeconfig, service accounts,
 * and token-based authentication.
 */
interface AuthenticationInterface
{
    /**
     * Get the authentication headers for API requests.
     *
     * @return array<string, string> Headers to include in HTTP requests
     *
     * @throws AuthenticationException If authentication fails
     */
    public function getHeaders(): array;

    /**
     * Get the API server URL.
     *
     * @return string The Kubernetes API server URL
     *
     * @throws AuthenticationException If server URL cannot be determined
     */
    public function getServerUrl(): string;

    /**
     * Get the CA certificate data for SSL verification.
     *
     * @return string|null The CA certificate data or null if not available
     */
    public function getCaCertificate(): ?string;

    /**
     * Check if SSL verification should be enabled.
     *
     * @return bool True if SSL verification should be enabled
     */
    public function shouldVerifySsl(): bool;

    /**
     * Get the client certificate data for mutual TLS.
     *
     * @return string|null The client certificate data or null if not available
     */
    public function getClientCertificate(): ?string;

    /**
     * Get the client key data for mutual TLS.
     *
     * @return string|null The client key data or null if not available
     */
    public function getClientKey(): ?string;

    /**
     * Check if the authentication is valid and ready to use.
     *
     * @return bool True if authentication is valid
     */
    public function isValid(): bool;

    /**
     * Refresh the authentication if supported (e.g., token refresh).
     *
     * @return bool True if refresh was successful
     *
     * @throws AuthenticationException If refresh fails
     */
    public function refresh(): bool;
}
