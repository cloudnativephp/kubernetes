<?php

declare(strict_types=1);

namespace Kubernetes\API\StorageK8sIo\V1;

/**
 * StorageClass describes the parameters for a class of storage.
 *
 * @link https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.30/#storageclass-v1-storage-k8s-io
 */
class StorageClass extends AbstractAbstractResource
{
    /**
     * Get the resource kind.
     *
     * @return string
     */
    public function getKind(): string
    {
        return 'StorageClass';
    }

    /**
     * Set the provisioner for this storage class.
     *
     * @param string $provisioner The provisioner name (e.g., "kubernetes.io/aws-ebs")
     *
     * @return self
     */
    public function setProvisioner(string $provisioner): self
    {
        $this->spec['provisioner'] = $provisioner;
        return $this;
    }

    /**
     * Get the provisioner.
     *
     * @return string|null
     */
    public function getProvisioner(): ?string
    {
        return $this->spec['provisioner'] ?? null;
    }

    /**
     * Set parameters for the provisioner.
     *
     * @param array<string, string> $parameters Provisioner-specific parameters
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
     * Set reclaim policy.
     *
     * @param string $policy Delete, Retain, or Recycle
     *
     * @return self
     */
    public function setReclaimPolicy(string $policy): self
    {
        $this->spec['reclaimPolicy'] = $policy;
        return $this;
    }

    /**
     * Get reclaim policy.
     *
     * @return string|null
     */
    public function getReclaimPolicy(): ?string
    {
        return $this->spec['reclaimPolicy'] ?? null;
    }

    /**
     * Set volume binding mode.
     *
     * @param string $mode Immediate or WaitForFirstConsumer
     *
     * @return self
     */
    public function setVolumeBindingMode(string $mode): self
    {
        $this->spec['volumeBindingMode'] = $mode;
        return $this;
    }

    /**
     * Get volume binding mode.
     *
     * @return string|null
     */
    public function getVolumeBindingMode(): ?string
    {
        return $this->spec['volumeBindingMode'] ?? null;
    }

    /**
     * Set allowed topologies for volume provisioning.
     *
     * @param array<int, array<string, mixed>> $topologies Topology constraints
     *
     * @return self
     */
    public function setAllowedTopologies(array $topologies): self
    {
        $this->spec['allowedTopologies'] = $topologies;
        return $this;
    }

    /**
     * Get allowed topologies.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAllowedTopologies(): array
    {
        return $this->spec['allowedTopologies'] ?? [];
    }

    /**
     * Add topology constraint.
     *
     * @param array<string, string> $matchLabels Topology labels to match
     *
     * @return self
     */
    public function addTopologyConstraint(array $matchLabels): self
    {
        if (!isset($this->spec['allowedTopologies'])) {
            $this->spec['allowedTopologies'] = [];
        }
        $this->spec['allowedTopologies'][] = [
            'matchLabelExpressions' => array_map(
                fn ($key, $value) => ['key' => $key, 'values' => [$value]],
                array_keys($matchLabels),
                array_values($matchLabels)
            ),
        ];
        return $this;
    }

    /**
     * Set whether volume expansion is allowed.
     *
     * @param bool $allowed Whether expansion is allowed
     *
     * @return self
     */
    public function setAllowVolumeExpansion(bool $allowed): self
    {
        $this->spec['allowVolumeExpansion'] = $allowed;
        return $this;
    }

    /**
     * Get whether volume expansion is allowed.
     *
     * @return bool
     */
    public function getAllowVolumeExpansion(): bool
    {
        return $this->spec['allowVolumeExpansion'] ?? false;
    }

    /**
     * Set mount options.
     *
     * @param array<int, string> $mountOptions Mount options for volumes
     *
     * @return self
     */
    public function setMountOptions(array $mountOptions): self
    {
        $this->spec['mountOptions'] = $mountOptions;
        return $this;
    }

    /**
     * Get mount options.
     *
     * @return array<int, string>
     */
    public function getMountOptions(): array
    {
        return $this->spec['mountOptions'] ?? [];
    }

    /**
     * Add mount option.
     *
     * @param string $option Mount option to add
     *
     * @return self
     */
    public function addMountOption(string $option): self
    {
        if (!isset($this->spec['mountOptions'])) {
            $this->spec['mountOptions'] = [];
        }
        $this->spec['mountOptions'][] = $option;
        return $this;
    }
}
