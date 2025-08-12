<?php

declare(strict_types=1);

namespace Kubernetes\Client\Auth;

use Exception;
use Kubernetes\Exceptions\AuthenticationException;
use Symfony\Component\Yaml\Yaml;

/**
 * Kubeconfig-based authentication for Kubernetes API.
 *
 * Supports loading authentication configuration from kubeconfig files,
 * including multiple contexts, users, and clusters. Handles various
 * authentication methods including certificates, tokens, and exec plugins.
 */
class KubeconfigAuthentication implements AuthenticationInterface
{
    protected array $config = [];
    protected ?string $currentContext = null;
    protected ?array $cluster = null;
    protected ?array $user = null;
    protected string $kubeconfigPath;

    /**
     * Create a new kubeconfig authentication instance.
     *
     * @param string|null $kubeconfigPath Path to kubeconfig file (defaults to ~/.kube/config)
     * @param string|null $context        Context name to use (defaults to current-context)
     *
     * @throws AuthenticationException If kubeconfig cannot be loaded
     */
    public function __construct(?string $kubeconfigPath = null, ?string $context = null)
    {
        $this->kubeconfigPath = $kubeconfigPath ?? $this->getDefaultKubeconfigPath();
        $this->loadKubeconfig();
        $this->setContext($context);
    }

    /**
     * Get the default kubeconfig path.
     *
     * @return string The default kubeconfig path
     */
    protected function getDefaultKubeconfigPath(): string
    {
        // Check KUBECONFIG environment variable
        $kubeconfigEnv = getenv('KUBECONFIG');
        if ($kubeconfigEnv) {
            // KUBECONFIG can contain multiple paths separated by colons
            $paths = explode(':', $kubeconfigEnv);
            foreach ($paths as $path) {
                if (file_exists(trim($path))) {
                    return trim($path);
                }
            }
        }

        // Default to ~/.kube/config
        $home = getenv('HOME') ?: getenv('USERPROFILE') ?: '.';
        return $home . '/.kube/config';
    }

    /**
     * Load the kubeconfig file.
     *
     * @throws AuthenticationException If kubeconfig cannot be loaded
     */
    protected function loadKubeconfig(): void
    {
        if (!file_exists($this->kubeconfigPath)) {
            throw new AuthenticationException("Kubeconfig file not found: {$this->kubeconfigPath}");
        }

        $content = file_get_contents($this->kubeconfigPath);
        if ($content === false) {
            throw new AuthenticationException("Unable to read kubeconfig file: {$this->kubeconfigPath}");
        }

        try {
            $this->config = Yaml::parse($content);
        } catch (Exception $e) {
            throw new AuthenticationException("Invalid YAML in kubeconfig: {$e->getMessage()}", 0, $e);
        }

        if (!is_array($this->config)) {
            throw new AuthenticationException('Kubeconfig must contain a YAML object');
        }
    }

    /**
     * Set the current context.
     *
     * @param string|null $contextName Context name or null for current-context
     *
     * @throws AuthenticationException If context is not found
     */
    protected function setContext(?string $contextName): void
    {
        // Use provided context or fall back to current-context
        $contextName ??= $this->config['current-context'] ?? null;

        if (!$contextName) {
            throw new AuthenticationException('No context specified and no current-context set');
        }

        // Find the context configuration
        $contextConfig = null;
        foreach ($this->config['contexts'] ?? [] as $context) {
            if ($context['name'] === $contextName) {
                $contextConfig = $context['context'] ?? [];
                break;
            }
        }

        if (!$contextConfig) {
            throw new AuthenticationException("Context '{$contextName}' not found in kubeconfig");
        }

        $this->currentContext = $contextName;

        // Load cluster configuration
        $this->loadCluster($contextConfig['cluster'] ?? null);

        // Load user configuration
        $this->loadUser($contextConfig['user'] ?? null);
    }

    /**
     * Load cluster configuration.
     *
     * @param string|null $clusterName The cluster name
     *
     * @throws AuthenticationException If cluster is not found
     */
    protected function loadCluster(?string $clusterName): void
    {
        if (!$clusterName) {
            throw new AuthenticationException('No cluster specified in context');
        }

        foreach ($this->config['clusters'] ?? [] as $cluster) {
            if ($cluster['name'] === $clusterName) {
                $this->cluster = $cluster['cluster'] ?? [];
                return;
            }
        }

        throw new AuthenticationException("Cluster '{$clusterName}' not found in kubeconfig");
    }

    /**
     * Load user configuration.
     *
     * @param string|null $userName The user name
     *
     * @throws AuthenticationException If user is not found
     */
    protected function loadUser(?string $userName): void
    {
        if (!$userName) {
            throw new AuthenticationException('No user specified in context');
        }

        foreach ($this->config['users'] ?? [] as $user) {
            if ($user['name'] === $userName) {
                $this->user = $user['user'] ?? [];
                return;
            }
        }

        throw new AuthenticationException("User '{$userName}' not found in kubeconfig");
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
        if (!$this->user) {
            throw new AuthenticationException('No user configuration found');
        }

        $headers = [];

        // Token authentication
        if (isset($this->user['token'])) {
            $headers['Authorization'] = "Bearer {$this->user['token']}";
        }

        // Token file authentication
        if (isset($this->user['tokenFile'])) {
            $token = $this->readTokenFile($this->user['tokenFile']);
            $headers['Authorization'] = "Bearer {$token}";
        }

        // Exec plugin authentication
        if (isset($this->user['exec'])) {
            $token = $this->executeAuthPlugin($this->user['exec']);
            $headers['Authorization'] = "Bearer {$token}";
        }

        // Username/password authentication (basic auth)
        if (isset($this->user['username'], $this->user['password'])) {
            $credentials = base64_encode($this->user['username'] . ':' . $this->user['password']);
            $headers['Authorization'] = "Basic {$credentials}";
        }

        return $headers;
    }

    /**
     * Read a token from a file.
     *
     * @param string $tokenFile Path to the token file
     *
     * @return string The token content
     *
     * @throws AuthenticationException If token file cannot be read
     */
    protected function readTokenFile(string $tokenFile): string
    {
        $resolvedPath = $this->resolvePath($tokenFile);

        if (!file_exists($resolvedPath)) {
            throw new AuthenticationException("Token file not found: {$resolvedPath}");
        }

        $token = file_get_contents($resolvedPath);
        if ($token === false) {
            throw new AuthenticationException("Unable to read token file: {$resolvedPath}");
        }

        return trim($token);
    }

    /**
     * Resolve a path relative to the kubeconfig file.
     *
     * @param string $path The path to resolve
     *
     * @return string The resolved absolute path
     */
    protected function resolvePath(string $path): string
    {
        if (str_starts_with($path, '/') || str_contains($path, ':')) {
            // Already absolute path (Unix or Windows)
            return $path;
        }

        // Resolve relative to kubeconfig directory
        $kubeconfigDir = dirname($this->kubeconfigPath);
        return $kubeconfigDir . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * Execute an authentication plugin.
     *
     * @param array<string, mixed> $execConfig The exec plugin configuration
     *
     * @return string The authentication token
     *
     * @throws AuthenticationException If exec plugin fails
     */
    protected function executeAuthPlugin(array $execConfig): string
    {
        $command = $execConfig['command'] ?? null;
        if (!$command) {
            throw new AuthenticationException('Exec plugin missing command');
        }

        $args = $execConfig['args'] ?? [];
        $env = $execConfig['env'] ?? [];

        // Build the command
        $fullCommand = escapeshellcmd($command);
        foreach ($args as $arg) {
            $fullCommand .= ' ' . escapeshellarg($arg);
        }

        // Set environment variables
        $currentEnv = $_ENV;
        foreach ($env as $envVar) {
            if (isset($envVar['name'], $envVar['value'])) {
                putenv($envVar['name'] . '=' . $envVar['value']);
            }
        }

        try {
            // Execute the command
            $output = shell_exec($fullCommand);
            if ($output === null || $output === false) {
                throw new AuthenticationException('Exec plugin failed to execute');
            }

            // Parse the output as JSON
            $result = json_decode($output, true);
            if (!$result || !isset($result['status']['token'])) {
                throw new AuthenticationException('Exec plugin returned invalid response');
            }

            return $result['status']['token'];
        } finally {
            // Restore environment
            foreach ($env as $envVar) {
                if (isset($envVar['name']) && isset($currentEnv[$envVar['name']])) {
                    putenv($envVar['name'] . '=' . $currentEnv[$envVar['name']]);
                } elseif (isset($envVar['name'])) {
                    putenv($envVar['name']);
                }
            }
        }
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
        if (!$this->cluster || !isset($this->cluster['server'])) {
            throw new AuthenticationException('No server URL found in cluster configuration');
        }

        return $this->cluster['server'];
    }

    /**
     * Get the CA certificate data for SSL verification.
     *
     * @return string|null The CA certificate data or null if not available
     */
    public function getCaCertificate(): ?string
    {
        if (!$this->cluster) {
            return null;
        }

        // Certificate data (base64 encoded)
        if (isset($this->cluster['certificate-authority-data'])) {
            return base64_decode($this->cluster['certificate-authority-data']);
        }

        // Certificate file
        if (isset($this->cluster['certificate-authority'])) {
            $caFile = $this->resolvePath($this->cluster['certificate-authority']);
            if (file_exists($caFile)) {
                return file_get_contents($caFile) ?: null;
            }
        }

        return null;
    }

    /**
     * Check if SSL verification should be enabled.
     *
     * @return bool True if SSL verification should be enabled
     */
    public function shouldVerifySsl(): bool
    {
        if (!$this->cluster) {
            return true; // Default to secure
        }

        // Check for insecure-skip-tls-verify flag
        return !($this->cluster['insecure-skip-tls-verify'] ?? false);
    }

    /**
     * Get the client certificate data for mutual TLS.
     *
     * @return string|null The client certificate data or null if not available
     */
    public function getClientCertificate(): ?string
    {
        if (!$this->user) {
            return null;
        }

        // Certificate data (base64 encoded)
        if (isset($this->user['client-certificate-data'])) {
            return base64_decode($this->user['client-certificate-data']);
        }

        // Certificate file
        if (isset($this->user['client-certificate'])) {
            $certFile = $this->resolvePath($this->user['client-certificate']);
            if (file_exists($certFile)) {
                return file_get_contents($certFile) ?: null;
            }
        }

        return null;
    }

    /**
     * Get the client key data for mutual TLS.
     *
     * @return string|null The client key data or null if not available
     */
    public function getClientKey(): ?string
    {
        if (!$this->user) {
            return null;
        }

        // Key data (base64 encoded)
        if (isset($this->user['client-key-data'])) {
            return base64_decode($this->user['client-key-data']);
        }

        // Key file
        if (isset($this->user['client-key'])) {
            $keyFile = $this->resolvePath($this->user['client-key']);
            if (file_exists($keyFile)) {
                return file_get_contents($keyFile) ?: null;
            }
        }

        return null;
    }

    /**
     * Check if the authentication is valid and ready to use.
     *
     * @return bool True if authentication is valid
     */
    public function isValid(): bool
    {
        try {
            // Check if we have a valid cluster and user configuration
            if (!$this->cluster || !$this->user) {
                return false;
            }

            // Check if we have a server URL
            if (!isset($this->cluster['server'])) {
                return false;
            }

            // Check if we have at least one authentication method
            $hasAuth = isset($this->user['token']) ||
                isset($this->user['tokenFile']) ||
                isset($this->user['exec']) ||
                (isset($this->user['username']) && isset($this->user['password'])) ||
                isset($this->user['client-certificate-data']) ||
                isset($this->user['client-certificate']);

            return $hasAuth;
        } catch (Exception) {
            return false;
        }
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
        // For exec plugins, we can re-execute to get fresh tokens
        if (isset($this->user['exec'])) {
            try {
                $this->executeAuthPlugin($this->user['exec']);
                return true;
            } catch (Exception $e) {
                throw new AuthenticationException("Failed to refresh exec plugin: {$e->getMessage()}", 0, $e);
            }
        }

        // For token files, re-read the file
        if (isset($this->user['tokenFile'])) {
            try {
                $this->readTokenFile($this->user['tokenFile']);
                return true;
            } catch (Exception $e) {
                throw new AuthenticationException("Failed to refresh token file: {$e->getMessage()}", 0, $e);
            }
        }

        // Static tokens and certificates don't need refresh
        return true;
    }

    /**
     * Get the current context name.
     *
     * @return string|null The current context name
     */
    public function getCurrentContext(): ?string
    {
        return $this->currentContext;
    }

    /**
     * Get available contexts.
     *
     * @return array<string> List of available context names
     */
    public function getAvailableContexts(): array
    {
        $contexts = [];
        foreach ($this->config['contexts'] ?? [] as $context) {
            if (isset($context['name'])) {
                $contexts[] = $context['name'];
            }
        }
        return $contexts;
    }

    /**
     * Switch to a different context.
     *
     * @param string $contextName The context name to switch to
     *
     * @return self
     *
     * @throws AuthenticationException If context is not found
     */
    public function switchContext(string $contextName): self
    {
        $this->setContext($contextName);
        return $this;
    }
}
