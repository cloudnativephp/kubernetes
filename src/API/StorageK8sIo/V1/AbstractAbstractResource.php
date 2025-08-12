<?php

declare(strict_types=1);

namespace Kubernetes\API\StorageK8sIo\V1;

use Kubernetes\API\AbstractResource;

/**
 * Abstract base class for storage.k8s.io/v1 API resources.
 *
 * @link https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.30/#-storage-k8s-io-v1
 */
abstract class AbstractAbstractResource extends AbstractResource
{
    /**
     * Get the API version for storage.k8s.io/v1 resources.
     *
     * @return string
     */
    public function getApiVersion(): string
    {
        return 'storage.k8s.io/v1';
    }

    /**
     * Get the resource kind.
     *
     * @return string
     */
    abstract public function getKind(): string;
}
