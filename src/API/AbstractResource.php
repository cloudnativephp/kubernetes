<?php

declare(strict_types=1);

namespace Kubernetes\API;

use Exception;
use InvalidArgumentException;
use JsonException;
use Kubernetes\Contracts\ClientInterface;
use Kubernetes\Contracts\ResourceInterface;
use Kubernetes\Exceptions\ApiException;
use Kubernetes\Exceptions\ResourceNotFoundException;
use Kubernetes\Traits\HasMetadata;
use ReflectionClass;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Abstract base class for Kubernetes resources.
 *
 * Provides common functionality for all Kubernetes resources including
 * serialization to/from JSON, YAML, and arrays, as well as file operations.
 */
abstract class AbstractResource implements ResourceInterface
{
    use HasMetadata;

    protected static ?ClientInterface $defaultClient = null;
    protected array $spec = [];
    protected array $status = [];

    /**
     * Get the default client.
     *
     * @return ClientInterface|null The default client or null if not set
     */
    public static function getDefaultClient(): ?ClientInterface
    {
        return self::$defaultClient;
    }

    /**
     * Set the default client for all resources.
     *
     * @param ClientInterface $client The default Kubernetes client
     *
     * @return void
     */
    public static function setDefaultClient(ClientInterface $client): void
    {
        self::$defaultClient = $client;
    }

    /**
     * Create a resource from a file.
     *
     * Automatically detects file format based on extension (.json, .yaml, .yml).
     * Falls back to JSON parsing if extension is not recognized.
     *
     * @param string $filePath Path to the file
     *
     * @return static The created resource instance
     *
     * @throws InvalidArgumentException If file doesn't exist or can't be read
     * @throws JsonException If JSON parsing fails
     * @throws ParseException If YAML parsing fails
     */
    public static function fromFile(string $filePath): static
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("File not found: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new InvalidArgumentException("Unable to read file: {$filePath}");
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return match ($extension) {
            'yaml', 'yml' => static::fromYaml($content),
            'json'  => static::fromJson($content),
            default => static::fromJson($content), // Default to JSON
        };
    }

    /**
     * Create a resource from YAML string.
     *
     * @param string $yaml YAML string representation
     *
     * @return static The created resource instance
     *
     * @throws ParseException If YAML parsing fails
     */
    public static function fromYaml(string $yaml): static
    {
        $data = Yaml::parse($yaml);

        if (!is_array($data)) {
            throw new InvalidArgumentException('Invalid YAML: expected object, got ' . gettype($data));
        }

        return static::fromArray($data);
    }

    /**
     * Create a resource from an array representation.
     *
     * @param array<string, mixed> $data The array data
     *
     * @return static
     */
    public static function fromArray(array $data): static
    {
        /** @var static $resource */
        $resource = new static();

        $resource->setMetadata($data['metadata'] ?? []);

        if (isset($data['spec'])) {
            $resource->setSpec($data['spec']);
        }

        if (isset($data['status'])) {
            $resource->setStatus($data['status']);
        }

        return $resource;
    }

    /**
     * Create a resource from JSON string.
     *
     * @param string $json JSON string representation
     *
     * @return static The created resource instance
     *
     * @throws JsonException If JSON decoding fails
     */
    public static function fromJson(string $json): static
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($data)) {
            throw new JsonException('Invalid JSON: expected object, got ' . gettype($data));
        }

        return static::fromArray($data);
    }

    /**
     * Get multiple resources by names from the Kubernetes cluster.
     *
     * For namespaced resources, optionally specify a namespace parameter.
     * For cluster-scoped resources, the namespace parameter is ignored.
     *
     * @param array<string>        $names     Array of resource names
     * @param string|null          $namespace The namespace (only used for namespaced resources)
     * @param ClientInterface|null $client    Optional client to use (falls back to default client)
     *
     * @return array<string, static> Array of resources keyed by name
     *
     * @throws ApiException If no client is available or retrieval fails
     * @throws InvalidArgumentException If names array is empty
     */
    public static function getMany(array $names, ?string $namespace = null, ?ClientInterface $client = null): array
    {
        if (empty($names)) {
            throw new InvalidArgumentException('Names array cannot be empty');
        }

        $client ??= self::$defaultClient;

        if ($client === null) {
            throw new InvalidArgumentException(
                'No Kubernetes client available. Set a default client with ' .
                static::class . '::setDefaultClient() or pass a client to getMany()'
            );
        }

        $resources = [];

        foreach ($names as $name) {
            try {
                $resources[$name] = static::get($name, $namespace, $client);
            } catch (ResourceNotFoundException) {
                // Skip resources that don't exist
                continue;
            }
        }

        return $resources;
    }

    /**
     * Get a resource by name from the Kubernetes cluster.
     *
     * For namespaced resources, optionally specify a namespace parameter.
     * For cluster-scoped resources, the namespace parameter is ignored.
     *
     * @param string               $name      The resource name
     * @param string|null          $namespace The namespace (only used for namespaced resources)
     * @param ClientInterface|null $client    Optional client to use (falls back to default client)
     *
     * @return static The retrieved resource
     *
     * @throws ApiException If no client is available or retrieval fails
     * @throws ResourceNotFoundException If resource doesn't exist
     * @throws InvalidArgumentException If name is empty
     */
    public static function get(string $name, ?string $namespace = null, ?ClientInterface $client = null): static
    {
        if (empty($name)) {
            throw new InvalidArgumentException('Resource name cannot be empty');
        }

        $client ??= self::$defaultClient;

        if ($client === null) {
            throw new InvalidArgumentException(
                'No Kubernetes client available. Set a default client with ' .
                static::class . '::setDefaultClient() or pass a client to get()'
            );
        }

        // Create a template resource for the query
        $template = new static();
        $template->setName($name);

        // Set namespace only if this is a namespaced resource and namespace is provided
        if (static::isNamespacedResourceClass() && $namespace !== null) {
            $template->setNamespace($namespace);
        }

        /** @var static $result */
        $result = $client->read($template);
        return $result;
    }

    /**
     * Check if the current resource class uses the IsNamespacedResource trait.
     *
     * @return bool True if the resource class is namespaced
     */
    protected static function isNamespacedResourceClass(): bool
    {
        $reflection = new ReflectionClass(static::class);
        $traits = $reflection->getTraitNames();

        return in_array('Kubernetes\\Traits\\IsNamespacedResource', $traits, true);
    }

    /**
     * Find a single resource by label selector.
     *
     * @param array<string, string> $labels    Label selector as key-value pairs
     * @param string|null           $namespace The namespace to search in (only used for namespaced resources)
     * @param ClientInterface|null  $client    Optional client to use (falls back to default client)
     *
     * @return static|null The first matching resource or null if none found
     *
     * @throws ApiException If search fails
     */
    public static function findOneByLabels(array $labels, ?string $namespace = null, ?ClientInterface $client = null): ?static
    {
        $resources = static::findByLabels($labels, $namespace, $client);
        return empty($resources) ? null : $resources[0];
    }

    /**
     * Find resources by label selector.
     *
     * @param array<string, string> $labels    Label selector as key-value pairs
     * @param string|null           $namespace The namespace to search in (only used for namespaced resources)
     * @param ClientInterface|null  $client    Optional client to use (falls back to default client)
     *
     * @return array<int, static> Array of matching resources
     *
     * @throws ApiException If no client is available or search fails
     */
    public static function findByLabels(array $labels, ?string $namespace = null, ?ClientInterface $client = null): array
    {
        $labelSelector = [];
        foreach ($labels as $key => $value) {
            $labelSelector[] = "{$key}={$value}";
        }

        return static::all($namespace, ['labelSelector' => implode(',', $labelSelector)], $client);
    }

    /**
     * Get all resources of this type from the Kubernetes cluster.
     *
     * For namespaced resources, optionally specify a namespace to filter results.
     * For cluster-scoped resources, the namespace parameter is ignored.
     *
     * @param string|null          $namespace The namespace to filter by (only used for namespaced resources)
     * @param array<string, mixed> $options   Query options (labelSelector, fieldSelector, etc.)
     * @param ClientInterface|null $client    Optional client to use (falls back to default client)
     *
     * @return array<int, static> Array of resources
     *
     * @throws ApiException If no client is available or listing fails
     */
    public static function all(?string $namespace = null, array $options = [], ?ClientInterface $client = null): array
    {
        $client ??= self::$defaultClient;

        if ($client === null) {
            throw new InvalidArgumentException(
                'No Kubernetes client available. Set a default client with ' .
                static::class . '::setDefaultClient() or pass a client to all()'
            );
        }

        // Create a template resource for the query
        $template = new static();

        // Set namespace only if this is a namespaced resource and namespace is provided
        if (static::isNamespacedResourceClass() && $namespace !== null) {
            $template->setNamespace($namespace);
        }

        /** @var array<int, static> $results */
        $results = $client->list($template, $options);
        return $results;
    }

    /**
     * Save the resource to a file.
     *
     * Automatically determines format based on file extension (.json, .yaml, .yml).
     * Falls back to JSON format if extension is not recognized.
     *
     * @param string $filePath   Path where to save the file
     * @param int    $jsonFlags  JSON encode flags (used for JSON format)
     * @param int    $yamlInline YAML inline depth (used for YAML format)
     * @param int    $yamlIndent YAML indentation (used for YAML format)
     *
     * @return bool True on success, false on failure
     *
     * @throws JsonException If JSON encoding fails
     */
    public function toFile(string $filePath, int $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT, int $yamlInline = 4, int $yamlIndent = 2): bool
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $content = match ($extension) {
            'yaml', 'yml' => $this->toYaml($yamlInline, $yamlIndent),
            'json'  => $this->toJson($jsonFlags),
            default => $this->toJson($jsonFlags), // Default to JSON
        };

        return file_put_contents($filePath, $content) !== false;
    }

    /**
     * Convert the resource to YAML.
     *
     * @param int           $inline Depth level for inline formatting
     * @param int           $indent Number of spaces for indentation
     * @param int<0, 64721> $flags  YAML dump flags
     *
     * @return string YAML representation of the resource
     */
    public function toYaml(int $inline = 4, int $indent = 2, int $flags = 0): string
    {
        return Yaml::dump($this->toArray(), $inline, $indent, $flags);
    }

    /**
     * Convert the resource to an array.
     *
     * @return array<string, mixed> Array representation of the resource
     */
    public function toArray(): array
    {
        $data = [
            'apiVersion' => $this->getApiVersion(),
            'kind'       => $this->getKind(),
            'metadata'   => $this->metadata,
        ];

        if (!empty($this->spec)) {
            $data['spec'] = $this->spec;
        }

        if (!empty($this->status)) {
            $data['status'] = $this->status;
        }

        return $data;
    }

    /**
     * Convert the resource to JSON.
     *
     * @param int $flags JSON encode flags
     *
     * @return string JSON representation of the resource
     *
     * @throws JsonException If JSON encoding fails
     */
    public function toJson(int $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->toArray(), $flags | JSON_THROW_ON_ERROR);
    }

    /**
     * Save the resource to the Kubernetes cluster.
     *
     * Intelligently determines whether to create or update the resource:
     * - If the resource has no resourceVersion in metadata, it will be created
     * - If the resource has a resourceVersion, it will be updated
     * - If creation fails due to resource already existing, attempts an update
     *
     * @param ClientInterface|null $client Optional client to use (falls back to default client)
     *
     * @return static The saved resource with updated metadata from the server
     *
     * @throws ApiException If no client is available or save operation fails
     * @throws InvalidArgumentException If resource name is not set
     */
    public function save(?ClientInterface $client = null): static
    {
        $client ??= self::$defaultClient;

        if ($client === null) {
            throw new InvalidArgumentException(
                'No Kubernetes client available. Set a default client with ' .
                static::class . '::setDefaultClient() or pass a client to save()'
            );
        }

        // Validate required metadata
        if (empty($this->getName())) {
            throw new InvalidArgumentException(
                'Resource name must be set before saving. Use setName() to set the resource name.'
            );
        }

        // Determine save strategy based on resource state
        if ($this->isNewResource()) {
            return $this->performCreate($client);
        } else {
            return $this->performUpdate($client);
        }
    }

    /**
     * Check if this is a new resource that hasn't been saved to the cluster.
     *
     * A resource is considered new if it doesn't have a resourceVersion in its metadata,
     * which is assigned by the Kubernetes API server when a resource is created.
     *
     * @return bool True if this is a new resource
     */
    public function isNewResource(): bool
    {
        $metadata = $this->getMetadata();
        return empty($metadata['resourceVersion']);
    }

    /**
     * Perform the create operation.
     *
     * @param ClientInterface $client The Kubernetes client
     *
     * @return static The created resource
     *
     * @throws ApiException If creation fails
     */
    protected function performCreate(ClientInterface $client): static
    {
        try {
            $createdResource = $client->create($this);

            // Update current instance with server response
            $this->setMetadata($createdResource->getMetadata());
            $this->setStatus($createdResource->getStatus());

            return $this;
        } catch (Exception $e) {
            // If creation fails because resource already exists, try update
            if (str_contains($e->getMessage(), 'already exists') ||
                str_contains($e->getMessage(), '409') ||
                str_contains($e->getMessage(), 'Conflict')) {
                return $this->performUpdate($client);
            }

            throw $e;
        }
    }

    /**
     * Get the status of the resource.
     *
     * @return array<string, mixed> The resource status
     */
    public function getStatus(): array
    {
        return $this->status;
    }

    /**
     * Set the status of the resource.
     *
     * @param array<string, mixed> $status The resource status
     *
     * @return self
     */
    public function setStatus(array $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Perform the update operation.
     *
     * @param ClientInterface $client The Kubernetes client
     *
     * @return static The updated resource
     *
     * @throws ApiException If update fails
     */
    protected function performUpdate(ClientInterface $client): static
    {
        $updatedResource = $client->update($this);

        // Update current instance with server response
        $this->setMetadata($updatedResource->getMetadata());
        $this->setStatus($updatedResource->getStatus());

        return $this;
    }

    /**
     * Refresh the resource from the Kubernetes cluster.
     *
     * Fetches the latest version of the resource from the cluster and updates
     * the current instance with the server state.
     *
     * @param ClientInterface|null $client Optional client to use (falls back to default client)
     *
     * @return static The refreshed resource
     *
     * @throws ApiException If no client is available or refresh fails
     * @throws ResourceNotFoundException If resource doesn't exist
     * @throws InvalidArgumentException If resource name is not set
     */
    public function refresh(?ClientInterface $client = null): static
    {
        $client ??= self::$defaultClient;

        if ($client === null) {
            throw new InvalidArgumentException(
                'No Kubernetes client available. Set a default client with ' .
                static::class . '::setDefaultClient() or pass a client to refresh()'
            );
        }

        if (empty($this->getName())) {
            throw new InvalidArgumentException(
                'Resource name must be set before refreshing. Use setName() to set the resource name.'
            );
        }

        // Create a template for reading
        $template = clone $this;
        $updatedResource = $client->read($template);

        // Update current instance with fresh data
        $this->setMetadata($updatedResource->getMetadata());
        $this->setSpec($updatedResource->getSpec());
        $this->setStatus($updatedResource->getStatus());

        return $this;
    }

    /**
     * Get the spec of the resource.
     *
     * @return array<string, mixed> The resource specification
     */
    public function getSpec(): array
    {
        return $this->spec;
    }

    /**
     * Set the spec of the resource.
     *
     * @param array<string, mixed> $spec The resource specification
     *
     * @return self
     */
    public function setSpec(array $spec): self
    {
        $this->spec = $spec;

        return $this;
    }

    /**
     * Delete the resource from the Kubernetes cluster.
     *
     * @param ClientInterface|null $client Optional client to use (falls back to default client)
     *
     * @return bool True if deletion was successful
     *
     * @throws ApiException If no client is available or deletion fails
     * @throws ResourceNotFoundException If resource doesn't exist
     * @throws InvalidArgumentException If resource name is not set
     */
    public function delete(?ClientInterface $client = null): bool
    {
        $client ??= self::$defaultClient;

        if ($client === null) {
            throw new InvalidArgumentException(
                'No Kubernetes client available. Set a default client with ' .
                static::class . '::setDefaultClient() or pass a client to delete()'
            );
        }

        if (empty($this->getName())) {
            throw new InvalidArgumentException(
                'Resource name must be set before deleting. Use setName() to set the resource name.'
            );
        }

        return $client->delete($this);
    }

    /**
     * Check if this resource exists in the cluster.
     *
     * Attempts to read the resource from the cluster to determine if it exists.
     *
     * @param ClientInterface|null $client Optional client to use (falls back to default client)
     *
     * @return bool True if the resource exists in the cluster
     *
     * @throws ApiException If no client is available or check fails
     * @throws InvalidArgumentException If resource name is not set
     */
    public function exists(?ClientInterface $client = null): bool
    {
        try {
            $template = clone $this;
            $client ??= self::$defaultClient;

            if ($client === null) {
                throw new InvalidArgumentException(
                    'No Kubernetes client available. Set a default client with ' .
                    static::class . '::setDefaultClient() or pass a client to exists()'
                );
            }

            $client->read($template);
            return true;
        } catch (ResourceNotFoundException) {
            return false;
        }
    }
}
