<?php

declare(strict_types=1);

namespace Kubernetes\API\ApiregistrationK8sIo\V1;

/**
 * APIService represents a server for a particular GroupVersion.
 *
 * APIService is used to register API extensions with the Kubernetes API server,
 * allowing custom resources and controllers to extend the API surface.
 *
 * @link https://kubernetes.io/docs/reference/kubernetes-api/cluster-resources/api-service-v1/
 */
class APIService extends AbstractAbstractResource
{
    /**
     * Get the kind of this resource.
     *
     * @return string
     */
    public function getKind(): string
    {
        return 'APIService';
    }

    /**
     * Get the group name for this API service.
     *
     * @return string|null
     */
    public function getGroup(): ?string
    {
        return $this->spec['group'] ?? null;
    }

    /**
     * Get the version for this API service.
     *
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->spec['version'] ?? null;
    }

    /**
     * Get the group priority minimum.
     *
     * @return int|null
     */
    public function getGroupPriorityMinimum(): ?int
    {
        return $this->spec['groupPriorityMinimum'] ?? null;
    }

    /**
     * Get the version priority.
     *
     * @return int|null
     */
    public function getVersionPriority(): ?int
    {
        return $this->spec['versionPriority'] ?? null;
    }

    /**
     * Get the service reference.
     *
     * @return array<string, mixed>|null
     */
    public function getService(): ?array
    {
        return $this->spec['service'] ?? null;
    }

    /**
     * Set the CA bundle for TLS verification.
     *
     * @param string $caBundle Base64-encoded CA certificate bundle
     *
     * @return self
     */
    public function setCaBundle(string $caBundle): self
    {
        $this->spec['caBundle'] = $caBundle;
        return $this;
    }

    /**
     * Get the CA bundle.
     *
     * @return string|null
     */
    public function getCaBundle(): ?string
    {
        return $this->spec['caBundle'] ?? null;
    }

    /**
     * Set whether TLS verification should be skipped.
     *
     * @param bool $insecure Whether to skip TLS verification
     *
     * @return self
     */
    public function setInsecureSkipTLSVerify(bool $insecure = true): self
    {
        $this->spec['insecureSkipTLSVerify'] = $insecure;
        return $this;
    }

    /**
     * Get whether TLS verification is skipped.
     *
     * @return bool
     */
    public function getInsecureSkipTLSVerify(): bool
    {
        return $this->spec['insecureSkipTLSVerify'] ?? false;
    }

    /**
     * Get the API service status.
     *
     * @return array<string, mixed>
     */
    public function getStatus(): array
    {
        return $this->status;
    }

    /**
     * Get the API service conditions.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getConditions(): array
    {
        return $this->status['conditions'] ?? [];
    }

    /**
     * Helper method to configure a typical extension API service.
     *
     * @param string $group            API group name
     * @param string $version          API version
     * @param string $serviceName      Backend service name
     * @param string $serviceNamespace Backend service namespace
     * @param int    $servicePort      Backend service port
     *
     * @return self
     */
    public function configureExtensionAPI(
        string $group,
        string $version,
        string $serviceName,
        string $serviceNamespace,
        int $servicePort = 443
    ): self {
        return $this
            ->setGroup($group)
            ->setVersion($version)
            ->setService($serviceName, $serviceNamespace, $servicePort)
            ->setGroupPriorityMinimum(1000)
            ->setVersionPriority(15);
    }

    /**
     * Set the version priority within a group.
     *
     * @param int $priority Priority for this version within the group
     *
     * @return self
     */
    public function setVersionPriority(int $priority): self
    {
        $this->spec['versionPriority'] = $priority;
        return $this;
    }

    /**
     * Set the group priority within a group.
     *
     * @param int $priority Priority for this version within the group
     *
     * @return self
     */
    public function setGroupPriorityMinimum(int $priority): self
    {
        $this->spec['groupPriorityMinimum'] = $priority;
        return $this;
    }

    /**
     * Set the service reference for this API service.
     *
     * @param string $name      Service name
     * @param string $namespace Service namespace
     * @param int    $port      Service port
     *
     * @return self
     */
    public function setService(string $name, string $namespace, int $port = 443): self
    {
        $this->spec['service'] = [
            'name'      => $name,
            'namespace' => $namespace,
            'port'      => $port,
        ];
        return $this;
    }

    /**
     * Set the version for this API service.
     *
     * @param string $version The API version
     *
     * @return self
     */
    public function setVersion(string $version): self
    {
        $this->spec['version'] = $version;
        return $this;
    }

    /**
     * Set the group name for this API service.
     *
     * @param string $group The API group name
     *
     * @return self
     */
    public function setGroup(string $group): self
    {
        $this->spec['group'] = $group;
        return $this;
    }
}
