<?php

declare(strict_types=1);

namespace Kubernetes\API\AuthorizationK8sIo\V1;

use Kubernetes\API\AbstractResource;

/**
 * AbstractResource for authorization.k8s.io/v1 API group.
 *
 * Provides base functionality for authorization resources including
 * SubjectAccessReview and SelfSubjectAccessReview resources for
 * authorization checks.
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
        return 'authorization.k8s.io/v1';
    }

    /**
     * Get the kind of this resource.
     *
     * @return string
     */
    abstract public function getKind(): string;
}
