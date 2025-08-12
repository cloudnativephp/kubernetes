<?php

declare(strict_types=1);

namespace Kubernetes\API\ApiregistrationK8sIo\V1;

use Kubernetes\API\AbstractResource;

/**
 * AbstractResource for apiregistration.k8s.io/v1 API group.
 *
 * Provides base functionality for API registration resources including
 * APIService resources for extending the Kubernetes API.
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
        return 'apiregistration.k8s.io/v1';
    }

    /**
     * Get the kind of this resource.
     *
     * @return string
     */
    abstract public function getKind(): string;
}
