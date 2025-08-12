<?php

declare(strict_types=1);

namespace Kubernetes\Client\Auth;

use Kubernetes\Exceptions\AuthenticationException;

/**
 * Token-based authentication for Kubernetes API.
 *
 * Provides authentication using a bearer token, commonly used with
 * service accounts, personal access tokens, or temporary tokens.
 */
class TokenAuthentication implements AuthenticationInterface
{
    protected string $serverUrl;
    protected string $token;
    protected ?string $caCertificate;
    protected bool $verifySsl;

    /**
     * Create a new token authentication instance.
     *
     * @param string      $serverUrl     The Kubernetes API server URL
     * @param string      $token         The bearer token
     * @param string|null $caCertificate CA certificate data for SSL verification
     * @param bool        $verifySsl     Whether to verify SSL certificates
     *
     * @throws AuthenticationException If parameters are invalid
     */
    public function __construct(
        string $serverUrl,
        string $token,
        ?string $caCertificate = null,
        bool $verifySsl = true
    ) {
        if (empty($serverUrl)) {
            throw new AuthenticationException('Server URL cannot be empty');
        }

        if (empty($token)) {
            throw new AuthenticationException('Token cannot be empty');
        }

        $this->serverUrl = rtrim($serverUrl, '/');
        $this->token = $token;
        $this->caCertificate = $caCertificate;
        $this->verifySsl = $verifySsl;
    }

    /**
     * Get the authentication headers for API requests.
     *
     * @return array<string, string> Headers to include in HTTP requests
     *
     * @throws AuthenticationException If authentication fails
     */
    public function getHeaders(): array
    {
        return [
            'Authorization' => "Bearer {$this->token}",
        ];
    }

    /**
     * Get the API server URL.
     *
     * @return string The Kubernetes API server URL
     *
     * @throws AuthenticationException If server URL cannot be determined
     */
    public function getServerUrl(): string
    {
        return $this->serverUrl;
    }

    /**
     * Get the CA certificate data for SSL verification.
     *
     * @return string|null The CA certificate data or null if not available
     */
    public function getCaCertificate(): ?string
    {
        return $this->caCertificate;
    }

    /**
     * Set CA certificate data.
     *
     * @param string|null $caCertificate CA certificate data or null to remove
     *
     * @return self
     */
    public function setCaCertificate(?string $caCertificate): self
    {
        $this->caCertificate = $caCertificate;
        return $this;
    }

    /**
     * Check if SSL verification should be enabled.
     *
     * @return bool True if SSL verification should be enabled
     */
    public function shouldVerifySsl(): bool
    {
        return $this->verifySsl;
    }

    /**
     * Get the client certificate data for mutual TLS.
     *
     * @return string|null The client certificate data or null if not available
     */
    public function getClientCertificate(): ?string
    {
        // Token authentication doesn't use client certificates
        return null;
    }

    /**
     * Get the client key data for mutual TLS.
     *
     * @return string|null The client key data or null if not available
     */
    public function getClientKey(): ?string
    {
        // Token authentication doesn't use client keys
        return null;
    }

    /**
     * Check if the authentication is valid and ready to use.
     *
     * @return bool True if authentication is valid
     */
    public function isValid(): bool
    {
        return !empty($this->serverUrl) && !empty($this->token);
    }

    /**
     * Refresh the authentication if supported (e.g., token refresh).
     *
     * @return bool True if refresh was successful
     *
     * @throws AuthenticationException If refresh fails
     */
    public function refresh(): bool
    {
        // Static tokens don't need refresh
        return true;
    }

    /**
     * Get the token.
     *
     * @return string The bearer token
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Set a new token.
     *
     * @param string $token The new bearer token
     *
     * @return self
     *
     * @throws AuthenticationException If token is invalid
     */
    public function setToken(string $token): self
    {
        if (empty($token)) {
            throw new AuthenticationException('Token cannot be empty');
        }

        $this->token = $token;
        return $this;
    }

    /**
     * Set SSL verification setting.
     *
     * @param bool $verifySsl Whether to verify SSL certificates
     *
     * @return self
     */
    public function setVerifySsl(bool $verifySsl): self
    {
        $this->verifySsl = $verifySsl;
        return $this;
    }
}
