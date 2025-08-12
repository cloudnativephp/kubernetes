<?php

declare(strict_types=1);

namespace Kubernetes\API\Core\V1;

/**
 * Represents a Kubernetes Node resource.
 *
 * Node is a worker node in Kubernetes, previously known as a minion.
 *
 * @see https://kubernetes.io/docs/concepts/architecture/nodes/
 */
class Node extends AbstractAbstractResource
{
    /**
     * Get the kind of the resource.
     *
     * @return string The resource kind (Node)
     */
    public function getKind(): string
    {
        return 'Node';
    }

    /**
     * Get the pod CIDR.
     *
     * @return string|null The pod CIDR
     */
    public function getPodCIDR(): ?string
    {
        return $this->spec['podCIDR'] ?? null;
    }

    /**
     * Set the pod CIDR.
     *
     * @param string $podCIDR The pod CIDR
     *
     * @return self
     */
    public function setPodCIDR(string $podCIDR): self
    {
        $this->spec['podCIDR'] = $podCIDR;

        return $this;
    }

    /**
     * Get the pod CIDRs.
     *
     * @return array<int, string> The pod CIDRs
     */
    public function getPodCIDRs(): array
    {
        return $this->spec['podCIDRs'] ?? [];
    }

    /**
     * Set the pod CIDRs.
     *
     * @param array<int, string> $podCIDRs The pod CIDRs
     *
     * @return self
     */
    public function setPodCIDRs(array $podCIDRs): self
    {
        $this->spec['podCIDRs'] = $podCIDRs;

        return $this;
    }

    /**
     * Get the provider ID.
     *
     * @return string|null The provider ID
     */
    public function getProviderID(): ?string
    {
        return $this->spec['providerID'] ?? null;
    }

    /**
     * Set the provider ID.
     *
     * @param string $providerID The provider ID
     *
     * @return self
     */
    public function setProviderID(string $providerID): self
    {
        $this->spec['providerID'] = $providerID;

        return $this;
    }

    /**
     * Check if the node is unschedulable.
     *
     * @return bool True if the node is unschedulable
     */
    public function isUnschedulable(): bool
    {
        return $this->spec['unschedulable'] ?? false;
    }

    /**
     * Set the node as unschedulable.
     *
     * @param bool $unschedulable Whether the node is unschedulable
     *
     * @return self
     */
    public function setUnschedulable(bool $unschedulable = true): self
    {
        $this->spec['unschedulable'] = $unschedulable;

        return $this;
    }

    /**
     * Get the node taints.
     *
     * @return array<int, array<string, mixed>> The node taints
     */
    public function getTaints(): array
    {
        return $this->spec['taints'] ?? [];
    }

    /**
     * Set the node taints.
     *
     * @param array<int, array<string, mixed>> $taints The node taints
     *
     * @return self
     */
    public function setTaints(array $taints): self
    {
        $this->spec['taints'] = $taints;

        return $this;
    }

    /**
     * Add a taint to the node.
     *
     * @param string $key    The taint key
     * @param string $value  The taint value
     * @param string $effect The taint effect (NoSchedule, PreferNoSchedule, NoExecute)
     *
     * @return self
     */
    public function addTaint(string $key, string $value, string $effect): self
    {
        if (!isset($this->spec['taints'])) {
            $this->spec['taints'] = [];
        }

        $this->spec['taints'][] = [
            'key'    => $key,
            'value'  => $value,
            'effect' => $effect,
        ];

        return $this;
    }

    /**
     * Get the node capacity.
     *
     * @return array<string, string> The node capacity
     */
    public function getCapacity(): array
    {
        return $this->status['capacity'] ?? [];
    }

    /**
     * Get the node allocatable resources.
     *
     * @return array<string, string> The allocatable resources
     */
    public function getAllocatable(): array
    {
        return $this->status['allocatable'] ?? [];
    }

    /**
     * Get the node phase.
     *
     * @return string|null The node phase (Pending, Running, Terminated)
     */
    public function getPhase(): ?string
    {
        return $this->status['phase'] ?? null;
    }

    /**
     * Get the node addresses.
     *
     * @return array<int, array<string, string>> The node addresses
     */
    public function getAddresses(): array
    {
        return $this->status['addresses'] ?? [];
    }

    /**
     * Get the node daemon endpoints.
     *
     * @return array<string, mixed> The daemon endpoints
     */
    public function getDaemonEndpoints(): array
    {
        return $this->status['daemonEndpoints'] ?? [];
    }

    /**
     * Get the node info.
     *
     * @return array<string, string> The node info
     */
    public function getNodeInfo(): array
    {
        return $this->status['nodeInfo'] ?? [];
    }

    /**
     * Get the node images.
     *
     * @return array<int, array<string, mixed>> The node images
     */
    public function getImages(): array
    {
        return $this->status['images'] ?? [];
    }

    /**
     * Check if the node is ready.
     *
     * @return bool True if the node is ready
     */
    public function isReady(): bool
    {
        $conditions = $this->getConditions();

        foreach ($conditions as $condition) {
            if ($condition['type'] === 'Ready' && $condition['status'] === 'True') {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the node conditions.
     *
     * @return array<int, array<string, mixed>> The node conditions
     */
    public function getConditions(): array
    {
        return $this->status['conditions'] ?? [];
    }
}
