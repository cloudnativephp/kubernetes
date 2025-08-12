<?php

declare(strict_types=1);

namespace Kubernetes\API\AuthorizationK8sIo\V1;

/**
 * SelfSubjectAccessReview checks whether the current user can perform an action.
 *
 * SelfSubjectAccessReview allows users to check their own permissions
 * without needing to specify user details explicitly.
 *
 * @link https://kubernetes.io/docs/reference/kubernetes-api/authorization-resources/self-subject-access-review-v1/
 */
class SelfSubjectAccessReview extends AbstractAbstractResource
{
    /**
     * Get the kind of this resource.
     *
     * @return string
     */
    public function getKind(): string
    {
        return 'SelfSubjectAccessReview';
    }

    /**
     * Get the resource attributes.
     *
     * @return array<string, string>|null
     */
    public function getResourceAttributes(): ?array
    {
        return $this->spec['resourceAttributes'] ?? null;
    }

    /**
     * Get the non-resource attributes.
     *
     * @return array<string, string>|null
     */
    public function getNonResourceAttributes(): ?array
    {
        return $this->spec['nonResourceAttributes'] ?? null;
    }

    /**
     * Check if the access is allowed.
     *
     * @return bool
     */
    public function isAllowed(): bool
    {
        return $this->status['allowed'] ?? false;
    }

    /**
     * Check if the access is denied.
     *
     * @return bool
     */
    public function isDenied(): bool
    {
        return $this->status['denied'] ?? false;
    }

    /**
     * Get the reason for the authorization decision.
     *
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->status['reason'] ?? null;
    }

    /**
     * Get the evaluation error if any.
     *
     * @return string|null
     */
    public function getEvaluationError(): ?string
    {
        return $this->status['evaluationError'] ?? null;
    }

    /**
     * Helper method to check non-resource URL access for current user.
     *
     * @param string $path URL path to check
     * @param string $verb HTTP verb
     *
     * @return self
     */
    public function checkNonResourceAccess(string $path, string $verb = 'get'): self
    {
        return $this->setNonResourceAttributes($path, $verb);
    }

    /**
     * Set the non-resource attributes for authorization check.
     *
     * @param string $path URL path to check
     * @param string $verb HTTP verb (get, post, etc.)
     *
     * @return self
     */
    public function setNonResourceAttributes(string $path, string $verb = 'get'): self
    {
        $this->spec['nonResourceAttributes'] = [
            'path' => $path,
            'verb' => $verb,
        ];
        return $this;
    }

    /**
     * Helper method to check if current user can create pods in a namespace.
     *
     * @param string $namespace Target namespace
     *
     * @return self
     */
    public function canCreatePods(string $namespace): self
    {
        return $this->checkResourceAccess('create', 'pods', $namespace);
    }

    /**
     * Helper method to check resource access for current user.
     *
     * @param string      $verb      Action to check
     * @param string      $resource  Resource type
     * @param string|null $namespace Namespace (if namespaced resource)
     * @param string|null $name      Specific resource name
     *
     * @return self
     */
    public function checkResourceAccess(
        string $verb,
        string $resource,
        ?string $namespace = null,
        ?string $name = null
    ): self {
        return $this->setResourceAttributes($verb, $resource, null, null, $namespace, $name);
    }

    /**
     * Set the resource attributes for authorization check.
     *
     * @param string      $verb        Action to check (get, list, create, update, delete, etc.)
     * @param string      $resource    Resource type (pods, services, etc.)
     * @param string|null $group       API group (empty for core group)
     * @param string|null $version     API version
     * @param string|null $namespace   Namespace for the resource
     * @param string|null $name        Specific resource name
     * @param string|null $subresource Subresource (status, scale, etc.)
     *
     * @return self
     */
    public function setResourceAttributes(
        string $verb,
        string $resource,
        ?string $group = null,
        ?string $version = null,
        ?string $namespace = null,
        ?string $name = null,
        ?string $subresource = null
    ): self {
        $attributes = [
            'verb'     => $verb,
            'resource' => $resource,
        ];

        if ($group !== null) {
            $attributes['group'] = $group;
        }

        if ($version !== null) {
            $attributes['version'] = $version;
        }

        if ($namespace !== null) {
            $attributes['namespace'] = $namespace;
        }

        if ($name !== null) {
            $attributes['name'] = $name;
        }

        if ($subresource !== null) {
            $attributes['subresource'] = $subresource;
        }

        $this->spec['resourceAttributes'] = $attributes;
        return $this;
    }

    /**
     * Helper method to check if current user can list secrets in a namespace.
     *
     * @param string $namespace Target namespace
     *
     * @return self
     */
    public function canListSecrets(string $namespace): self
    {
        return $this->checkResourceAccess('list', 'secrets', $namespace);
    }

    /**
     * Helper method to check if current user can delete deployments in a namespace.
     *
     * @param string $namespace Target namespace
     *
     * @return self
     */
    public function canDeleteDeployments(string $namespace): self
    {
        return $this->checkResourceAccess('delete', 'deployments', $namespace);
    }

    /**
     * Helper method to check if current user can access cluster nodes.
     *
     * @param string $verb Action to check (get, list, etc.)
     *
     * @return self
     */
    public function canAccessNodes(string $verb = 'list'): self
    {
        return $this->checkClusterAccess($verb, 'nodes');
    }

    /**
     * Helper method to check cluster-level access for current user.
     *
     * @param string $verb     Action to check
     * @param string $resource Resource type
     *
     * @return self
     */
    public function checkClusterAccess(string $verb, string $resource): self
    {
        return $this->setResourceAttributes($verb, $resource);
    }
}
