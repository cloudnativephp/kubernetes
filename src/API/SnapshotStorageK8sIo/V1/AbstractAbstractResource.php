<?php

declare(strict_types=1);

namespace Kubernetes\API\SnapshotStorageK8sIo\V1;

use Kubernetes\API\AbstractResource;

/**
 * Abstract base class for snapshot.storage.k8s.io/v1 API resources.
 *
 * @link https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.30/#-snapshot-storage-k8s-io-v1
 */
abstract class AbstractAbstractResource extends AbstractResource
{
    /**
     * Get the API version for snapshot.storage.k8s.io/v1 resources.
     *
     * @return string
     */
    public function getApiVersion(): string
    {
        return 'snapshot.storage.k8s.io/v1';
    }

    /**
     * Get the resource kind.
     *
     * @return string
     */
    abstract public function getKind(): string;
}
