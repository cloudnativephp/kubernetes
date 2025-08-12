<?php

declare(strict_types=1);

namespace Kubernetes\API\Core\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * Endpoints represents the endpoints for a service.
 *
 * Endpoints is a collection of endpoints that implement the actual service.
 * It contains a list of endpoint subsets.
 *
 * @see https://kubernetes.io/docs/reference/kubernetes-api/service-resources/endpoints-v1/
 */
class Endpoints extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of the resource.
     *
     * @return string
     */
    public function getKind(): string
    {
        return 'Endpoints';
    }

    /**
     * Set the subsets of endpoints.
     *
     * @param array<int, array<string, mixed>> $subsets
     *
     * @return self
     */
    public function setSubsets(array $subsets): self
    {
        $this->spec['subsets'] = $subsets;

        return $this;
    }

    /**
     * Add a subset to the endpoints.
     *
     * @param array<string, mixed> $subset
     *
     * @return self
     */
    public function addSubset(array $subset): self
    {
        $this->spec['subsets'][] = $subset;

        return $this;
    }

    /**
     * Get the addresses from all subsets.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAddresses(): array
    {
        $addresses = [];
        foreach ($this->getSubsets() as $subset) {
            if (isset($subset['addresses'])) {
                $addresses = array_merge($addresses, $subset['addresses']);
            }
        }

        return $addresses;
    }

    /**
     * Get the subsets of endpoints.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSubsets(): array
    {
        return $this->spec['subsets'] ?? [];
    }

    /**
     * Get the ports from all subsets.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPorts(): array
    {
        $ports = [];
        foreach ($this->getSubsets() as $subset) {
            if (isset($subset['ports'])) {
                $ports = array_merge($ports, $subset['ports']);
            }
        }

        return $ports;
    }
}
