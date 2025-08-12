<?php

declare(strict_types=1);

namespace Kubernetes\API\Autoscaling\V2;

use Kubernetes\API\AbstractResource as BaseAbstractResource;

/**
 * Base class for Autoscaling API v2 resources.
 */
abstract class AbstractResource extends BaseAbstractResource
{
    /**
     * Get the API version for autoscaling/v2 resources.
     *
     * @return string
     */
    public function getApiVersion(): string
    {
        return 'autoscaling/v2';
    }

    /**
     * Get the kind of Kubernetes resource.
     *
     * @return string
     */
    abstract public function getKind(): string;
}
