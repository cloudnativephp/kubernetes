<?php

declare(strict_types=1);

namespace Kubernetes\API\RbacAuthorizationK8sIo\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * Represents a Kubernetes RoleBinding resource.
 *
 * A RoleBinding grants the permissions defined in a role to a user or set of users within a namespace.
 *
 * @see https://kubernetes.io/docs/reference/access-authn-authz/rbac/#rolebinding-and-clusterrolebinding
 */
class RoleBinding extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of the resource.
     *
     * @return string The resource kind (RoleBinding)
     */
    public function getKind(): string
    {
        return 'RoleBinding';
    }

    /**
     * Get the subjects this role binding applies to.
     *
     * @return array<int, array<string, mixed>> The subjects
     */
    public function getSubjects(): array
    {
        return $this->spec['subjects'] ?? [];
    }

    /**
     * Set the subjects this role binding applies to.
     *
     * @param array<int, array<string, mixed>> $subjects The subjects
     *
     * @return self
     */
    public function setSubjects(array $subjects): self
    {
        $this->spec['subjects'] = $subjects;

        return $this;
    }

    /**
     * Get the role reference for this binding.
     *
     * @return array<string, mixed>|null The role reference
     */
    public function getRoleRef(): ?array
    {
        return $this->spec['roleRef'] ?? null;
    }

    /**
     * Add a user subject to this role binding.
     *
     * @param string      $name      The user name
     * @param string|null $namespace The user namespace (optional)
     *
     * @return self
     */
    public function addUser(string $name, ?string $namespace = null): self
    {
        $subject = [
            'kind' => 'User',
            'name' => $name,
        ];

        if ($namespace !== null) {
            $subject['namespace'] = $namespace;
        }

        return $this->addSubject($subject);
    }

    /**
     * Add a subject to this role binding.
     *
     * @param array<string, mixed> $subject The subject to add
     *
     * @return self
     */
    public function addSubject(array $subject): self
    {
        $this->spec['subjects'][] = $subject;

        return $this;
    }

    /**
     * Add a group subject to this role binding.
     *
     * @param string      $name      The group name
     * @param string|null $namespace The group namespace (optional)
     *
     * @return self
     */
    public function addGroup(string $name, ?string $namespace = null): self
    {
        $subject = [
            'kind' => 'Group',
            'name' => $name,
        ];

        if ($namespace !== null) {
            $subject['namespace'] = $namespace;
        }

        return $this->addSubject($subject);
    }

    /**
     * Add a service account subject to this role binding.
     *
     * @param string $name      The service account name
     * @param string $namespace The service account namespace
     *
     * @return self
     */
    public function addServiceAccount(string $name, string $namespace): self
    {
        return $this->addSubject([
            'kind'      => 'ServiceAccount',
            'name'      => $name,
            'namespace' => $namespace,
        ]);
    }

    /**
     * Set the role reference to bind to a Role.
     *
     * @param string $roleName The role name
     *
     * @return self
     */
    public function bindToRole(string $roleName): self
    {
        return $this->setRoleRef([
            'kind'     => 'Role',
            'name'     => $roleName,
            'apiGroup' => 'rbac.authorization.k8s.io',
        ]);
    }

    /**
     * Set the role reference for this binding.
     *
     * @param array<string, mixed> $roleRef The role reference
     *
     * @return self
     */
    public function setRoleRef(array $roleRef): self
    {
        $this->spec['roleRef'] = $roleRef;

        return $this;
    }

    /**
     * Set the role reference to bind to a ClusterRole.
     *
     * @param string $clusterRoleName The cluster role name
     *
     * @return self
     */
    public function bindToClusterRole(string $clusterRoleName): self
    {
        return $this->setRoleRef([
            'kind'     => 'ClusterRole',
            'name'     => $clusterRoleName,
            'apiGroup' => 'rbac.authorization.k8s.io',
        ]);
    }
}
