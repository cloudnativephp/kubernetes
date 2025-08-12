<?php

declare(strict_types=1);

namespace Kubernetes\Client\Auth;

use Exception;
use Kubernetes\Exceptions\AuthenticationException;

/**
 * In-cluster authentication for Kubernetes API.
 *
 * Provides authentication using the service account mounted in pods running
 * within a Kubernetes cluster. Automatically discovers the API server endpoint
 * and uses the mounted service account token for authentication.
 */
class InClusterAuthentication implements AuthenticationInterface
{
    protected const SERVICE_ACCOUNT_PATH = '/var/run/secrets/kubernetes.io/serviceaccount';
    protected const TOKEN_FILE = self::SERVICE_ACCOUNT_PATH . '/token';
    protected const CA_CERT_FILE = self::SERVICE_ACCOUNT_PATH . '/ca.crt';
    protected const NAMESPACE_FILE = self::SERVICE_ACCOUNT_PATH . '/namespace';

    protected ?string $token = null;
    protected ?string $caCertificate = null;
    protected ?string $namespace = null;
    protected string $apiServerHost;
    protected int $apiServerPort;

    /**
     * Create a new in-cluster authentication instance.
     *
     * @param string|null $apiServerHost Custom API server host (defaults to KUBERNETES_SERVICE_HOST)
     * @param int|null    $apiServerPort Custom API server port (defaults to KUBERNETES_SERVICE_PORT)
     *
     * @throws AuthenticationException If in-cluster authentication is not available
     */
    public function __construct(?string $apiServerHost = null, ?int $apiServerPort = null)
    {
        $this->apiServerHost = $apiServerHost ?? $this->getEnvironmentVariable('KUBERNETES_SERVICE_HOST');
        $this->apiServerPort = $apiServerPort ?? (int) $this->getEnvironmentVariable('KUBERNETES_SERVICE_PORT');

        if (!$this->apiServerHost || !$this->apiServerPort) {
            throw new AuthenticationException(
                'In-cluster authentication requires KUBERNETES_SERVICE_HOST and KUBERNETES_SERVICE_PORT environment variables'
            );
        }

        $this->loadServiceAccountData();
    }

    /**
     * Get an environment variable with error handling.
     *
     * @param string $name Environment variable name
     *
     * @return string The environment variable value
     *
     * @throws AuthenticationException If environment variable is not set
     */
    protected function getEnvironmentVariable(string $name): string
    {
        $value = getenv($name);
        if ($value === false) {
            throw new AuthenticationException("Environment variable {$name} not set");
        }

        return $value;
    }

    /**
     * Load service account data from mounted files.
     *
     * @throws AuthenticationException If service account data cannot be loaded
     */
    protected function loadServiceAccountData(): void
    {
        // Load the service account token
        $this->token = $this->readServiceAccountFile(self::TOKEN_FILE, 'token');

        // Load the CA certificate (optional)
        try {
            $this->caCertificate = $this->readServiceAccountFile(self::CA_CERT_FILE, 'CA certificate', false);
        } catch (AuthenticationException) {
            // CA certificate is optional - we can still authenticate without it
            $this->caCertificate = null;
        }

        // Load the namespace (optional)
        try {
            $this->namespace = $this->readServiceAccountFile(self::NAMESPACE_FILE, 'namespace', false);
        } catch (AuthenticationException) {
            // Namespace is optional
            $this->namespace = null;
        }
    }

    /**
     * Read a service account file.
     *
     * @param string $filePath    Path to the service account file
     * @param string $description Description of the file for error messages
     * @param bool   $required    Whether the file is required
     *
     * @return string The file content
     *
     * @throws AuthenticationException If required file cannot be read
     */
    protected function readServiceAccountFile(string $filePath, string $description, bool $required = true): string
    {
        if (!file_exists($filePath)) {
            if ($required) {
                throw new AuthenticationException("Service account {$description} file not found: {$filePath}");
            }
            return '';
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            if ($required) {
                throw new AuthenticationException("Unable to read service account {$description} file: {$filePath}");
            }
            return '';
        }

        return trim($content);
    }

    /**
     * Create an in-cluster authentication instance if available.
     *
     * @return static|null The authentication instance or null if not in cluster
     */
    public static function tryCreate(): ?static
    {
        if (!self::isInCluster()) {
            return null;
        }

        try {
            return new static();
        } catch (AuthenticationException) {
            return null;
        }
    }

    /**
     * Check if running inside a Kubernetes cluster.
     *
     * @return bool True if running inside a cluster
     */
    public static function isInCluster(): bool
    {
        return file_exists(self::TOKEN_FILE) &&
            getenv('KUBERNETES_SERVICE_HOST') !== false &&
            getenv('KUBERNETES_SERVICE_PORT') !== false;
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
        if (!$this->token) {
            throw new AuthenticationException('Service account token not available');
        }

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
        return "https://{$this->apiServerHost}:{$this->apiServerPort}";
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
     * Check if SSL verification should be enabled.
     *
     * @return bool True if SSL verification should be enabled
     */
    public function shouldVerifySsl(): bool
    {
        // Always verify SSL in production cluster environments
        return true;
    }

    /**
     * Get the client certificate data for mutual TLS.
     *
     * @return string|null The client certificate data or null if not available
     */
    public function getClientCertificate(): ?string
    {
        // Service account authentication doesn't use client certificates
        return null;
    }

    /**
     * Get the client key data for mutual TLS.
     *
     * @return string|null The client key data or null if not available
     */
    public function getClientKey(): ?string
    {
        // Service account authentication doesn't use client keys
        return null;
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
        try {
            $this->loadServiceAccountData();
            return $this->isValid();
        } catch (Exception $e) {
            throw new AuthenticationException("Failed to refresh service account token: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Check if the authentication is valid and ready to use.
     *
     * @return bool True if authentication is valid
     */
    public function isValid(): bool
    {
        return $this->token !== null &&
            $this->apiServerHost !== '' &&
            $this->apiServerPort > 0;
    }

    /**
     * Get the current namespace from the service account.
     *
     * @return string|null The namespace or null if not available
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    /**
     * Get the service account token.
     *
     * @return string|null The service account token or null if not available
     */
    public function getToken(): ?string
    {
        return $this->token;
    }
}
