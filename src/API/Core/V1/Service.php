<?php

declare(strict_types=1);

namespace Kubernetes\API\Core\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * Represents a Kubernetes Service resource.
 *
 * A Service is an abstract way to expose an application running on a set of Pods as a network service.
 *
 * @see https://kubernetes.io/docs/concepts/services-networking/service/
 */
class Service extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of the resource.
     *
     * @return string The resource kind (Service)
     */
    public function getKind(): string
    {
        return 'Service';
    }

    /**
     * Get the service type.
     *
     * @return string|null The service type (ClusterIP, NodePort, LoadBalancer, ExternalName)
     */
    public function getType(): ?string
    {
        return $this->spec['type'] ?? null;
    }

    /**
     * Set the service type.
     *
     * @param string $type The service type (ClusterIP, NodePort, LoadBalancer, ExternalName)
     *
     * @return self
     */
    public function setType(string $type): self
    {
        $this->spec['type'] = $type;

        return $this;
    }

    /**
     * Get the service selector.
     *
     * @return array<string, string> The selector labels
     */
    public function getSelector(): array
    {
        return $this->spec['selector'] ?? [];
    }

    /**
     * Set the service selector.
     *
     * @param array<string, string> $selector The selector labels
     *
     * @return self
     */
    public function setSelector(array $selector): self
    {
        $this->spec['selector'] = $selector;

        return $this;
    }

    /**
     * Get the service ports.
     *
     * @return array<int, array<string, mixed>> The service ports configuration
     */
    public function getPorts(): array
    {
        return $this->spec['ports'] ?? [];
    }

    /**
     * Set the service ports.
     *
     * @param array<int, array<string, mixed>> $ports The service ports configuration
     *
     * @return self
     */
    public function setPorts(array $ports): self
    {
        $this->spec['ports'] = $ports;

        return $this;
    }

    /**
     * Add a port to the service.
     *
     * @param array<string, mixed> $port The port configuration
     *
     * @return self
     */
    public function addPort(array $port): self
    {
        if (!isset($this->spec['ports'])) {
            $this->spec['ports'] = [];
        }

        $this->spec['ports'][] = $port;

        return $this;
    }

    /**
     * Get the cluster IP.
     *
     * @return string|null The cluster IP address
     */
    public function getClusterIP(): ?string
    {
        return $this->spec['clusterIP'] ?? null;
    }

    /**
     * Set the cluster IP.
     *
     * @param string $clusterIP The cluster IP address
     *
     * @return self
     */
    public function setClusterIP(string $clusterIP): self
    {
        $this->spec['clusterIP'] = $clusterIP;

        return $this;
    }

    /**
     * Get external IPs.
     *
     * @return array<int, string> The external IP addresses
     */
    public function getExternalIPs(): array
    {
        return $this->spec['externalIPs'] ?? [];
    }

    /**
     * Set external IPs.
     *
     * @param array<int, string> $externalIPs The external IP addresses
     *
     * @return self
     */
    public function setExternalIPs(array $externalIPs): self
    {
        $this->spec['externalIPs'] = $externalIPs;

        return $this;
    }

    /**
     * Get the load balancer IP.
     *
     * @return string|null The load balancer IP address
     */
    public function getLoadBalancerIP(): ?string
    {
        return $this->spec['loadBalancerIP'] ?? null;
    }

    /**
     * Set the load balancer IP.
     *
     * @param string $loadBalancerIP The load balancer IP address
     *
     * @return self
     */
    public function setLoadBalancerIP(string $loadBalancerIP): self
    {
        $this->spec['loadBalancerIP'] = $loadBalancerIP;

        return $this;
    }

    /**
     * Get session affinity.
     *
     * @return string|null The session affinity (None, ClientIP)
     */
    public function getSessionAffinity(): ?string
    {
        return $this->spec['sessionAffinity'] ?? null;
    }

    /**
     * Set session affinity.
     *
     * @param string $sessionAffinity The session affinity (None, ClientIP)
     *
     * @return self
     */
    public function setSessionAffinity(string $sessionAffinity): self
    {
        $this->spec['sessionAffinity'] = $sessionAffinity;

        return $this;
    }

    /**
     * Get external name (for ExternalName service type).
     *
     * @return string|null The external name
     */
    public function getExternalName(): ?string
    {
        return $this->spec['externalName'] ?? null;
    }

    /**
     * Set external name (for ExternalName service type).
     *
     * @param string $externalName The external name
     *
     * @return self
     */
    public function setExternalName(string $externalName): self
    {
        $this->spec['externalName'] = $externalName;

        return $this;
    }

    /**
     * Get the service status.
     *
     * @return array<string, mixed> The service status
     */
    public function getStatus(): array
    {
        return $this->status;
    }

    /**
     * Get load balancer status.
     *
     * @return array<string, mixed>|null The load balancer status
     */
    public function getLoadBalancerStatus(): ?array
    {
        return $this->status['loadBalancer'] ?? null;
    }
}
