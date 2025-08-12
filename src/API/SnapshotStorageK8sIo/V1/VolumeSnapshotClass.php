<?php

declare(strict_types=1);

namespace Kubernetes\API\SnapshotStorageK8sIo\V1;

/**
 * VolumeSnapshotClass specifies parameters that a underlying storage system uses when creating a volume snapshot.
 *
 * @link https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.30/#volumesnapshotclass-v1-snapshot-storage-k8s-io
 */
class VolumeSnapshotClass extends AbstractAbstractResource
{
    /**
     * Get the resource kind.
     *
     * @return string
     */
    public function getKind(): string
    {
        return 'VolumeSnapshotClass';
    }

    /**
     * Set the CSI driver name.
     *
     * @param string $driver Name of the CSI driver
     *
     * @return self
     */
    public function setDriver(string $driver): self
    {
        $this->spec['driver'] = $driver;
        return $this;
    }

    /**
     * Get the CSI driver name.
     *
     * @return string|null
     */
    public function getDriver(): ?string
    {
        return $this->spec['driver'] ?? null;
    }

    /**
     * Set parameters for the driver.
     *
     * @param array<string, string> $parameters Driver-specific parameters
     *
     * @return self
     */
    public function setParameters(array $parameters): self
    {
        $this->spec['parameters'] = $parameters;
        return $this;
    }

    /**
     * Get parameters.
     *
     * @return array<string, string>
     */
    public function getParameters(): array
    {
        return $this->spec['parameters'] ?? [];
    }

    /**
     * Add a parameter.
     *
     * @param string $key   Parameter key
     * @param string $value Parameter value
     *
     * @return self
     */
    public function addParameter(string $key, string $value): self
    {
        if (!isset($this->spec['parameters'])) {
            $this->spec['parameters'] = [];
        }
        $this->spec['parameters'][$key] = $value;
        return $this;
    }

    /**
     * Set deletion policy.
     *
     * @param string $policy Delete or Retain
     *
     * @return self
     */
    public function setDeletionPolicy(string $policy): self
    {
        $this->spec['deletionPolicy'] = $policy;
        return $this;
    }

    /**
     * Get deletion policy.
     *
     * @return string|null
     */
    public function getDeletionPolicy(): ?string
    {
        return $this->spec['deletionPolicy'] ?? null;
    }
}
