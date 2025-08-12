<?php

declare(strict_types=1);

namespace Kubernetes\API\StorageK8sIo\V1;

/**
 * VolumeAttachment captures the intent to attach or detach the specified volume to/from the specified node.
 *
 * @link https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.30/#volumeattachment-v1-storage-k8s-io
 */
class VolumeAttachment extends AbstractAbstractResource
{
    /**
     * Get the resource kind.
     *
     * @return string
     */
    public function getKind(): string
    {
        return 'VolumeAttachment';
    }

    /**
     * Set the attacher name.
     *
     * @param string $attacher Name of the CSI driver
     *
     * @return self
     */
    public function setAttacher(string $attacher): self
    {
        $this->spec['attacher'] = $attacher;
        return $this;
    }

    /**
     * Get the attacher name.
     *
     * @return string|null
     */
    public function getAttacher(): ?string
    {
        return $this->spec['attacher'] ?? null;
    }

    /**
     * Set the node name where the volume should be attached.
     *
     * @param string $nodeName Name of the node
     *
     * @return self
     */
    public function setNodeName(string $nodeName): self
    {
        $this->spec['nodeName'] = $nodeName;
        return $this;
    }

    /**
     * Get the node name.
     *
     * @return string|null
     */
    public function getNodeName(): ?string
    {
        return $this->spec['nodeName'] ?? null;
    }

    /**
     * Set the volume source.
     *
     * @param array<string, mixed> $source Volume source specification
     *
     * @return self
     */
    public function setSource(array $source): self
    {
        $this->spec['source'] = $source;
        return $this;
    }

    /**
     * Get the volume source.
     *
     * @return array<string, mixed>|null
     */
    public function getSource(): ?array
    {
        return $this->spec['source'] ?? null;
    }

    /**
     * Set CSI volume source.
     *
     * @param string                    $driver       CSI driver name
     * @param string                    $volumeHandle Volume handle
     * @param array<string, mixed>|null $attributes   Volume attributes
     *
     * @return self
     */
    public function setCsiSource(string $driver, string $volumeHandle, ?array $attributes = null): self
    {
        $csiSource = [
            'driver'       => $driver,
            'volumeHandle' => $volumeHandle,
        ];

        if ($attributes !== null) {
            $csiSource['volumeAttributes'] = $attributes;
        }

        $this->spec['source']['csi'] = $csiSource;
        return $this;
    }

    /**
     * Set persistent volume source.
     *
     * @param string $persistentVolumeName Name of the PersistentVolume
     *
     * @return self
     */
    public function setPersistentVolumeSource(string $persistentVolumeName): self
    {
        $this->spec['source']['persistentVolumeName'] = $persistentVolumeName;
        return $this;
    }

    /**
     * Get attachment status.
     *
     * @return array<string, mixed>
     */
    public function getAttachmentStatus(): array
    {
        return $this->status ?? [];
    }

    /**
     * Check if volume is attached.
     *
     * @return bool
     */
    public function isAttached(): bool
    {
        return $this->status['attached'] ?? false;
    }

    /**
     * Get attachment metadata.
     *
     * @return array<string, string>
     */
    public function getAttachmentMetadata(): array
    {
        return $this->status['attachmentMetadata'] ?? [];
    }

    /**
     * Get attach error.
     *
     * @return array<string, mixed>|null
     */
    public function getAttachError(): ?array
    {
        return $this->status['attachError'] ?? null;
    }

    /**
     * Get detach error.
     *
     * @return array<string, mixed>|null
     */
    public function getDetachError(): ?array
    {
        return $this->status['detachError'] ?? null;
    }
}
