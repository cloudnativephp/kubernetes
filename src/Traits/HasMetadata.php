<?php

declare(strict_types=1);

namespace Kubernetes\Traits;

/**
 * Trait for common Kubernetes resource metadata functionality.
 *
 * Provides methods for managing resource metadata including name,
 * labels, and annotations that are common to all Kubernetes resources.
 */
trait HasMetadata
{
    protected array $metadata = [];

    /**
     * Get the metadata of the resource.
     *
     * @return array<string, mixed> The resource metadata
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Set the metadata of the resource.
     *
     * @param array<string, mixed> $metadata The resource metadata
     *
     * @return self
     */
    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Get the name of the resource.
     *
     * @return string|null The resource name or null if not set
     */
    public function getName(): ?string
    {
        return $this->metadata['name'] ?? null;
    }

    /**
     * Set the name of the resource.
     *
     * @param string $name The resource name
     *
     * @return self
     */
    public function setName(string $name): self
    {
        $this->metadata['name'] = $name;

        return $this;
    }

    /**
     * Get the labels of the resource.
     *
     * @return array<string, string> The resource labels
     */
    public function getLabels(): array
    {
        return $this->metadata['labels'] ?? [];
    }

    /**
     * Set the labels of the resource.
     *
     * @param array<string, string> $labels The resource labels
     *
     * @return self
     */
    public function setLabels(array $labels): self
    {
        $this->metadata['labels'] = $labels;

        return $this;
    }

    /**
     * Get the annotations of the resource.
     *
     * @return array<string, string> The resource annotations
     */
    public function getAnnotations(): array
    {
        return $this->metadata['annotations'] ?? [];
    }

    /**
     * Set the annotations of the resource.
     *
     * @param array<string, string> $annotations The resource annotations
     *
     * @return self
     */
    public function setAnnotations(array $annotations): self
    {
        $this->metadata['annotations'] = $annotations;

        return $this;
    }
}
