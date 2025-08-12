<?php

declare(strict_types=1);

namespace Kubernetes\API\CertificatesK8sIo\V1;

use Kubernetes\API\AbstractResource;

/**
 * AbstractResource for certificates.k8s.io/v1 API group.
 *
 * Provides base functionality for certificate resources including
 * CertificateSigningRequest resources for certificate management.
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
        return 'certificates.k8s.io/v1';
    }

    /**
     * Get the kind of this resource.
     *
     * @return string
     */
    abstract public function getKind(): string;
}
