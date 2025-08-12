<?php

declare(strict_types=1);

namespace Kubernetes\API\AuthorizationK8sIo\V1;

/**
 * SubjectAccessReview checks whether a user or service account has authorization
 * to perform a specific action.
 *
 * SubjectAccessReview is used to determine if a subject (user, group, or service account)
 * can perform a specific action on a resource within a namespace.
 *
 * @link https://kubernetes.io/docs/reference/kubernetes-api/authorization-resources/subject-access-review-v1/
 */
class SubjectAccessReview extends AbstractAbstractResource
{
    /**
     * Get the kind of this resource.
     *
     * @return string
     */
    public function getKind(): string
    {
        return 'SubjectAccessReview';
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
     * Get the user being checked.
     *
     * @return array<string, mixed>|null
     */
    public function getUser(): ?array
    {
        return $this->spec['user'] ?? null;
    }

    /**
     * Set additional groups for the authorization check.
     *
     * @param array<string> $groups Additional groups
     *
     * @return self
     */
    public function setGroups(array $groups): self
    {
        $this->spec['groups'] = $groups;
        return $this;
    }

    /**
     * Get the additional groups.
     *
     * @return array<string>
     */
    public function getGroups(): array
    {
        return $this->spec['groups'] ?? [];
    }

    /**
     * Set extra attributes for the authorization check.
     *
     * @param array<string, array<string>> $extra Extra attributes
     *
     * @return self
     */
    public function setExtra(array $extra): self
    {
        $this->spec['extra'] = $extra;
        return $this;
    }

    /**
     * Get the extra attributes.
     *
     * @return array<string, array<string>>
     */
    public function getExtra(): array
    {
        return $this->spec['extra'] ?? [];
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
     * Helper method to check resource access.
     *
     * @param string      $username  Username to check
     * @param string      $verb      Action to check
     * @param string      $resource  Resource type
     * @param string|null $namespace Namespace (if namespaced resource)
     * @param string|null $name      Specific resource name
     *
     * @return self
     */
    public function checkResourceAccess(
        string $username,
        string $verb,
        string $resource,
        ?string $namespace = null,
        ?string $name = null
    ): self {
        return $this
            ->setUser($username)
            ->setResourceAttributes($verb, $resource, null, null, $namespace, $name);
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
     * Set the user to check authorization for.
     *
     * @param string                       $username Username
     * @param string|null                  $uid      User UID
     * @param array<string>                $groups   User groups
     * @param array<string, array<string>> $extra    Extra user attributes
     *
     * @return self
     */
    public function setUser(string $username, ?string $uid = null, array $groups = [], array $extra = []): self
    {
        $user = ['username' => $username];

        if ($uid !== null) {
            $user['uid'] = $uid;
        }

        if (!empty($groups)) {
            $user['groups'] = $groups;
        }

        if (!empty($extra)) {
            $user['extra'] = $extra;
        }

        $this->spec['user'] = $user;
        return $this;
    }

    /**
     * Helper method to check cluster-level access.
     *
     * @param string $username Username to check
     * @param string $verb     Action to check
     * @param string $resource Resource type
     *
     * @return self
     */
    public function checkClusterAccess(string $username, string $verb, string $resource): self
    {
        return $this
            ->setUser($username)
            ->setResourceAttributes($verb, $resource);
    }

    /**
     * Helper method to check non-resource URL access.
     *
     * @param string $username Username to check
     * @param string $path     URL path to check
     * @param string $verb     HTTP verb
     *
     * @return self
     */
    public function checkNonResourceAccess(string $username, string $path, string $verb = 'get'): self
    {
        return $this
            ->setUser($username)
            ->setNonResourceAttributes($path, $verb);
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
}
