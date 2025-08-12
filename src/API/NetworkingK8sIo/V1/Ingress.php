<?php

declare(strict_types=1);

namespace Kubernetes\API\NetworkingK8sIo\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * Represents a Kubernetes Ingress resource.
 *
 * An Ingress exposes HTTP and HTTPS routes from outside the cluster to services within the cluster.
 * Traffic routing is controlled by rules defined on the Ingress resource.
 *
 * @see https://kubernetes.io/docs/concepts/services-networking/ingress/
 */
class Ingress extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of the resource.
     *
     * @return string The resource kind (Ingress)
     */
    public function getKind(): string
    {
        return 'Ingress';
    }

    /**
     * Get the ingress class name.
     *
     * @return string|null The ingress class name
     */
    public function getIngressClassName(): ?string
    {
        return $this->spec['ingressClassName'] ?? null;
    }

    /**
     * Get the default backend for this ingress.
     *
     * @return array<string, mixed>|null The default backend
     */
    public function getDefaultBackend(): ?array
    {
        return $this->spec['defaultBackend'] ?? null;
    }

    /**
     * Set the TLS configuration for this ingress.
     *
     * @param array<int, array<string, mixed>> $tls The TLS configuration
     *
     * @return self
     */
    public function setTls(array $tls): self
    {
        $this->spec['tls'] = $tls;

        return $this;
    }

    /**
     * Set the rules for this ingress.
     *
     * @param array<int, array<string, mixed>> $rules The rules
     *
     * @return self
     */
    public function setRules(array $rules): self
    {
        $this->spec['rules'] = $rules;

        return $this;
    }

    /**
     * Get the load balancer status.
     *
     * @return array<string, mixed>|null The load balancer status
     */
    public function getLoadBalancer(): ?array
    {
        return $this->status['loadBalancer'] ?? null;
    }

    /**
     * Set the default backend to a service.
     *
     * @param string $serviceName The service name
     * @param int    $servicePort The service port
     *
     * @return self
     */
    public function setDefaultBackendService(string $serviceName, int $servicePort): self
    {
        return $this->setDefaultBackend([
            'service' => [
                'name' => $serviceName,
                'port' => [
                    'number' => $servicePort,
                ],
            ],
        ]);
    }

    /**
     * Set the default backend for this ingress.
     *
     * @param array<string, mixed> $defaultBackend The default backend
     *
     * @return self
     */
    public function setDefaultBackend(array $defaultBackend): self
    {
        $this->spec['defaultBackend'] = $defaultBackend;

        return $this;
    }

    /**
     * Add a path to an existing rule.
     *
     * @param int    $ruleIndex   The index of the rule to modify
     * @param string $path        The path
     * @param string $pathType    The path type
     * @param string $serviceName The backend service name
     * @param int    $servicePort The backend service port
     *
     * @return self
     */
    public function addPathToRule(
        int $ruleIndex,
        string $path,
        string $pathType,
        string $serviceName,
        int $servicePort
    ): self {
        if (!isset($this->spec['rules'][$ruleIndex])) {
            return $this;
        }

        $pathConfig = [
            'path'     => $path,
            'pathType' => $pathType,
            'backend'  => [
                'service' => [
                    'name' => $serviceName,
                    'port' => [
                        'number' => $servicePort,
                    ],
                ],
            ],
        ];

        $this->spec['rules'][$ruleIndex]['http']['paths'][] = $pathConfig;

        return $this;
    }

    /**
     * Create a simple ingress with one host and service.
     *
     * @param string      $host         The hostname
     * @param string      $serviceName  The service name
     * @param int         $servicePort  The service port
     * @param string|null $ingressClass The ingress class (optional)
     *
     * @return self
     */
    public function createSimpleIngress(
        string $host,
        string $serviceName,
        int $servicePort,
        ?string $ingressClass = null
    ): self {
        if ($ingressClass !== null) {
            $this->setIngressClassName($ingressClass);
        }

        return $this->addHttpRule($host, '/', 'Prefix', $serviceName, $servicePort);
    }

    /**
     * Set the ingress class name.
     *
     * @param string $ingressClassName The ingress class name
     *
     * @return self
     */
    public function setIngressClassName(string $ingressClassName): self
    {
        $this->spec['ingressClassName'] = $ingressClassName;

        return $this;
    }

    /**
     * Add a simple HTTP rule for a host.
     *
     * @param string $host        The hostname
     * @param string $path        The path (default: /)
     * @param string $pathType    The path type (Exact, Prefix, ImplementationSpecific)
     * @param string $serviceName The backend service name
     * @param int    $servicePort The backend service port
     *
     * @return self
     */
    public function addHttpRule(
        string $host,
        string $path = '/',
        string $pathType = 'Prefix',
        string $serviceName = '',
        int $servicePort = 80
    ): self {
        $rule = [
            'host' => $host,
            'http' => [
                'paths' => [
                    [
                        'path'     => $path,
                        'pathType' => $pathType,
                        'backend'  => [
                            'service' => [
                                'name' => $serviceName,
                                'port' => [
                                    'number' => $servicePort,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $this->addRule($rule);
    }

    /**
     * Add a rule to this ingress.
     *
     * @param array<string, mixed> $rule The rule to add
     *
     * @return self
     */
    public function addRule(array $rule): self
    {
        $this->spec['rules'][] = $rule;

        return $this;
    }

    /**
     * Enable TLS for specific hosts.
     *
     * @param array<string> $hosts      The hosts to enable TLS for
     * @param string        $secretName The TLS certificate secret name
     *
     * @return self
     */
    public function enableTls(array $hosts, string $secretName): self
    {
        return $this->addTlsConfig($hosts, $secretName);
    }

    /**
     * Add a TLS configuration with hosts and secret.
     *
     * @param array<string> $hosts      The hostnames for TLS
     * @param string        $secretName The TLS secret name
     *
     * @return self
     */
    public function addTlsConfig(array $hosts, string $secretName): self
    {
        return $this->addTls([
            'hosts'      => $hosts,
            'secretName' => $secretName,
        ]);
    }

    /**
     * Add a TLS configuration to this ingress.
     *
     * @param array<string, mixed> $tlsConfig The TLS configuration to add
     *
     * @return self
     */
    public function addTls(array $tlsConfig): self
    {
        $this->spec['tls'][] = $tlsConfig;

        return $this;
    }

    /**
     * Get all hostnames configured in this ingress.
     *
     * @return array<string> The configured hostnames
     */
    public function getHostnames(): array
    {
        $hostnames = [];
        foreach ($this->getRules() as $rule) {
            if (isset($rule['host'])) {
                $hostnames[] = $rule['host'];
            }
        }

        return array_unique($hostnames);
    }

    /**
     * Get the rules for this ingress.
     *
     * @return array<int, array<string, mixed>> The rules
     */
    public function getRules(): array
    {
        return $this->spec['rules'] ?? [];
    }

    /**
     * Check if TLS is enabled for a specific host.
     *
     * @param string $host The hostname to check
     *
     * @return bool True if TLS is enabled for the host
     */
    public function isTlsEnabledForHost(string $host): bool
    {
        foreach ($this->getTls() as $tlsConfig) {
            if (in_array($host, $tlsConfig['hosts'] ?? [], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the TLS configuration for this ingress.
     *
     * @return array<int, array<string, mixed>> The TLS configuration
     */
    public function getTls(): array
    {
        return $this->spec['tls'] ?? [];
    }
}
