<?php

declare(strict_types=1);

namespace Kubernetes\API\Core\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * LimitRange sets resource usage limits for objects in a namespace.
 *
 * By default, containers run with unbounded compute resources on a Kubernetes cluster.
 * With resource quotas, cluster administrators can restrict the resource consumption and creation on a namespace basis.
 * Within a namespace, a Pod or Container can consume as much CPU and memory as defined by the namespace's resource quota.
 * There is a concern that one Pod or Container could monopolize all of the available resources.
 * LimitRange is a policy to constrain resource allocations (to Pods or Containers) in a namespace.
 *
 * @see https://kubernetes.io/docs/reference/kubernetes-api/policy-resources/limit-range-v1/
 */
class LimitRange extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of the resource.
     *
     * @return string
     */
    public function getKind(): string
    {
        return 'LimitRange';
    }

    /**
     * Get the limits defined in this LimitRange.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLimits(): array
    {
        return $this->spec['limits'] ?? [];
    }

    /**
     * Set the limits for this LimitRange.
     *
     * @param array<int, array<string, mixed>> $limits
     *
     * @return self
     */
    public function setLimits(array $limits): self
    {
        $this->spec['limits'] = $limits;

        return $this;
    }

    /**
     * Add a container limit.
     *
     * @param array<string, string> $defaultLimits
     * @param array<string, string> $defaultRequests
     * @param array<string, string> $max
     * @param array<string, string> $min
     *
     * @return self
     */
    public function addContainerLimit(
        array $defaultLimits = [],
        array $defaultRequests = [],
        array $max = [],
        array $min = []
    ): self {
        $limit = [
            'type' => 'Container',
        ];

        if (!empty($defaultLimits)) {
            $limit['default'] = $defaultLimits;
        }

        if (!empty($defaultRequests)) {
            $limit['defaultRequest'] = $defaultRequests;
        }

        if (!empty($max)) {
            $limit['max'] = $max;
        }

        if (!empty($min)) {
            $limit['min'] = $min;
        }

        return $this->addLimit($limit);
    }

    /**
     * Add a limit to this LimitRange.
     *
     * @param array<string, mixed> $limit
     *
     * @return self
     */
    public function addLimit(array $limit): self
    {
        $this->spec['limits'][] = $limit;

        return $this;
    }

    /**
     * Add a Pod limit.
     *
     * @param array<string, string> $max
     * @param array<string, string> $min
     *
     * @return self
     */
    public function addPodLimit(array $max = [], array $min = []): self
    {
        $limit = [
            'type' => 'Pod',
        ];

        if (!empty($max)) {
            $limit['max'] = $max;
        }

        if (!empty($min)) {
            $limit['min'] = $min;
        }

        return $this->addLimit($limit);
    }

    /**
     * Add a PersistentVolumeClaim limit.
     *
     * @param array<string, string> $max
     * @param array<string, string> $min
     *
     * @return self
     */
    public function addPvcLimit(array $max = [], array $min = []): self
    {
        $limit = [
            'type' => 'PersistentVolumeClaim',
        ];

        if (!empty($max)) {
            $limit['max'] = $max;
        }

        if (!empty($min)) {
            $limit['min'] = $min;
        }

        return $this->addLimit($limit);
    }
}
