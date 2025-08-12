<?php

declare(strict_types=1);

namespace Kubernetes\API\Core\V1;

/**
 * Represents a Kubernetes Namespace resource.
 *
 * Namespaces provide a mechanism for isolating groups of resources within a single cluster.
 * Names of resources need to be unique within a namespace, but not across namespaces.
 *
 * @see https://kubernetes.io/docs/concepts/overview/working-with-objects/namespaces/
 */
class KubernetesNamespace extends AbstractAbstractResource
{
    /**
     * Get the kind of the resource.
     *
     * @return string The resource kind 'Namespace'
     */
    public function getKind(): string
    {
        return 'Namespace';
    }

    /**
     * Check if the namespace is active.
     *
     * @return bool True if the namespace is active
     */
    public function isActive(): bool
    {
        return $this->getPhase() === 'Active';
    }

    /**
     * Get the namespace phase.
     *
     * @return string|null The namespace phase (Active, Terminating)
     */
    public function getPhase(): ?string
    {
        return $this->status['phase'] ?? null;
    }

    /**
     * Check if the namespace is terminating.
     *
     * @return bool True if the namespace is terminating
     */
    public function isTerminating(): bool
    {
        return $this->getPhase() === 'Terminating';
    }
}
