<?php

declare(strict_types=1);

namespace Kubernetes\API\StorageK8sIo\V1;

/**
 * CSIDriver captures information about a Container Storage Interface (CSI) volume driver.
 *
 * @link https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.30/#csidriver-v1-storage-k8s-io
 */
class CSIDriver extends AbstractAbstractResource
{
    /**
     * Get the resource kind.
     *
     * @return string
     */
    public function getKind(): string
    {
        return 'CSIDriver';
    }

    /**
     * Set whether the driver supports volume attachment.
     *
     * @param bool $attachRequired Whether attachment is required
     *
     * @return self
     */
    public function setAttachRequired(bool $attachRequired): self
    {
        $this->spec['attachRequired'] = $attachRequired;
        return $this;
    }

    /**
     * Get whether attachment is required.
     *
     * @return bool
     */
    public function getAttachRequired(): bool
    {
        return $this->spec['attachRequired'] ?? true;
    }

    /**
     * Set whether the driver supports pod info on mount.
     *
     * @param bool $podInfoOnMount Whether pod info is passed on mount
     *
     * @return self
     */
    public function setPodInfoOnMount(bool $podInfoOnMount): self
    {
        $this->spec['podInfoOnMount'] = $podInfoOnMount;
        return $this;
    }

    /**
     * Get whether pod info on mount is enabled.
     *
     * @return bool
     */
    public function getPodInfoOnMount(): bool
    {
        return $this->spec['podInfoOnMount'] ?? false;
    }

    /**
     * Set volume lifecycle modes.
     *
     * @param array<int, string> $modes Volume lifecycle modes (Persistent, Ephemeral)
     *
     * @return self
     */
    public function setVolumeLifecycleModes(array $modes): self
    {
        $this->spec['volumeLifecycleModes'] = $modes;
        return $this;
    }

    /**
     * Get volume lifecycle modes.
     *
     * @return array<int, string>
     */
    public function getVolumeLifecycleModes(): array
    {
        return $this->spec['volumeLifecycleModes'] ?? ['Persistent'];
    }

    /**
     * Add volume lifecycle mode.
     *
     * @param string $mode Volume lifecycle mode
     *
     * @return self
     */
    public function addVolumeLifecycleMode(string $mode): self
    {
        if (!isset($this->spec['volumeLifecycleModes'])) {
            $this->spec['volumeLifecycleModes'] = [];
        }
        if (!in_array($mode, $this->spec['volumeLifecycleModes'], true)) {
            $this->spec['volumeLifecycleModes'][] = $mode;
        }
        return $this;
    }

    /**
     * Set whether the driver supports storage capacity tracking.
     *
     * @param bool $storageCapacity Whether storage capacity is tracked
     *
     * @return self
     */
    public function setStorageCapacity(bool $storageCapacity): self
    {
        $this->spec['storageCapacity'] = $storageCapacity;
        return $this;
    }

    /**
     * Get whether storage capacity tracking is enabled.
     *
     * @return bool
     */
    public function getStorageCapacity(): bool
    {
        return $this->spec['storageCapacity'] ?? false;
    }

    /**
     * Set filesystem group policy.
     *
     * @param string $policy FSGroup policy (ReadWriteOnceWithFSType, File, None)
     *
     * @return self
     */
    public function setFsGroupPolicy(string $policy): self
    {
        $this->spec['fsGroupPolicy'] = $policy;
        return $this;
    }

    /**
     * Get filesystem group policy.
     *
     * @return string|null
     */
    public function getFsGroupPolicy(): ?string
    {
        return $this->spec['fsGroupPolicy'] ?? null;
    }

    /**
     * Set supported modes for token requests.
     *
     * @param array<int, string> $tokenRequests Token request modes
     *
     * @return self
     */
    public function setTokenRequests(array $tokenRequests): self
    {
        $this->spec['tokenRequests'] = $tokenRequests;
        return $this;
    }

    /**
     * Get token requests.
     *
     * @return array<int, string>
     */
    public function getTokenRequests(): array
    {
        return $this->spec['tokenRequests'] ?? [];
    }

    /**
     * Set whether the driver requires republishing.
     *
     * @param bool $requiresRepublish Whether republishing is required
     *
     * @return self
     */
    public function setRequiresRepublish(bool $requiresRepublish): self
    {
        $this->spec['requiresRepublish'] = $requiresRepublish;
        return $this;
    }

    /**
     * Get whether republishing is required.
     *
     * @return bool
     */
    public function getRequiresRepublish(): bool
    {
        return $this->spec['requiresRepublish'] ?? false;
    }
}
