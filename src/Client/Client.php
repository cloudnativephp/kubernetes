<?php

declare(strict_types=1);

namespace Kubernetes\Client;

use Exception;
use Kubernetes\Client\Auth\AuthenticationFactory;
use Kubernetes\Client\Auth\AuthenticationInterface;
use Kubernetes\Client\Http\GuzzleHttpClient;
use Kubernetes\Client\Http\HttpClientInterface;
use Kubernetes\Contracts\ClientInterface;
use Kubernetes\Contracts\ResourceInterface;
use Kubernetes\Exceptions\ApiException;
use Kubernetes\Exceptions\AuthenticationException;
use Kubernetes\Exceptions\ResourceNotFoundException;
use ReflectionClass;
use RuntimeException;

/**
 * Kubernetes HTTP client implementation.
 *
 * Provides HTTP-based interaction with the Kubernetes API server,
 * implementing the ClientInterface contract for resource operations.
 * Uses an abstracted HTTP client that supports multiple implementations
 * and integrates with the authentication system for secure API access.
 */
class Client implements ClientInterface
{
    protected HttpClientInterface $httpClient;
    protected AuthenticationInterface $authentication;

    /**
     * Create a new Kubernetes client.
     *
     * @param AuthenticationInterface|null $authentication Authentication instance (auto-detected if null)
     * @param HttpClientInterface|null     $httpClient     Custom HTTP client instance
     *
     * @throws AuthenticationException If authentication cannot be established
     */
    public function __construct(
        ?AuthenticationInterface $authentication = null,
        ?HttpClientInterface $httpClient = null
    ) {
        $this->authentication = $authentication ?? AuthenticationFactory::create();
        $this->httpClient = $httpClient ?? new GuzzleHttpClient();

        $this->configureHttpClient();
    }

    /**
     * Create a new resource.
     *
     * @param ResourceInterface $resource The resource to create
     *
     * @return ResourceInterface The created resource with server-assigned fields
     *
     * @throws ApiException If creation fails
     */
    public function create(ResourceInterface $resource): ResourceInterface
    {
        $path = $this->buildResourceApiPath($resource, 'create');
        $body = json_encode($resource->toArray(), JSON_THROW_ON_ERROR);

        try {
            $response = $this->httpClient->post($path, $body);

            if (!$response->isSuccessful()) {
                throw new ApiException("API request failed with status {$response->getStatusCode()}: {$response->getBody()}");
            }

            $data = $response->getJson();
            return $resource::fromArray($data);
        } catch (Exception $e) {
            throw new ApiException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Build the API path for a resource operation.
     *
     * Intelligently constructs the correct API path based on the resource's
     * API version and kind, supporting all Kubernetes API groups including
     * core, extensions, and custom resource definitions.
     *
     * @param ResourceInterface $resource  The resource
     * @param string            $operation The operation (create, get, update, delete, list, watch)
     * @param string|null       $name      Resource name for specific operations
     *
     * @return string The API path
     *
     * @throws ApiException If resource information is insufficient
     */
    protected function buildResourceApiPath(ResourceInterface $resource, string $operation, ?string $name = null): string
    {
        $apiVersion = $resource->getApiVersion();
        $kind = $resource->getKind();
        $namespace = $this->extractResourceNamespace($resource);

        // Parse API version to determine if it's core or extension API
        $apiPath = $this->determineApiBasePath($apiVersion);

        // Get the resource name (plural form) for the API
        $resourceName = $this->getResourceApiName($kind);

        // Build the path based on whether the resource is namespaced
        if ($namespace && $this->isNamespacedResource($resource)) {
            $path = "{$apiPath}/namespaces/{$namespace}/{$resourceName}";
        } else {
            $path = "{$apiPath}/{$resourceName}";
        }

        // Add specific resource name for operations that require it
        if (in_array($operation, ['get', 'update', 'delete']) && $name) {
            $path .= "/{$name}";
        }

        return $path;
    }

    /**
     * Extract the resource namespace from metadata if it's a namespaced resource.
     *
     * @param ResourceInterface $resource The resource
     *
     * @return string|null The namespace or null for cluster-scoped resources
     */
    protected function extractResourceNamespace(ResourceInterface $resource): ?string
    {
        if (!$this->isNamespacedResource($resource)) {
            return null;
        }

        $metadata = $resource->getMetadata();
        return $metadata['namespace'] ?? null;
    }

    /**
     * Check if a resource is namespaced.
     *
     * @param ResourceInterface $resource The resource to check
     *
     * @return bool True if the resource is namespaced
     */
    protected function isNamespacedResource(ResourceInterface $resource): bool
    {
        // Check if the resource uses the IsNamespacedResource trait
        $reflection = new ReflectionClass($resource);
        $traits = $reflection->getTraitNames();

        return in_array('Kubernetes\\Traits\\IsNamespacedResource', $traits, true);
    }

    /**
     * Determine the base API path from the API version.
     *
     * @param string $apiVersion The API version (e.g., 'v1', 'apps/v1', 'custom.io/v1')
     *
     * @return string The base API path
     */
    protected function determineApiBasePath(string $apiVersion): string
    {
        // Core API (no group)
        if ($apiVersion === 'v1') {
            return '/api/v1';
        }

        // Extension APIs (with group)
        return "/apis/{$apiVersion}";
    }

    /**
     * Convert resource Kind to API resource name (plural form).
     *
     * Handles standard Kubernetes naming conventions and common special cases.
     *
     * @param string $kind The resource kind
     *
     * @return string The API resource name (plural)
     */
    protected function getResourceApiName(string $kind): string
    {
        // Handle special cases first
        $specialCases = [
            'Endpoints'          => 'endpoints',
            'NetworkPolicy'      => 'networkpolicies',
            'IngressClass'       => 'ingressclasses',
            'StorageClass'       => 'storageclasses',
            'PriorityClass'      => 'priorityclasses',
            'RuntimeClass'       => 'runtimeclasses',
            'VolumeSnapshot'     => 'volumesnapshots',
            'CSIDriver'          => 'csidrivers',
            'CSINode'            => 'csinodes',
            'CSIStorageCapacity' => 'csistoragecapacities',
        ];

        if (isset($specialCases[$kind])) {
            return $specialCases[$kind];
        }

        // Standard pluralization rules
        $lowerKind = strtolower($kind);

        // Words ending in 'y' -> 'ies'
        if (str_ends_with($lowerKind, 'y')) {
            return substr($lowerKind, 0, -1) . 'ies';
        }

        // Words ending in 's', 'sh', 'ch', 'x', 'z' -> 'es'
        if (str_ends_with($lowerKind, 's') ||
            str_ends_with($lowerKind, 'sh') ||
            str_ends_with($lowerKind, 'ch') ||
            str_ends_with($lowerKind, 'x') ||
            str_ends_with($lowerKind, 'z')) {
            return $lowerKind . 'es';
        }

        // Default: add 's'
        return $lowerKind . 's';
    }

    /**
     * Configure the HTTP client with authentication settings.
     */
    protected function configureHttpClient(): void
    {
        $this->httpClient
            ->setBaseUrl($this->authentication->getServerUrl())
            ->setDefaultHeaders($this->authentication->getHeaders())
            ->setVerifySsl($this->authentication->shouldVerifySsl());

        // Set CA certificate if available
        $caCert = $this->authentication->getCaCertificate();
        if ($caCert !== null) {
            // Note: Actual CA certificate configuration would depend on HTTP client implementation
            // This is a placeholder for the interface
        }
    }

    /**
     * Create a client with kubeconfig authentication.
     *
     * @param string|null              $kubeconfigPath Path to kubeconfig file
     * @param string|null              $context        Context name to use
     * @param HttpClientInterface|null $httpClient     Custom HTTP client instance
     *
     * @return static
     *
     * @throws AuthenticationException If kubeconfig authentication fails
     */
    public static function kubeconfig(
        ?string $kubeconfigPath = null,
        ?string $context = null,
        ?HttpClientInterface $httpClient = null
    ): static {
        $auth = AuthenticationFactory::kubeconfig($kubeconfigPath, $context);
        return new static($auth, $httpClient);
    }

    /**
     * Create a client with in-cluster authentication.
     *
     * @param HttpClientInterface|null $httpClient Custom HTTP client instance
     *
     * @return static
     *
     * @throws AuthenticationException If in-cluster authentication fails
     */
    public static function inCluster(?HttpClientInterface $httpClient = null): static
    {
        $auth = AuthenticationFactory::inCluster();
        return new static($auth, $httpClient);
    }

    /**
     * Create a client with token authentication.
     *
     * @param string                   $serverUrl     The Kubernetes API server URL
     * @param string                   $token         The bearer token
     * @param string|null              $caCertificate CA certificate data
     * @param bool                     $verifySsl     Whether to verify SSL certificates
     * @param HttpClientInterface|null $httpClient    Custom HTTP client instance
     *
     * @return static
     */
    public static function token(
        string $serverUrl,
        string $token,
        ?string $caCertificate = null,
        bool $verifySsl = true,
        ?HttpClientInterface $httpClient = null
    ): static {
        $auth = AuthenticationFactory::token($serverUrl, $token, $caCertificate, $verifySsl);
        return new static($auth, $httpClient);
    }

    /**
     * Read (get) a resource by name.
     *
     * @param ResourceInterface $resource The resource template with name and namespace set
     *
     * @return ResourceInterface The retrieved resource
     *
     * @throws ResourceNotFoundException If resource not found
     * @throws ApiException If API request fails
     */
    public function read(ResourceInterface $resource): ResourceInterface
    {
        $name = $this->extractResourceName($resource);
        $path = $this->buildResourceApiPath($resource, 'get', $name);

        try {
            $response = $this->httpClient->get($path);

            if (!$response->isSuccessful()) {
                if ($response->getStatusCode() === 404) {
                    throw new ResourceNotFoundException(
                        $resource->getKind(),
                        $name,
                        $this->extractResourceNamespace($resource)
                    );
                }
                throw new ApiException("API request failed with status {$response->getStatusCode()}: {$response->getBody()}");
            }

            $data = $response->getJson();
            return $resource::fromArray($data);
        } catch (ResourceNotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ApiException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Extract the resource name from metadata.
     *
     * @param ResourceInterface $resource The resource
     *
     * @return string The resource name
     *
     * @throws ApiException If name is not set
     */
    protected function extractResourceName(ResourceInterface $resource): string
    {
        $metadata = $resource->getMetadata();
        $name = $metadata['name'] ?? null;

        if (!$name) {
            throw new ApiException('Resource name is required but not set in metadata');
        }

        return $name;
    }

    /**
     * Get a resource by name and namespace.
     *
     * @param string      $name      The resource name
     * @param string|null $namespace The namespace (null for cluster-scoped resources)
     *
     * @return ResourceInterface The retrieved resource
     *
     * @throws ResourceNotFoundException If resource not found
     * @throws ApiException If API request fails
     * @deprecated Use read() method instead
     */
    public function get(string $name, ?string $namespace = null): ResourceInterface
    {
        throw new RuntimeException('get() method is deprecated. Use read() with a resource template instead.');
    }

    /**
     * Update an existing resource.
     *
     * @param ResourceInterface $resource The resource to update
     *
     * @return ResourceInterface The updated resource
     *
     * @throws ApiException If update fails
     */
    public function update(ResourceInterface $resource): ResourceInterface
    {
        $name = $this->extractResourceName($resource);
        $path = $this->buildResourceApiPath($resource, 'update', $name);
        $body = json_encode($resource->toArray(), JSON_THROW_ON_ERROR);

        try {
            $response = $this->httpClient->put($path, $body);

            if (!$response->isSuccessful()) {
                throw new ApiException("API request failed with status {$response->getStatusCode()}: {$response->getBody()}");
            }

            $data = $response->getJson();
            return $resource::fromArray($data);
        } catch (Exception $e) {
            throw new ApiException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Delete a resource.
     *
     * @param ResourceInterface $resource The resource to delete (name and namespace must be set)
     *
     * @return bool True if deletion was successful
     *
     * @throws ResourceNotFoundException If resource not found
     * @throws ApiException If deletion fails
     */
    public function delete(ResourceInterface $resource): bool
    {
        $name = $this->extractResourceName($resource);
        $path = $this->buildResourceApiPath($resource, 'delete', $name);

        try {
            $response = $this->httpClient->delete($path);

            if ($response->getStatusCode() === 404) {
                throw new ResourceNotFoundException(
                    $resource->getKind(),
                    $name,
                    $this->extractResourceNamespace($resource)
                );
            }

            if (!$response->isSuccessful()) {
                throw new ApiException("API request failed with status {$response->getStatusCode()}: {$response->getBody()}");
            }

            return true;
        } catch (ResourceNotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ApiException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * List resources of a specific type.
     *
     * @param ResourceInterface    $resourceTemplate Template resource for type and namespace
     * @param array<string, mixed> $options          Query options (labelSelector, fieldSelector, etc.)
     *
     * @return array<int, ResourceInterface> Array of resources
     *
     * @throws ApiException If API request fails
     */
    public function list(ResourceInterface $resourceTemplate, array $options = []): array
    {
        $path = $this->buildResourceApiPath($resourceTemplate, 'list');

        if (!empty($options)) {
            $path .= '?' . http_build_query($options);
        }

        try {
            $response = $this->httpClient->get($path);

            if (!$response->isSuccessful()) {
                throw new ApiException("API request failed with status {$response->getStatusCode()}: {$response->getBody()}");
            }

            $data = $response->getJson();
            $items = $data['items'] ?? [];

            $resources = [];
            foreach ($items as $item) {
                $resources[] = $resourceTemplate::fromArray($item);
            }

            return $resources;
        } catch (Exception $e) {
            throw new ApiException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Watch for changes to resources.
     *
     * @param ResourceInterface    $resourceTemplate Template resource for type and namespace
     * @param array<string, mixed> $options          Watch options (resourceVersion, timeoutSeconds, etc.)
     *
     * @return iterable<mixed> Stream of watch events
     *
     * @throws ApiException If watch request fails
     */
    public function watch(ResourceInterface $resourceTemplate, array $options = []): iterable
    {
        $path = $this->buildResourceApiPath($resourceTemplate, 'watch');
        $options['watch'] = 'true';

        $path .= '?' . http_build_query($options);

        // This is a placeholder - implementing watch would require streaming support
        throw new RuntimeException('Watch functionality not implemented yet');
    }

    /**
     * Get the underlying HTTP client.
     *
     * @return HttpClientInterface The HTTP client instance
     */
    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }

    /**
     * Set the HTTP client.
     *
     * @param HttpClientInterface $httpClient The HTTP client instance
     *
     * @return self
     */
    public function setHttpClient(HttpClientInterface $httpClient): self
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * Get the authentication instance.
     *
     * @return AuthenticationInterface The authentication instance
     */
    public function getAuthentication(): AuthenticationInterface
    {
        return $this->authentication;
    }

    // Legacy methods for backward compatibility - delegate to new CRUD methods

    /**
     * Set the authentication instance.
     *
     * @param AuthenticationInterface $authentication The authentication instance
     *
     * @return self
     */
    public function setAuthentication(AuthenticationInterface $authentication): self
    {
        $this->authentication = $authentication;
        $this->configureHttpClient();

        return $this;
    }
}
