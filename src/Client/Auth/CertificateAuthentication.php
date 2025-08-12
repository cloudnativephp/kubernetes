<?php

declare(strict_types=1);

namespace Kubernetes\Client\Auth;

use Kubernetes\Exceptions\AuthenticationException;

/**
 * Certificate-based authentication for Kubernetes API.
 *
 * Provides authentication using client certificates for mutual TLS,
 * commonly used for administrative access and service-to-service communication.
 */
class CertificateAuthentication implements AuthenticationInterface
{
    protected string $serverUrl;
    protected string $clientCertificate;
    protected string $clientKey;
    protected ?string $caCertificate;
    protected bool $verifySsl;

    /**
     * Create a new certificate authentication instance.
     *
     * @param string      $serverUrl         The Kubernetes API server URL
     * @param string      $clientCertificate Client certificate data (PEM format)
     * @param string      $clientKey         Client key data (PEM format)
     * @param string|null $caCertificate     CA certificate data for SSL verification
     * @param bool        $verifySsl         Whether to verify SSL certificates
     *
     * @throws AuthenticationException If parameters are invalid
     */
    public function __construct(
        string $serverUrl,
        string $clientCertificate,
        string $clientKey,
        ?string $caCertificate = null,
        bool $verifySsl = true
    ) {
        if (empty($serverUrl)) {
            throw new AuthenticationException('Server URL cannot be empty');
        }

        if (empty($clientCertificate)) {
            throw new AuthenticationException('Client certificate cannot be empty');
        }

        if (empty($clientKey)) {
            throw new AuthenticationException('Client key cannot be empty');
        }

        $this->serverUrl = rtrim($serverUrl, '/');
        $this->clientCertificate = $clientCertificate;
        $this->clientKey = $clientKey;
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
        // Certificate authentication is handled by the HTTP client through SSL/TLS
        // No special headers are needed for client certificate authentication
        return [];
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
        return $this->clientCertificate;
    }

    /**
     * Get the client key data for mutual TLS.
     *
     * @return string|null The client key data or null if not available
     */
    public function getClientKey(): ?string
    {
        return $this->clientKey;
    }

    /**
     * Check if the authentication is valid and ready to use.
     *
     * @return bool True if authentication is valid
     */
    public function isValid(): bool
    {
        return !empty($this->serverUrl) &&
            !empty($this->clientCertificate) &&
            !empty($this->clientKey);
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
        // Certificate authentication doesn't need refresh
        return true;
    }

    /**
     * Get the client certificate data.
     *
     * @return string The client certificate data
     */
    public function getCertificateData(): string
    {
        return $this->clientCertificate;
    }

    /**
     * Get the client key data.
     *
     * @return string The client key data
     */
    public function getKeyData(): string
    {
        return $this->clientKey;
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

    /**
     * Load certificate and key from files.
     *
     * @param string $certificateFile Path to client certificate file
     * @param string $keyFile         Path to client key file
     *
     * @return self
     *
     * @throws AuthenticationException If files cannot be read
     */
    public function loadFromFiles(string $certificateFile, string $keyFile): self
    {
        if (!file_exists($certificateFile)) {
            throw new AuthenticationException("Certificate file not found: {$certificateFile}");
        }

        if (!file_exists($keyFile)) {
            throw new AuthenticationException("Key file not found: {$keyFile}");
        }

        $certificate = file_get_contents($certificateFile);
        if ($certificate === false) {
            throw new AuthenticationException("Unable to read certificate file: {$certificateFile}");
        }

        $key = file_get_contents($keyFile);
        if ($key === false) {
            throw new AuthenticationException("Unable to read key file: {$keyFile}");
        }

        return $this->setCertificateAndKey($certificate, $key);
    }

    /**
     * Set new certificate and key data.
     *
     * @param string $clientCertificate Client certificate data (PEM format)
     * @param string $clientKey         Client key data (PEM format)
     *
     * @return self
     *
     * @throws AuthenticationException If certificate or key is invalid
     */
    public function setCertificateAndKey(string $clientCertificate, string $clientKey): self
    {
        if (empty($clientCertificate)) {
            throw new AuthenticationException('Client certificate cannot be empty');
        }

        if (empty($clientKey)) {
            throw new AuthenticationException('Client key cannot be empty');
        }

        $this->clientCertificate = $clientCertificate;
        $this->clientKey = $clientKey;
        return $this;
    }
}
