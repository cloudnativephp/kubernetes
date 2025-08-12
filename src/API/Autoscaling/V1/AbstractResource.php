<?php

declare(strict_types=1);

namespace Kubernetes\API\Autoscaling\V1;

use Kubernetes\API\AbstractResource as BaseAbstractResource;

/**
 * Base class for Autoscaling API v1 resources.
 */
abstract class AbstractResource extends BaseAbstractResource
{
    /**
     * Get the API version for autoscaling/v1 resources.
     *
     * @return string
     */
    public function getApiVersion(): string
    {
        return 'autoscaling/v1';
    }

    /**
     * Get the kind of Kubernetes resource.
     *
     * @return string
     */
    abstract public function getKind(): string;
}
