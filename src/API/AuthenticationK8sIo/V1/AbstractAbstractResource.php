<?php

declare(strict_types=1);

namespace Kubernetes\API\AuthenticationK8sIo\V1;

use Kubernetes\API\AbstractResource;

/**
 * AbstractResource for authentication.k8s.io/v1 API group.
 *
 * Provides base functionality for authentication resources including
 * TokenRequest and TokenReview resources for token-based authentication.
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
        return 'authentication.k8s.io/v1';
    }

    /**
     * Get the kind of this resource.
     *
     * @return string
     */
    abstract public function getKind(): string;
}
