<?php

declare(strict_types=1);

namespace Kubernetes\API\NetworkingK8sIo\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * Represents a Kubernetes NetworkPolicy resource.
 *
 * NetworkPolicies provide network segmentation by controlling traffic flow between pods.
 * They act as a firewall for pods, specifying which traffic is allowed.
 *
 * @see https://kubernetes.io/docs/concepts/services-networking/network-policies/
 */
class NetworkPolicy extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of the resource.
     *
     * @return string The resource kind (NetworkPolicy)
     */
    public function getKind(): string
    {
        return 'NetworkPolicy';
    }

    /**
     * Get the pod selector for this network policy.
     *
     * @return array<string, mixed>|null The pod selector
     */
    public function getPodSelector(): ?array
    {
        return $this->spec['podSelector'] ?? null;
    }

    /**
     * Get the ingress rules for this network policy.
     *
     * @return array<int, array<string, mixed>> The ingress rules
     */
    public function getIngressRules(): array
    {
        return $this->spec['ingress'] ?? [];
    }

    /**
     * Set the ingress rules for this network policy.
     *
     * @param array<int, array<string, mixed>> $ingressRules The ingress rules
     *
     * @return self
     */
    public function setIngressRules(array $ingressRules): self
    {
        $this->spec['ingress'] = $ingressRules;

        return $this;
    }

    /**
     * Get the egress rules for this network policy.
     *
     * @return array<int, array<string, mixed>> The egress rules
     */
    public function getEgressRules(): array
    {
        return $this->spec['egress'] ?? [];
    }

    /**
     * Set the egress rules for this network policy.
     *
     * @param array<int, array<string, mixed>> $egressRules The egress rules
     *
     * @return self
     */
    public function setEgressRules(array $egressRules): self
    {
        $this->spec['egress'] = $egressRules;

        return $this;
    }

    /**
     * Get the policy types for this network policy.
     *
     * @return array<string> The policy types
     */
    public function getPolicyTypes(): array
    {
        return $this->spec['policyTypes'] ?? [];
    }

    /**
     * Select all pods in the namespace.
     *
     * @return self
     */
    public function selectAllPods(): self
    {
        return $this->setPodSelector([]);
    }

    /**
     * Set the pod selector for this network policy.
     *
     * @param array<string, mixed> $podSelector The pod selector
     *
     * @return self
     */
    public function setPodSelector(array $podSelector): self
    {
        $this->spec['podSelector'] = $podSelector;

        return $this;
    }

    /**
     * Select pods by labels.
     *
     * @param array<string, string> $labels The labels to match
     *
     * @return self
     */
    public function selectPodsByLabels(array $labels): self
    {
        return $this->setPodSelector([
            'matchLabels' => $labels,
        ]);
    }

    /**
     * Allow ingress traffic from all pods in the same namespace.
     *
     * @param array<int, array<string, mixed>>|null $ports Optional port restrictions
     *
     * @return self
     */
    public function allowIngressFromNamespace(?array $ports = null): self
    {
        $rule = [
            'from' => [
                [
                    'namespaceSelector' => [],
                ],
            ],
        ];

        if ($ports !== null) {
            $rule['ports'] = $ports;
        }

        return $this->addIngressRule($rule);
    }

    /**
     * Add an ingress rule to this network policy.
     *
     * @param array<string, mixed> $ingressRule The ingress rule to add
     *
     * @return self
     */
    public function addIngressRule(array $ingressRule): self
    {
        $this->spec['ingress'][] = $ingressRule;

        return $this;
    }

    /**
     * Allow ingress traffic from pods with specific labels.
     *
     * @param array<string, string>                 $labels The pod labels to match
     * @param array<int, array<string, mixed>>|null $ports  Optional port restrictions
     *
     * @return self
     */
    public function allowIngressFromPods(array $labels, ?array $ports = null): self
    {
        $rule = [
            'from' => [
                [
                    'podSelector' => [
                        'matchLabels' => $labels,
                    ],
                ],
            ],
        ];

        if ($ports !== null) {
            $rule['ports'] = $ports;
        }

        return $this->addIngressRule($rule);
    }

    /**
     * Allow egress traffic to all destinations.
     *
     * @param array<int, array<string, mixed>>|null $ports Optional port restrictions
     *
     * @return self
     */
    public function allowEgressToAll(?array $ports = null): self
    {
        $rule = [];

        if ($ports !== null) {
            $rule['ports'] = $ports;
        }

        return $this->addEgressRule($rule);
    }

    /**
     * Add an egress rule to this network policy.
     *
     * @param array<string, mixed> $egressRule The egress rule to add
     *
     * @return self
     */
    public function addEgressRule(array $egressRule): self
    {
        $this->spec['egress'][] = $egressRule;

        return $this;
    }

    /**
     * Allow egress traffic to specific CIDR blocks.
     *
     * @param array<string>                         $cidrs The CIDR blocks to allow
     * @param array<int, array<string, mixed>>|null $ports Optional port restrictions
     *
     * @return self
     */
    public function allowEgressToCidrs(array $cidrs, ?array $ports = null): self
    {
        $ipBlocks = array_map(fn ($cidr) => ['ipBlock' => ['cidr' => $cidr]], $cidrs);

        $rule = [
            'to' => $ipBlocks,
        ];

        if ($ports !== null) {
            $rule['ports'] = $ports;
        }

        return $this->addEgressRule($rule);
    }

    /**
     * Create a port specification for network policy rules.
     *
     * @param string   $protocol The protocol (TCP, UDP, SCTP)
     * @param int|null $port     The port number (optional for protocols)
     *
     * @return array<string, mixed> The port specification
     */
    public function createPortSpec(string $protocol, ?int $port = null): array
    {
        $spec = ['protocol' => $protocol];

        if ($port !== null) {
            $spec['port'] = $port;
        }

        return $spec;
    }

    /**
     * Enable both ingress and egress policy enforcement.
     *
     * @return self
     */
    public function enableBothPolicyTypes(): self
    {
        return $this->setPolicyTypes(['Ingress', 'Egress']);
    }

    /**
     * Set the policy types for this network policy.
     *
     * @param array<string> $policyTypes The policy types (Ingress, Egress)
     *
     * @return self
     */
    public function setPolicyTypes(array $policyTypes): self
    {
        $this->spec['policyTypes'] = $policyTypes;

        return $this;
    }

    /**
     * Enable only ingress policy enforcement.
     *
     * @return self
     */
    public function enableIngressOnly(): self
    {
        return $this->setPolicyTypes(['Ingress']);
    }

    /**
     * Enable only egress policy enforcement.
     *
     * @return self
     */
    public function enableEgressOnly(): self
    {
        return $this->setPolicyTypes(['Egress']);
    }
}
