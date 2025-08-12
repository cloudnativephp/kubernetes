<?php

declare(strict_types=1);

namespace Kubernetes\API\EventsK8sIo\V1;

use Kubernetes\API\AbstractResource;

/**
 * AbstractResource for events.k8s.io/v1 API group.
 *
 * Provides base functionality for event resources including
 * Event resources for enhanced cluster event handling.
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
        return 'events.k8s.io/v1';
    }

    /**
     * Get the kind of this resource.
     *
     * @return string
     */
    abstract public function getKind(): string;
}
