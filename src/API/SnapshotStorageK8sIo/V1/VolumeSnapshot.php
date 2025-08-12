<?php

declare(strict_types=1);

namespace Kubernetes\API\SnapshotStorageK8sIo\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * VolumeSnapshot represents a user's request for either creating a point-in-time snapshot of a persistent volume, or binding to a pre-existing snapshot.
 *
 * @link https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.30/#volumesnapshot-v1-snapshot-storage-k8s-io
 */
class VolumeSnapshot extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the resource kind.
     *
     * @return string
     */
    public function getKind(): string
    {
        return 'VolumeSnapshot';
    }

    /**
     * Set the volume snapshot class name.
     *
     * @param string $className Name of the VolumeSnapshotClass
     *
     * @return self
     */
    public function setVolumeSnapshotClassName(string $className): self
    {
        $this->spec['volumeSnapshotClassName'] = $className;
        return $this;
    }

    /**
     * Get the volume snapshot class name.
     *
     * @return string|null
     */
    public function getVolumeSnapshotClassName(): ?string
    {
        return $this->spec['volumeSnapshotClassName'] ?? null;
    }

    /**
     * Set the source for this snapshot.
     *
     * @param array<string, mixed> $source Snapshot source specification
     *
     * @return self
     */
    public function setSource(array $source): self
    {
        $this->spec['source'] = $source;
        return $this;
    }

    /**
     * Get the snapshot source.
     *
     * @return array<string, mixed>|null
     */
    public function getSource(): ?array
    {
        return $this->spec['source'] ?? null;
    }

    /**
     * Set persistent volume claim as source.
     *
     * @param string $pvcName Name of the PersistentVolumeClaim
     *
     * @return self
     */
    public function setPersistentVolumeClaimSource(string $pvcName): self
    {
        $this->spec['source']['persistentVolumeClaimName'] = $pvcName;
        return $this;
    }

    /**
     * Set volume snapshot content as source.
     *
     * @param string $contentName Name of the VolumeSnapshotContent
     *
     * @return self
     */
    public function setVolumeSnapshotContentSource(string $contentName): self
    {
        $this->spec['source']['volumeSnapshotContentName'] = $contentName;
        return $this;
    }

    /**
     * Get the snapshot status.
     *
     * @return array<string, mixed>
     */
    public function getSnapshotStatus(): array
    {
        return $this->status ?? [];
    }

    /**
     * Check if snapshot is ready to use.
     *
     * @return bool
     */
    public function isReadyToUse(): bool
    {
        return $this->status['readyToUse'] ?? false;
    }

    /**
     * Get the bound volume snapshot content name.
     *
     * @return string|null
     */
    public function getBoundVolumeSnapshotContentName(): ?string
    {
        return $this->status['boundVolumeSnapshotContentName'] ?? null;
    }

    /**
     * Get the creation time.
     *
     * @return string|null
     */
    public function getCreationTime(): ?string
    {
        return $this->status['creationTime'] ?? null;
    }

    /**
     * Get the restore size.
     *
     * @return string|null
     */
    public function getRestoreSize(): ?string
    {
        return $this->status['restoreSize'] ?? null;
    }

    /**
     * Get snapshot error.
     *
     * @return array<string, mixed>|null
     */
    public function getError(): ?array
    {
        return $this->status['error'] ?? null;
    }
}
