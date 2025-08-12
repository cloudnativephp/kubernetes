<?php

declare(strict_types=1);

namespace Kubernetes\Exceptions;

use Throwable;

/**
 * Exception thrown when a Kubernetes resource is not found.
 *
 * This exception is thrown when attempting to retrieve, update, or delete
 * a resource that doesn't exist in the Kubernetes cluster.
 */
class ResourceNotFoundException extends KubernetesException
{
    protected string $resourceType;
    protected string $resourceName;
    protected ?string $namespace;

    /**
     * Create a new resource not found exception.
     *
     * @param string          $resourceType The type of resource (e.g., 'Pod', 'Service')
     * @param string          $resourceName The name of the resource
     * @param string|null     $namespace    The namespace (null for cluster-scoped resources)
     * @param Throwable|null $previous     The previous exception
     */
    public function __construct(string $resourceType, string $resourceName, ?string $namespace = null, ?Throwable $previous = null)
    {
        $this->resourceType = $resourceType;
        $this->resourceName = $resourceName;
        $this->namespace = $namespace;

        $message = $namespace
            ? "Resource {$resourceType} '{$resourceName}' not found in namespace '{$namespace}'"
            : "Resource {$resourceType} '{$resourceName}' not found";

        parent::__construct($message, 404, $previous);
    }

    /**
     * Get the resource type.
     *
     * @return string The resource type
     */
    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    /**
     * Get the resource name.
     *
     * @return string The resource name
     */
    public function getResourceName(): string
    {
        return $this->resourceName;
    }

    /**
     * Get the namespace.
     *
     * @return string|null The namespace or null for cluster-scoped resources
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }
}
