<?php

declare(strict_types=1);

namespace Kubernetes\API\DiscoveryK8sIo\V1;

use Kubernetes\API\AbstractResource;

/**
 * AbstractResource for discovery.k8s.io/v1 API group.
 *
 * Provides base functionality for discovery resources including
 * EndpointSlice resources for scalable service discovery.
 */
abstract class AbstractAbstractResource extends AbstractResource
{
    /**
     * Get the API version for this resource.
     *
     * @return string
     */
    public function getApiVersion(): string
    {
        return 'discovery.k8s.io/v1';
    }

    /**
     * Get the kind of this resource.
     *
     * @return string
     */
    abstract public function getKind(): string;
}
