<?php

declare(strict_types=1);

namespace Kubernetes\API\CoordinationK8sIo\V1;

use Kubernetes\API\AbstractResource;

/**
 * AbstractResource for coordination.k8s.io/v1 API group.
 *
 * Provides base functionality for coordination resources including
 * Lease resources for leader election and coordination patterns.
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
        return 'coordination.k8s.io/v1';
    }

    /**
     * Get the kind of this resource.
     *
     * @return string
     */
    abstract public function getKind(): string;
}
