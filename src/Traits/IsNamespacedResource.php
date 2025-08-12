<?php

declare(strict_types=1);

namespace Kubernetes\Traits;

/**
 * Trait for Kubernetes resources that exist within a namespace.
 *
 * This trait provides namespace-related functionality for resources that are scoped
 * to a namespace. Non-namespaced resources (like Node, Namespace, etc.) should not use this trait.
 */
trait IsNamespacedResource
{
    /**
     * Get the namespace of the resource.
     *
     * @return string|null The namespace name, or null if not set
     */
    public function getNamespace(): ?string
    {
        return $this->metadata['namespace'] ?? null;
    }

    /**
     * Set the namespace of the resource.
     *
     * @param string $namespace The namespace name
     *
     * @return self
     */
    public function setNamespace(string $namespace): self
    {
        $this->metadata['namespace'] = $namespace;

        return $this;
    }
}
