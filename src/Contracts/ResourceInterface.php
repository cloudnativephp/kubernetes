<?php

declare(strict_types=1);

namespace Kubernetes\Contracts;

/**
 * Interface for Kubernetes resources.
 *
 * Defines the contract that all Kubernetes resources must implement,
 * providing essential methods for resource identification and metadata management.
 */
interface ResourceInterface
{
    /**
     * Create a resource from an array.
     *
     * @param array<string, mixed> $data The array data
     *
     * @return static
     */
    public static function fromArray(array $data): static;

    /**
     * Get the API version of the resource.
     *
     * @return string The API version (e.g., 'v1', 'apps/v1', 'batch/v1')
     */
    public function getApiVersion(): string;

    /**
     * Get the kind of the resource.
     *
     * @return string The resource kind (e.g., 'Pod', 'Service', 'Deployment')
     */
    public function getKind(): string;

    /**
     * Get the metadata of the resource.
     *
     * @return array<string, mixed> The resource metadata
     */
    public function getMetadata(): array;

    /**
     * Set the metadata of the resource.
     *
     * @param array<string, mixed> $metadata The resource metadata
     *
     * @return self
     */
    public function setMetadata(array $metadata): self;

    /**
     * Get the spec of the resource.
     *
     * @return array<string, mixed> The resource specification
     */
    public function getSpec(): array;

    /**
     * Set the spec of the resource.
     *
     * @param array<string, mixed> $spec The resource specification
     *
     * @return self
     */
    public function setSpec(array $spec): self;

    /**
     * Get the status of the resource.
     *
     * @return array<string, mixed> The resource status
     */
    public function getStatus(): array;

    /**
     * Set the status of the resource.
     *
     * @param array<string, mixed> $status The resource status
     *
     * @return self
     */
    public function setStatus(array $status): self;

    /**
     * Convert the resource to an array.
     *
     * @return array<string, mixed> Array representation of the resource
     */
    public function toArray(): array;
}
