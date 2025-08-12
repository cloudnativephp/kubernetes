<?php

declare(strict_types=1);

namespace Kubernetes\API\RbacAuthorizationK8sIo\V1;

/**
 * Represents a Kubernetes ClusterRole resource.
 *
 * A ClusterRole contains rules that represent a set of permissions at the cluster level.
 * Unlike Role, ClusterRole is cluster-scoped and can grant access to cluster-scoped resources.
 *
 * @see https://kubernetes.io/docs/reference/access-authn-authz/rbac/#role-and-clusterrole
 */
class ClusterRole extends AbstractAbstractResource
{
    /**
     * Get the kind of the resource.
     *
     * @return string The resource kind (ClusterRole)
     */
    public function getKind(): string
    {
        return 'ClusterRole';
    }

    /**
     * Get the policy rules for this cluster role.
     *
     * @return array<int, array<string, mixed>> The policy rules
     */
    public function getRules(): array
    {
        return $this->spec['rules'] ?? [];
    }

    /**
     * Set the policy rules for this cluster role.
     *
     * @param array<int, array<string, mixed>> $rules The policy rules
     *
     * @return self
     */
    public function setRules(array $rules): self
    {
        $this->spec['rules'] = $rules;

        return $this;
    }

    /**
     * Get the aggregation rule for this cluster role.
     *
     * @return array<string, mixed>|null The aggregation rule
     */
    public function getAggregationRule(): ?array
    {
        return $this->spec['aggregationRule'] ?? null;
    }

    /**
     * Add a rule for non-resource URLs.
     *
     * @param array<string> $nonResourceURLs The non-resource URLs
     * @param array<string> $verbs           The allowed verbs
     *
     * @return self
     */
    public function addNonResourceRule(array $nonResourceURLs, array $verbs): self
    {
        return $this->addRule([
            'nonResourceURLs' => $nonResourceURLs,
            'verbs'           => $verbs,
        ]);
    }

    /**
     * Add a policy rule to this cluster role.
     *
     * @param array<string, mixed> $rule The policy rule to add
     *
     * @return self
     */
    public function addRule(array $rule): self
    {
        $this->spec['rules'][] = $rule;

        return $this;
    }

    /**
     * Add a rule allowing all operations on core resources.
     *
     * @param array<string> $resources The core resources
     *
     * @return self
     */
    public function addCoreResourceRule(array $resources): self
    {
        return $this->addResourceRule(
            [''],
            $resources,
            ['get', 'list', 'watch', 'create', 'update', 'patch', 'delete']
        );
    }

    /**
     * Add a rule for specific resources and verbs.
     *
     * @param array<string>      $apiGroups     The API groups (empty string for core group)
     * @param array<string>      $resources     The resource types
     * @param array<string>      $verbs         The allowed verbs
     * @param array<string>|null $resourceNames Specific resource names (optional)
     *
     * @return self
     */
    public function addResourceRule(
        array $apiGroups,
        array $resources,
        array $verbs,
        ?array $resourceNames = null
    ): self {
        $rule = [
            'apiGroups' => $apiGroups,
            'resources' => $resources,
            'verbs'     => $verbs,
        ];

        if ($resourceNames !== null) {
            $rule['resourceNames'] = $resourceNames;
        }

        return $this->addRule($rule);
    }

    /**
     * Add a read-only rule for specific resources.
     *
     * @param array<string> $apiGroups The API groups
     * @param array<string> $resources The resource types
     *
     * @return self
     */
    public function addReadOnlyRule(array $apiGroups, array $resources): self
    {
        return $this->addResourceRule(
            $apiGroups,
            $resources,
            ['get', 'list', 'watch']
        );
    }

    /**
     * Add cluster admin permissions (all resources, all verbs).
     *
     * @return self
     */
    public function addClusterAdminRule(): self
    {
        return $this->addResourceRule(['*'], ['*'], ['*']);
    }

    /**
     * Add node management permissions.
     *
     * @return self
     */
    public function addNodeManagementRule(): self
    {
        return $this->addResourceRule(
            [''],
            ['nodes', 'nodes/status', 'nodes/proxy'],
            ['get', 'list', 'watch', 'create', 'update', 'patch', 'delete']
        );
    }

    /**
     * Add namespace management permissions.
     *
     * @return self
     */
    public function addNamespaceManagementRule(): self
    {
        return $this->addResourceRule(
            [''],
            ['namespaces', 'namespaces/status'],
            ['get', 'list', 'watch', 'create', 'update', 'patch', 'delete']
        );
    }

    /**
     * Enable aggregation from other cluster roles with specific labels.
     *
     * @param array<string, string> $matchLabels The labels to match for aggregation
     *
     * @return self
     */
    public function enableAggregation(array $matchLabels): self
    {
        return $this->setAggregationRule([
            'clusterRoleSelectors' => [
                [
                    'matchLabels' => $matchLabels,
                ],
            ],
        ]);
    }

    /**
     * Set the aggregation rule for this cluster role.
     *
     * @param array<string, mixed> $aggregationRule The aggregation rule
     *
     * @return self
     */
    public function setAggregationRule(array $aggregationRule): self
    {
        $this->spec['aggregationRule'] = $aggregationRule;

        return $this;
    }
}
