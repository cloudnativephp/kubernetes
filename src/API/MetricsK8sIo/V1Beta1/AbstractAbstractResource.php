<?php

declare(strict_types=1);

namespace Kubernetes\API\MetricsK8sIo\V1Beta1;

use Kubernetes\API\AbstractResource;

/**
 * Abstract base class for metrics.k8s.io/v1beta1 API resources.
 *
 * @link https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.30/#-metrics-k8s-io-v1beta1
 */
abstract class AbstractAbstractResource extends AbstractResource
{
    /**
     * Get the API version for metrics.k8s.io/v1beta1 resources.
     *
     * @return string
     */
    public function getApiVersion(): string
    {
        return 'metrics.k8s.io/v1beta1';
    }

    /**
     * Get the resource kind.
     *
     * @return string
     */
    abstract public function getKind(): string;
}
