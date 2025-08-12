<?php

declare(strict_types=1);

namespace Kubernetes\Contracts;

use Kubernetes\Exceptions\ApiException;
use Kubernetes\Exceptions\ResourceNotFoundException;

/**
 * Interface for Kubernetes API clients.
 *
 * Defines the contract for clients that interact with the Kubernetes API,
 * providing CRUD operations and watching capabilities for resources.
 * Uses resource objects as templates for type-safe operations.
 */
interface ClientInterface
{
    /**
     * Create a new resource.
     *
     * @param ResourceInterface $resource The resource to create
     *
     * @return ResourceInterface The created resource with server-assigned fields
     *
     * @throws ApiException If creation fails
     */
    public function create(ResourceInterface $resource): ResourceInterface;

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
    public function read(ResourceInterface $resource): ResourceInterface;

    /**
     * Update an existing resource.
     *
     * @param ResourceInterface $resource The resource to update
     *
     * @return ResourceInterface The updated resource
     *
     * @throws ApiException If update fails
     */
    public function update(ResourceInterface $resource): ResourceInterface;

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
    public function delete(ResourceInterface $resource): bool;

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
    public function list(ResourceInterface $resourceTemplate, array $options = []): array;

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
    public function watch(ResourceInterface $resourceTemplate, array $options = []): iterable;
}
