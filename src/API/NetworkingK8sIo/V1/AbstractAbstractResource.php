<?php

declare(strict_types=1);

namespace Kubernetes\API\NetworkingK8sIo\V1;

use Kubernetes\API\AbstractResource;

/**
 * Abstract base class for Kubernetes Networking.k8s.io V1 API resources.
 *
 * Provides common functionality for all resources in the networking.k8s.io/v1 API group.
 */
abstract class AbstractAbstractResource extends AbstractResource
{
    /**
     * Get the API version of the resource.
     *
     * @return string The API version (networking.k8s.io/v1)
     */
    public function getApiVersion(): string
    {
        return 'networking.k8s.io/v1';
    }

    /**
     * Get the kind of the resource.
     *
     * Must be implemented by each concrete resource class.
     *
     * @return string The resource kind
     */
    abstract public function getKind(): string;
}
