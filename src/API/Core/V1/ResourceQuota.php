<?php

declare(strict_types=1);

namespace Kubernetes\API\Core\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * ResourceQuota provides constraints that limit aggregate resource consumption per namespace.
 *
 * It can limit the quantity of objects that can be created in a namespace by type,
 * as well as the total amount of compute resources that may be consumed by resources in that namespace.
 *
 * @see https://kubernetes.io/docs/reference/kubernetes-api/policy-resources/resource-quota-v1/
 */
class ResourceQuota extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of the resource.
     *
     * @return string
     */
    public function getKind(): string
    {
        return 'ResourceQuota';
    }

    /**
     * Get the hard limits enforced per namespace.
     *
     * @return array<string, string>
     */
    public function getHard(): array
    {
        return $this->spec['hard'] ?? [];
    }

    /**
     * Set the hard limits enforced per namespace.
     *
     * @param array<string, string> $hard
     *
     * @return self
     */
    public function setHard(array $hard): self
    {
        $this->spec['hard'] = $hard;

        return $this;
    }

    /**
     * Get the scope selector that restricts the set of objects to which the quota applies.
     *
     * @return array<string, mixed>|null
     */
    public function getScopeSelector(): ?array
    {
        return $this->spec['scopeSelector'] ?? null;
    }

    /**
     * Set the scope selector that restricts the set of objects to which the quota applies.
     *
     * @param array<string, mixed> $scopeSelector
     *
     * @return self
     */
    public function setScopeSelector(array $scopeSelector): self
    {
        $this->spec['scopeSelector'] = $scopeSelector;

        return $this;
    }

    /**
     * Get the collection of scopes that the quota restricts.
     *
     * @return array<int, string>
     */
    public function getScopes(): array
    {
        return $this->spec['scopes'] ?? [];
    }

    /**
     * Set the collection of scopes that the quota restricts.
     *
     * @param array<int, string> $scopes
     *
     * @return self
     */
    public function setScopes(array $scopes): self
    {
        $this->spec['scopes'] = $scopes;

        return $this;
    }

    /**
     * Add a scope to the quota.
     *
     * @param string $scope
     *
     * @return self
     */
    public function addScope(string $scope): self
    {
        $this->spec['scopes'][] = $scope;

        return $this;
    }

    /**
     * Get the current observed total usage of the resource in the namespace.
     *
     * @return array<string, string>
     */
    public function getUsed(): array
    {
        return $this->status['used'] ?? [];
    }

    /**
     * Set compute resource limits.
     *
     * @param string $cpuLimit
     * @param string $memoryLimit
     *
     * @return self
     */
    public function setComputeLimits(string $cpuLimit, string $memoryLimit): self
    {
        return $this
            ->addHardLimit('limits.cpu', $cpuLimit)
            ->addHardLimit('limits.memory', $memoryLimit);
    }

    /**
     * Add a hard limit for a specific resource.
     *
     * @param string $resource
     * @param string $limit
     *
     * @return self
     */
    public function addHardLimit(string $resource, string $limit): self
    {
        $this->spec['hard'][$resource] = $limit;

        return $this;
    }

    /**
     * Set compute resource requests.
     *
     * @param string $cpuRequest
     * @param string $memoryRequest
     *
     * @return self
     */
    public function setComputeRequests(string $cpuRequest, string $memoryRequest): self
    {
        return $this
            ->addHardLimit('requests.cpu', $cpuRequest)
            ->addHardLimit('requests.memory', $memoryRequest);
    }

    /**
     * Set object count limits.
     *
     * @param int $podCount
     * @param int $serviceCount
     * @param int $secretCount
     * @param int $configMapCount
     *
     * @return self
     */
    public function setObjectLimits(
        int $podCount = 0,
        int $serviceCount = 0,
        int $secretCount = 0,
        int $configMapCount = 0
    ): self {
        if ($podCount > 0) {
            $this->addHardLimit('count/pods', (string) $podCount);
        }

        if ($serviceCount > 0) {
            $this->addHardLimit('count/services', (string) $serviceCount);
        }

        if ($secretCount > 0) {
            $this->addHardLimit('count/secrets', (string) $secretCount);
        }

        if ($configMapCount > 0) {
            $this->addHardLimit('count/configmaps', (string) $configMapCount);
        }

        return $this;
    }
}
