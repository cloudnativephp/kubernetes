<?php

declare(strict_types=1);

namespace Kubernetes\API\NodeK8sIo\V1;

use Kubernetes\API\AbstractResource;

/**
 * Abstract base class for node.k8s.io/v1 API resources.
 *
 * @link https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.30/#-node-k8s-io-v1
 */
abstract class AbstractAbstractResource extends AbstractResource
{
    /**
     * Get the API version for node.k8s.io/v1 resources.
     *
     * @return string
     */
    public function getApiVersion(): string
    {
        return 'node.k8s.io/v1';
    }

    /**
     * Get the resource kind.
     *
     * @return string
     */
    abstract public function getKind(): string;
}
