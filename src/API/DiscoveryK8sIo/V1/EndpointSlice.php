<?php

declare(strict_types=1);

namespace Kubernetes\API\DiscoveryK8sIo\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * EndpointSlice represents a subset of the endpoints that implement a Service.
 *
 * EndpointSlice provides a scalable and extensible alternative to Endpoints
 * for service discovery, supporting larger numbers of network endpoints.
 *
 * @link https://kubernetes.io/docs/reference/kubernetes-api/service-resources/endpoint-slice-v1/
 */
class EndpointSlice extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of this resource.
     *
     * @return string
     */
    public function getKind(): string
    {
        return 'EndpointSlice';
    }

    /**
     * Get the address type.
     *
     * @return string|null
     */
    public function getAddressType(): ?string
    {
        return $this->spec['addressType'] ?? null;
    }

    /**
     * Set the endpoints for this slice.
     *
     * @param array<int, array<string, mixed>> $endpoints List of endpoints
     *
     * @return self
     */
    public function setEndpoints(array $endpoints): self
    {
        $this->spec['endpoints'] = $endpoints;
        return $this;
    }

    /**
     * Set the ports for the endpoint slice.
     *
     * @param array<int, array<string, mixed>> $ports List of ports
     *
     * @return self
     */
    public function setPorts(array $ports): self
    {
        $this->spec['ports'] = $ports;
        return $this;
    }

    /**
     * Get the ports.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPorts(): array
    {
        return $this->spec['ports'] ?? [];
    }

    /**
     * Get all serving endpoints.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getServingEndpoints(): array
    {
        return array_filter($this->getEndpoints(), function ($endpoint) {
            return $endpoint['conditions']['serving'] ?? false;
        });
    }

    /**
     * Get the endpoints.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getEndpoints(): array
    {
        return $this->spec['endpoints'] ?? [];
    }

    /**
     * Get all addresses from all endpoints.
     *
     * @return array<string>
     */
    public function getAllAddresses(): array
    {
        $addresses = [];
        foreach ($this->getEndpoints() as $endpoint) {
            $addresses = array_merge($addresses, $endpoint['addresses'] ?? []);
        }
        return array_unique($addresses);
    }

    /**
     * Get addresses from ready endpoints only.
     *
     * @return array<string>
     */
    public function getReadyAddresses(): array
    {
        $addresses = [];
        foreach ($this->getReadyEndpoints() as $endpoint) {
            $addresses = array_merge($addresses, $endpoint['addresses'] ?? []);
        }
        return array_unique($addresses);
    }

    /**
     * Get all ready endpoints.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getReadyEndpoints(): array
    {
        return array_filter($this->getEndpoints(), function ($endpoint) {
            return $endpoint['conditions']['ready'] ?? false;
        });
    }

    /**
     * Helper method to create an IPv6 endpoint slice.
     *
     * @param string $serviceName Name of the associated service
     * @param string $namespace   Namespace for the endpoint slice
     *
     * @return self
     */
    public function createIPv6EndpointSlice(string $serviceName, string $namespace): self
    {
        return $this
            ->setName("{$serviceName}-ipv6")
            ->setNamespace($namespace)
            ->setAddressType('IPv6')
            ->setLabels(['kubernetes.io/service-name' => $serviceName]);
    }

    /**
     * Set the address type for the endpoint slice.
     *
     * @param string $addressType Address type (IPv4, IPv6, or FQDN)
     *
     * @return self
     */
    public function setAddressType(string $addressType): self
    {
        $this->spec['addressType'] = $addressType;
        return $this;
    }

    /**
     * Helper method to create a multi-port web service endpoint slice.
     *
     * @param string        $serviceName Name of the service
     * @param string        $namespace   Namespace
     * @param array<string> $addresses   List of endpoint addresses
     * @param int           $httpPort    HTTP port number
     * @param int           $httpsPort   HTTPS port number
     *
     * @return self
     */
    public function createWebServiceEndpointSlice(
        string $serviceName,
        string $namespace,
        array $addresses,
        int $httpPort = 80,
        int $httpsPort = 443
    ): self {
        return $this
            ->createIPv4EndpointSlice($serviceName, $namespace)
            ->addSimplePort('http', $httpPort, 'TCP', 'HTTP')
            ->addSimplePort('https', $httpsPort, 'TCP', 'HTTPS')
            ->addSimpleEndpoint($addresses);
    }

    /**
     * Add an endpoint with simplified parameters.
     *
     * @param array<string> $addresses List of IP addresses or FQDNs
     * @param bool          $ready     Whether the endpoint is ready
     * @param bool          $serving   Whether the endpoint is serving traffic
     * @param string|null   $nodeName  Name of the node hosting this endpoint
     *
     * @return self
     */
    public function addSimpleEndpoint(
        array $addresses,
        bool $ready = true,
        bool $serving = true,
        ?string $nodeName = null
    ): self {
        $endpoint = $this->createEndpoint($addresses, $ready, $serving, false, $nodeName);
        return $this->addEndpoint($endpoint);
    }

    /**
     * Create an endpoint with addresses and conditions.
     *
     * @param array<string> $addresses   List of IP addresses or FQDNs
     * @param bool          $ready       Whether the endpoint is ready
     * @param bool          $serving     Whether the endpoint is serving traffic
     * @param bool          $terminating Whether the endpoint is terminating
     * @param string|null   $nodeName    Name of the node hosting this endpoint
     * @param string|null   $zone        Zone information for the endpoint
     *
     * @return array<string, mixed>
     */
    public function createEndpoint(
        array $addresses,
        bool $ready = true,
        bool $serving = true,
        bool $terminating = false,
        ?string $nodeName = null,
        ?string $zone = null
    ): array {
        $endpoint = [
            'addresses'  => $addresses,
            'conditions' => [
                'ready'       => $ready,
                'serving'     => $serving,
                'terminating' => $terminating,
            ],
        ];

        if ($nodeName !== null) {
            $endpoint['nodeName'] = $nodeName;
        }

        if ($zone !== null) {
            $endpoint['zone'] = $zone;
        }

        return $endpoint;
    }

    /**
     * Add an endpoint to the slice.
     *
     * @param array<string, mixed> $endpoint Endpoint configuration
     *
     * @return self
     */
    public function addEndpoint(array $endpoint): self
    {
        $this->spec['endpoints'][] = $endpoint;
        return $this;
    }

    /**
     * Add a port with simplified parameters.
     *
     * @param string      $name        Port name
     * @param int         $port        Port number
     * @param string      $protocol    Protocol (TCP, UDP, SCTP)
     * @param string|null $appProtocol Application protocol
     *
     * @return self
     */
    public function addSimplePort(
        string $name,
        int $port,
        string $protocol = 'TCP',
        ?string $appProtocol = null
    ): self {
        $portConfig = $this->createPort($name, $port, $protocol, $appProtocol);
        return $this->addPort($portConfig);
    }

    /**
     * Create a port configuration.
     *
     * @param string      $name        Port name
     * @param int         $port        Port number
     * @param string      $protocol    Protocol (TCP, UDP, SCTP)
     * @param string|null $appProtocol Application protocol
     *
     * @return array<string, mixed>
     */
    public function createPort(
        string $name,
        int $port,
        string $protocol = 'TCP',
        ?string $appProtocol = null
    ): array {
        $portConfig = [
            'name'     => $name,
            'port'     => $port,
            'protocol' => $protocol,
        ];

        if ($appProtocol !== null) {
            $portConfig['appProtocol'] = $appProtocol;
        }

        return $portConfig;
    }

    /**
     * Add a port to the endpoint slice.
     *
     * @param array<string, mixed> $port Port configuration
     *
     * @return self
     */
    public function addPort(array $port): self
    {
        $this->spec['ports'][] = $port;
        return $this;
    }

    /**
     * Helper method to create an IPv4 endpoint slice.
     *
     * @param string $serviceName Name of the associated service
     * @param string $namespace   Namespace for the endpoint slice
     *
     * @return self
     */
    public function createIPv4EndpointSlice(string $serviceName, string $namespace): self
    {
        return $this
            ->setName("{$serviceName}-ipv4")
            ->setNamespace($namespace)
            ->setAddressType('IPv4')
            ->setLabels(['kubernetes.io/service-name' => $serviceName]);
    }
}
