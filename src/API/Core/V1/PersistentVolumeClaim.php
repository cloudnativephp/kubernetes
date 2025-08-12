<?php

declare(strict_types=1);

namespace Kubernetes\API\Core\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * Represents a Kubernetes PersistentVolumeClaim resource.
 *
 * A PersistentVolumeClaim (PVC) is a request for storage by a user. It is similar to a Pod.
 * Pods consume node resources and PVCs consume PV resources.
 *
 * @see https://kubernetes.io/docs/concepts/storage/persistent-volumes/
 */
class PersistentVolumeClaim extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of the resource.
     *
     * @return string The resource kind (PersistentVolumeClaim)
     */
    public function getKind(): string
    {
        return 'PersistentVolumeClaim';
    }

    /**
     * Get the access modes.
     *
     * @return array<int, string> The access modes (ReadWriteOnce, ReadOnlyMany, ReadWriteMany)
     */
    public function getAccessModes(): array
    {
        return $this->spec['accessModes'] ?? [];
    }

    /**
     * Set the access modes.
     *
     * @param array<int, string> $accessModes The access modes
     *
     * @return self
     */
    public function setAccessModes(array $accessModes): self
    {
        $this->spec['accessModes'] = $accessModes;

        return $this;
    }

    /**
     * Get the resource requests.
     *
     * @return array<string, string> The resource requests
     */
    public function getRequests(): array
    {
        return $this->spec['resources']['requests'] ?? [];
    }

    /**
     * Set the resource requests.
     *
     * @param array<string, string> $requests The resource requests
     *
     * @return self
     */
    public function setRequests(array $requests): self
    {
        if (!isset($this->spec['resources'])) {
            $this->spec['resources'] = [];
        }

        $this->spec['resources']['requests'] = $requests;

        return $this;
    }

    /**
     * Get the resource limits.
     *
     * @return array<string, string> The resource limits
     */
    public function getLimits(): array
    {
        return $this->spec['resources']['limits'] ?? [];
    }

    /**
     * Set the resource limits.
     *
     * @param array<string, string> $limits The resource limits
     *
     * @return self
     */
    public function setLimits(array $limits): self
    {
        if (!isset($this->spec['resources'])) {
            $this->spec['resources'] = [];
        }

        $this->spec['resources']['limits'] = $limits;

        return $this;
    }

    /**
     * Get the storage class name.
     *
     * @return string|null The storage class name
     */
    public function getStorageClassName(): ?string
    {
        return $this->spec['storageClassName'] ?? null;
    }

    /**
     * Set the storage class name.
     *
     * @param string $storageClassName The storage class name
     *
     * @return self
     */
    public function setStorageClassName(string $storageClassName): self
    {
        $this->spec['storageClassName'] = $storageClassName;

        return $this;
    }

    /**
     * Get the volume mode.
     *
     * @return string|null The volume mode (Filesystem, Block)
     */
    public function getVolumeMode(): ?string
    {
        return $this->spec['volumeMode'] ?? null;
    }

    /**
     * Set the volume mode.
     *
     * @param string $volumeMode The volume mode
     *
     * @return self
     */
    public function setVolumeMode(string $volumeMode): self
    {
        $this->spec['volumeMode'] = $volumeMode;

        return $this;
    }

    /**
     * Get the volume name.
     *
     * @return string|null The volume name
     */
    public function getVolumeName(): ?string
    {
        return $this->spec['volumeName'] ?? null;
    }

    /**
     * Set the volume name.
     *
     * @param string $volumeName The volume name
     *
     * @return self
     */
    public function setVolumeName(string $volumeName): self
    {
        $this->spec['volumeName'] = $volumeName;

        return $this;
    }

    /**
     * Get the selector.
     *
     * @return array<string, mixed>|null The selector
     */
    public function getSelector(): ?array
    {
        return $this->spec['selector'] ?? null;
    }

    /**
     * Set the selector.
     *
     * @param array<string, mixed> $selector The selector
     *
     * @return self
     */
    public function setSelector(array $selector): self
    {
        $this->spec['selector'] = $selector;

        return $this;
    }

    /**
     * Check if the persistent volume claim is pending.
     *
     * @return bool True if the persistent volume claim is pending
     */
    public function isPending(): bool
    {
        return $this->getPhase() === 'Pending';
    }

    /**
     * Get the persistent volume claim phase.
     *
     * @return string|null The phase (Pending, Bound, Lost)
     */
    public function getPhase(): ?string
    {
        return $this->status['phase'] ?? null;
    }

    /**
     * Check if the persistent volume claim is bound.
     *
     * @return bool True if the persistent volume claim is bound
     */
    public function isBound(): bool
    {
        return $this->getPhase() === 'Bound';
    }

    /**
     * Get the actual capacity.
     *
     * @return array<string, string> The actual capacity
     */
    public function getCapacity(): array
    {
        return $this->status['capacity'] ?? [];
    }

    /**
     * Get the allocated resources.
     *
     * @return array<string, string> The allocated resources
     */
    public function getAllocatedResources(): array
    {
        return $this->status['allocatedResources'] ?? [];
    }
}
