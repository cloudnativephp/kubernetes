<?php

declare(strict_types=1);

namespace Kubernetes\API\RbacAuthorizationK8sIo\V1;

/**
 * Represents a Kubernetes ClusterRoleBinding resource.
 *
 * A ClusterRoleBinding grants the permissions defined in a ClusterRole to a user or set of users
 * across the entire cluster. Unlike RoleBinding, ClusterRoleBinding is cluster-scoped.
 *
 * @see https://kubernetes.io/docs/reference/access-authn-authz/rbac/#rolebinding-and-clusterrolebinding
 */
class ClusterRoleBinding extends AbstractAbstractResource
{
    /**
     * Get the kind of the resource.
     *
     * @return string The resource kind (ClusterRoleBinding)
     */
    public function getKind(): string
    {
        return 'ClusterRoleBinding';
    }

    /**
     * Get the subjects this cluster role binding applies to.
     *
     * @return array<int, array<string, mixed>> The subjects
     */
    public function getSubjects(): array
    {
        return $this->spec['subjects'] ?? [];
    }

    /**
     * Set the subjects this cluster role binding applies to.
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
     * Add a group subject to this cluster role binding.
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
     * Add a subject to this cluster role binding.
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
     * Create a cluster admin binding for a user.
     *
     * @param string $userName The user name
     *
     * @return self
     */
    public function createClusterAdminBinding(string $userName): self
    {
        return $this
            ->bindToClusterRole('cluster-admin')
            ->addUser($userName);
    }

    /**
     * Add a user subject to this cluster role binding.
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
     * Create a cluster reader binding for a user.
     *
     * @param string $userName The user name
     *
     * @return self
     */
    public function createClusterReaderBinding(string $userName): self
    {
        return $this
            ->bindToClusterRole('view')
            ->addUser($userName);
    }

    /**
     * Create a system binding for service accounts.
     *
     * @param string $systemRole              The system role name (e.g., 'system:node')
     * @param string $serviceAccountName      The service account name
     * @param string $serviceAccountNamespace The service account namespace
     *
     * @return self
     */
    public function createSystemBinding(
        string $systemRole,
        string $serviceAccountName,
        string $serviceAccountNamespace
    ): self {
        return $this
            ->bindToClusterRole($systemRole)
            ->addServiceAccount($serviceAccountName, $serviceAccountNamespace);
    }

    /**
     * Add a service account subject to this cluster role binding.
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
}
