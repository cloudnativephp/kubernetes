<?php

declare(strict_types=1);

namespace Kubernetes\API\RbacAuthorizationK8sIo\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * Represents a Kubernetes Role resource.
 *
 * A Role contains rules that represent a set of permissions within a namespace.
 *
 * @see https://kubernetes.io/docs/reference/access-authn-authz/rbac/#role-and-clusterrole
 */
class Role extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of the resource.
     *
     * @return string The resource kind (Role)
     */
    public function getKind(): string
    {
        return 'Role';
    }

    /**
     * Get the policy rules for this role.
     *
     * @return array<int, array<string, mixed>> The policy rules
     */
    public function getRules(): array
    {
        return $this->spec['rules'] ?? [];
    }

    /**
     * Set the policy rules for this role.
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
     * Add a policy rule to this role.
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
}
