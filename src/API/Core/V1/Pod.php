<?php

declare(strict_types=1);

namespace Kubernetes\API\Core\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * Represents a Kubernetes Pod resource.
 *
 * Pods are the smallest deployable units in Kubernetes that can hold
 * one or more containers with shared storage and network resources.
 */
class Pod extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of the resource.
     *
     * @return string The resource kind 'Pod'
     */
    public function getKind(): string
    {
        return 'Pod';
    }

    /**
     * Get the containers in the pod spec.
     *
     * @return array<int, array<string, mixed>> Array of container specifications
     */
    public function getContainers(): array
    {
        return $this->spec['containers'] ?? [];
    }

    /**
     * Set the containers in the pod spec.
     *
     * @param array<int, array<string, mixed>> $containers Array of container specifications
     *
     * @return self
     */
    public function setContainers(array $containers): self
    {
        $this->spec['containers'] = $containers;

        return $this;
    }

    /**
     * Add a container to the pod spec.
     *
     * @param array<string, mixed> $container Container specification
     *
     * @return self
     */
    public function addContainer(array $container): self
    {
        if (!isset($this->spec['containers'])) {
            $this->spec['containers'] = [];
        }

        $this->spec['containers'][] = $container;

        return $this;
    }

    /**
     * Get the restart policy for the pod.
     *
     * @return string The restart policy (Always, OnFailure, Never)
     */
    public function getRestartPolicy(): string
    {
        return $this->spec['restartPolicy'] ?? '';
    }

    /**
     * Set the restart policy for the pod.
     *
     * @param string $policy The restart policy (Always, OnFailure, Never)
     *
     * @return self
     */
    public function setRestartPolicy(string $policy): self
    {
        $this->spec['restartPolicy'] = $policy;

        return $this;
    }

    /**
     * Get the pod phase from status.
     *
     * @return string|null The pod phase (Pending, Running, Succeeded, Failed, Unknown)
     */
    public function getPhase(): ?string
    {
        return $this->status['phase'] ?? null;
    }

    /**
     * Get the pod conditions from status.
     *
     * @return array<int, array<string, mixed>> Array of pod conditions
     */
    public function getConditions(): array
    {
        return $this->status['conditions'] ?? [];
    }
}
