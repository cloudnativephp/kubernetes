<?php

declare(strict_types=1);

namespace Kubernetes\API\NetworkingK8sIo\V1;

/**
 * Represents a Kubernetes IngressClass resource.
 *
 * IngressClass represents the class of the Ingress, referenced by the Ingress Spec.
 * The ingressclass.kubernetes.io/is-default-class annotation can be used to indicate
 * that an IngressClass should be considered default.
 *
 * @see https://kubernetes.io/docs/concepts/services-networking/ingress/#ingress-class
 */
class IngressClass extends AbstractAbstractResource
{
    /**
     * Get the kind of the resource.
     *
     * @return string The resource kind (IngressClass)
     */
    public function getKind(): string
    {
        return 'IngressClass';
    }

    /**
     * Get the parameters for this ingress class.
     *
     * @return array<string, mixed>|null The parameters
     */
    public function getParameters(): ?array
    {
        return $this->spec['parameters'] ?? null;
    }

    /**
     * Check if this ingress class is marked as default.
     *
     * @return bool True if this is the default ingress class
     */
    public function isDefault(): bool
    {
        $annotations = $this->getAnnotations();
        return ($annotations['ingressclass.kubernetes.io/is-default-class'] ?? '') === 'true';
    }

    /**
     * Set parameters referencing a ConfigMap.
     *
     * @param string      $name      The ConfigMap name
     * @param string      $namespace The ConfigMap namespace
     * @param string|null $scope     The parameter scope (optional)
     *
     * @return self
     */
    public function setConfigMapParameters(string $name, string $namespace, ?string $scope = null): self
    {
        $parameters = [
            'apiGroup' => '',
            'kind'     => 'ConfigMap',
            'name'     => $name,
        ];

        if ($namespace !== '') {
            $parameters['namespace'] = $namespace;
        }

        if ($scope !== null) {
            $parameters['scope'] = $scope;
        }

        return $this->setParameters($parameters);
    }

    /**
     * Set the parameters for this ingress class.
     *
     * @param array<string, mixed> $parameters The parameters
     *
     * @return self
     */
    public function setParameters(array $parameters): self
    {
        $this->spec['parameters'] = $parameters;

        return $this;
    }

    /**
     * Set parameters referencing a Secret.
     *
     * @param string      $name      The Secret name
     * @param string      $namespace The Secret namespace
     * @param string|null $scope     The parameter scope (optional)
     *
     * @return self
     */
    public function setSecretParameters(string $name, string $namespace, ?string $scope = null): self
    {
        $parameters = [
            'apiGroup' => '',
            'kind'     => 'Secret',
            'name'     => $name,
        ];

        if ($namespace !== '') {
            $parameters['namespace'] = $namespace;
        }

        if ($scope !== null) {
            $parameters['scope'] = $scope;
        }

        return $this->setParameters($parameters);
    }

    /**
     * Create an NGINX ingress class.
     *
     * @param bool $isDefault Whether this should be the default ingress class
     *
     * @return self
     */
    public function createNginxIngressClass(bool $isDefault = false): self
    {
        $this->setController('k8s.io/ingress-nginx');

        if ($isDefault) {
            $this->setAsDefault(true);
        }

        return $this;
    }

    /**
     * Set the controller name for this ingress class.
     *
     * @param string $controller The controller name
     *
     * @return self
     */
    public function setController(string $controller): self
    {
        $this->spec['controller'] = $controller;

        return $this;
    }

    /**
     * Mark this ingress class as the default.
     *
     * @param bool $isDefault Whether this should be the default ingress class
     *
     * @return self
     */
    public function setAsDefault(bool $isDefault = true): self
    {
        $annotations = $this->getAnnotations();

        if ($isDefault) {
            $annotations['ingressclass.kubernetes.io/is-default-class'] = 'true';
        } else {
            unset($annotations['ingressclass.kubernetes.io/is-default-class']);
        }

        $this->setAnnotations($annotations);

        return $this;
    }

    /**
     * Create a Traefik ingress class.
     *
     * @param bool $isDefault Whether this should be the default ingress class
     *
     * @return self
     */
    public function createTraefikIngressClass(bool $isDefault = false): self
    {
        $this->setController('traefik.io/ingress-controller');

        if ($isDefault) {
            $this->setAsDefault(true);
        }

        return $this;
    }

    /**
     * Create an HAProxy ingress class.
     *
     * @param bool $isDefault Whether this should be the default ingress class
     *
     * @return self
     */
    public function createHaproxyIngressClass(bool $isDefault = false): self
    {
        $this->setController('haproxy.org/ingress-controller');

        if ($isDefault) {
            $this->setAsDefault(true);
        }

        return $this;
    }

    /**
     * Create an AWS ALB ingress class.
     *
     * @param bool $isDefault Whether this should be the default ingress class
     *
     * @return self
     */
    public function createAwsAlbIngressClass(bool $isDefault = false): self
    {
        $this->setController('ingress.k8s.aws/alb');

        if ($isDefault) {
            $this->setAsDefault(true);
        }

        return $this;
    }

    /**
     * Create a GCE ingress class.
     *
     * @param bool $isDefault Whether this should be the default ingress class
     *
     * @return self
     */
    public function createGceIngressClass(bool $isDefault = false): self
    {
        $this->setController('k8s.io/ingress-gce');

        if ($isDefault) {
            $this->setAsDefault(true);
        }

        return $this;
    }

    /**
     * Get the controller type based on the controller name.
     *
     * @return string The controller type (nginx, traefik, haproxy, aws-alb, gce, unknown)
     */
    public function getControllerType(): string
    {
        $controller = $this->getController();

        return match ($controller) {
            'k8s.io/ingress-nginx'           => 'nginx',
            'traefik.io/ingress-controller'  => 'traefik',
            'haproxy.org/ingress-controller' => 'haproxy',
            'ingress.k8s.aws/alb'            => 'aws-alb',
            'k8s.io/ingress-gce'             => 'gce',
            default                          => 'unknown',
        };
    }

    /**
     * Get the controller name for this ingress class.
     *
     * @return string|null The controller name
     */
    public function getController(): ?string
    {
        return $this->spec['controller'] ?? null;
    }
}
