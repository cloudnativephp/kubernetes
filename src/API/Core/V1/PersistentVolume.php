<?php

declare(strict_types=1);

namespace Kubernetes\API\Core\V1;

/**
 * Represents a Kubernetes PersistentVolume resource.
 *
 * A PersistentVolume (PV) is a piece of storage in the cluster that has been provisioned by an administrator
 * or dynamically provisioned using Storage Classes.
 *
 * @see https://kubernetes.io/docs/concepts/storage/persistent-volumes/
 */
class PersistentVolume extends AbstractAbstractResource
{
    /**
     * Get the kind of the resource.
     *
     * @return string The resource kind (PersistentVolume)
     */
    public function getKind(): string
    {
        return 'PersistentVolume';
    }

    /**
     * Get the storage capacity.
     *
     * @return array<string, string> The storage capacity
     */
    public function getCapacity(): array
    {
        return $this->spec['capacity'] ?? [];
    }

    /**
     * Set the storage capacity.
     *
     * @param array<string, string> $capacity The storage capacity
     *
     * @return self
     */
    public function setCapacity(array $capacity): self
    {
        $this->spec['capacity'] = $capacity;

        return $this;
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
     * Get the reclaim policy.
     *
     * @return string|null The reclaim policy (Retain, Recycle, Delete)
     */
    public function getReclaimPolicy(): ?string
    {
        return $this->spec['persistentVolumeReclaimPolicy'] ?? null;
    }

    /**
     * Set the reclaim policy.
     *
     * @param string $reclaimPolicy The reclaim policy
     *
     * @return self
     */
    public function setReclaimPolicy(string $reclaimPolicy): self
    {
        $this->spec['persistentVolumeReclaimPolicy'] = $reclaimPolicy;

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
     * Get the volume source.
     *
     * @return array<string, mixed>|null The volume source configuration
     */
    public function getVolumeSource(): ?array
    {
        $spec = $this->spec;
        unset($spec['capacity'], $spec['accessModes'], $spec['persistentVolumeReclaimPolicy'],
            $spec['storageClassName'], $spec['volumeMode'], $spec['claimRef'], $spec['nodeAffinity']);

        return !empty($spec) ? $spec : null;
    }

    /**
     * Set the volume source.
     *
     * @param array<string, mixed> $volumeSource The volume source configuration
     *
     * @return self
     */
    public function setVolumeSource(array $volumeSource): self
    {
        // Preserve existing spec fields
        $preservedFields = [
            'capacity', 'accessModes', 'persistentVolumeReclaimPolicy',
            'storageClassName', 'volumeMode', 'claimRef', 'nodeAffinity',
        ];

        $preserved = [];
        foreach ($preservedFields as $field) {
            if (isset($this->spec[$field])) {
                $preserved[$field] = $this->spec[$field];
            }
        }

        $this->spec = array_merge($volumeSource, $preserved);

        return $this;
    }

    /**
     * Get the claim reference.
     *
     * @return array<string, mixed>|null The claim reference
     */
    public function getClaimRef(): ?array
    {
        return $this->spec['claimRef'] ?? null;
    }

    /**
     * Set the claim reference.
     *
     * @param array<string, mixed> $claimRef The claim reference
     *
     * @return self
     */
    public function setClaimRef(array $claimRef): self
    {
        $this->spec['claimRef'] = $claimRef;

        return $this;
    }

    /**
     * Check if the persistent volume is available.
     *
     * @return bool True if the persistent volume is available
     */
    public function isAvailable(): bool
    {
        return $this->getPhase() === 'Available';
    }

    /**
     * Get the persistent volume phase.
     *
     * @return string|null The phase (Pending, Available, Bound, Released, Failed)
     */
    public function getPhase(): ?string
    {
        return $this->status['phase'] ?? null;
    }

    /**
     * Check if the persistent volume is bound.
     *
     * @return bool True if the persistent volume is bound
     */
    public function isBound(): bool
    {
        return $this->getPhase() === 'Bound';
    }
}
