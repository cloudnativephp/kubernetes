<?php

declare(strict_types=1);

namespace Kubernetes\API\RbacAuthorizationK8sIo\V1;

use Kubernetes\API\AbstractResource;

/**
 * Abstract base class for Kubernetes RBAC Authorization V1 API resources.
 *
 * Provides common functionality for all resources in the rbac.authorization.k8s.io/v1 API group.
 */
abstract class AbstractAbstractResource extends AbstractResource
{
    /**
     * Get the API version of the resource.
     *
     * @return string The API version (rbac.authorization.k8s.io/v1)
     */
    public function getApiVersion(): string
    {
        return 'rbac.authorization.k8s.io/v1';
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
