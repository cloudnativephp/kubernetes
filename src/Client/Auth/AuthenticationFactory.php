<?php

declare(strict_types=1);

namespace Kubernetes\Client\Auth;

use Kubernetes\Exceptions\AuthenticationException;

/**
 * Factory for creating Kubernetes authentication instances.
 *
 * Provides convenient methods to create authentication instances for various
 * scenarios including automatic detection, kubeconfig files, and in-cluster
 * service account authentication.
 */
class AuthenticationFactory
{
    /**
     * Create an authentication instance automatically.
     *
     * Attempts to detect the best authentication method in the following order:
     * 1. In-cluster service account (if running in a pod)
     * 2. Kubeconfig from KUBECONFIG environment variable
     * 3. Default kubeconfig (~/.kube/config)
     *
     * @param string|null $context Kubeconfig context to use (only applies to kubeconfig auth)
     *
     * @return AuthenticationInterface The authentication instance
     *
     * @throws AuthenticationException If no authentication method is available
     */
    public static function create(?string $context = null): AuthenticationInterface
    {
        // Try in-cluster authentication first
        $inClusterAuth = InClusterAuthentication::tryCreate();
        if ($inClusterAuth !== null) {
            return $inClusterAuth;
        }

        // Try kubeconfig authentication
        try {
            return new KubeconfigAuthentication(null, $context);
        } catch (AuthenticationException $e) {
            throw new AuthenticationException(
                "No authentication method available. Tried in-cluster and kubeconfig: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Create kubeconfig-based authentication.
     *
     * @param string|null $kubeconfigPath Path to kubeconfig file (defaults to standard locations)
     * @param string|null $context        Context name to use (defaults to current-context)
     *
     * @return KubeconfigAuthentication The kubeconfig authentication instance
     *
     * @throws AuthenticationException If kubeconfig cannot be loaded
     */
    public static function kubeconfig(?string $kubeconfigPath = null, ?string $context = null): KubeconfigAuthentication
    {
        return new KubeconfigAuthentication($kubeconfigPath, $context);
    }

    /**
     * Create in-cluster service account authentication.
     *
     * @param string|null $apiServerHost Custom API server host (defaults to KUBERNETES_SERVICE_HOST)
     * @param int|null    $apiServerPort Custom API server port (defaults to KUBERNETES_SERVICE_PORT)
     *
     * @return InClusterAuthentication The in-cluster authentication instance
     *
     * @throws AuthenticationException If in-cluster authentication is not available
     */
    public static function inCluster(?string $apiServerHost = null, ?int $apiServerPort = null): InClusterAuthentication
    {
        return new InClusterAuthentication($apiServerHost, $apiServerPort);
    }

    /**
     * Create token-based authentication.
     *
     * @param string      $serverUrl     The Kubernetes API server URL
     * @param string      $token         The bearer token
     * @param string|null $caCertificate CA certificate data for SSL verification
     * @param bool        $verifySsl     Whether to verify SSL certificates
     *
     * @return TokenAuthentication The token authentication instance
     */
    public static function token(
        string $serverUrl,
        string $token,
        ?string $caCertificate = null,
        bool $verifySsl = true
    ): TokenAuthentication {
        return new TokenAuthentication($serverUrl, $token, $caCertificate, $verifySsl);
    }

    /**
     * Create certificate-based authentication.
     *
     * @param string      $serverUrl         The Kubernetes API server URL
     * @param string      $clientCertificate Client certificate data
     * @param string      $clientKey         Client key data
     * @param string|null $caCertificate     CA certificate data for SSL verification
     * @param bool        $verifySsl         Whether to verify SSL certificates
     *
     * @return CertificateAuthentication The certificate authentication instance
     */
    public static function certificate(
        string $serverUrl,
        string $clientCertificate,
        string $clientKey,
        ?string $caCertificate = null,
        bool $verifySsl = true
    ): CertificateAuthentication {
        return new CertificateAuthentication(
            $serverUrl,
            $clientCertificate,
            $clientKey,
            $caCertificate,
            $verifySsl
        );
    }

    /**
     * Check if in-cluster authentication is available.
     *
     * @return bool True if running inside a Kubernetes cluster
     */
    public static function isInCluster(): bool
    {
        return InClusterAuthentication::isInCluster();
    }

    /**
     * Get available kubeconfig contexts.
     *
     * @param string|null $kubeconfigPath Path to kubeconfig file
     *
     * @return array<string> List of available context names
     *
     * @throws AuthenticationException If kubeconfig cannot be loaded
     */
    public static function getAvailableContexts(?string $kubeconfigPath = null): array
    {
        $auth = new KubeconfigAuthentication($kubeconfigPath);
        return $auth->getAvailableContexts();
    }
}
