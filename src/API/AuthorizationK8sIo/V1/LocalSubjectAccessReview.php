<?php

declare(strict_types=1);

namespace Kubernetes\API\AuthorizationK8sIo\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * LocalSubjectAccessReview checks whether a user or service account has authorization
 * to perform a specific action within a particular namespace.
 *
 * LocalSubjectAccessReview is the namespaced variant of SubjectAccessReview,
 * automatically scoped to the namespace where the review is created.
 *
 * @link https://kubernetes.io/docs/reference/kubernetes-api/authorization-resources/local-subject-access-review-v1/
 */
class LocalSubjectAccessReview extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of this resource.
     *
     * @return string
     */
    public function getKind(): string
    {
        return 'LocalSubjectAccessReview';
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
     * Helper method to check if a user can manage pods in this namespace.
     *
     * @param string $username Username to check
     * @param string $verb     Action (get, list, create, update, delete)
     *
     * @return self
     */
    public function checkPodAccess(string $username, string $verb): self
    {
        return $this->checkResourceAccess($username, $verb, 'pods');
    }

    /**
     * Helper method to check resource access within the current namespace.
     *
     * @param string      $username Username to check
     * @param string      $verb     Action to check
     * @param string      $resource Resource type
     * @param string|null $name     Specific resource name
     *
     * @return self
     */
    public function checkResourceAccess(
        string $username,
        string $verb,
        string $resource,
        ?string $name = null
    ): self {
        return $this
            ->setUser($username)
            ->setResourceAttributes($verb, $resource, null, null, $name);
    }

    /**
     * Set the resource attributes for authorization check.
     *
     * @param string      $verb        Action to check (get, list, create, update, delete, etc.)
     * @param string      $resource    Resource type (pods, services, etc.)
     * @param string|null $group       API group (empty for core group)
     * @param string|null $version     API version
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
     * Helper method to check if a user can manage secrets in this namespace.
     *
     * @param string $username Username to check
     * @param string $verb     Action (get, list, create, update, delete)
     *
     * @return self
     */
    public function checkSecretAccess(string $username, string $verb): self
    {
        return $this->checkResourceAccess($username, $verb, 'secrets');
    }

    /**
     * Helper method to check if a user can manage services in this namespace.
     *
     * @param string $username Username to check
     * @param string $verb     Action (get, list, create, update, delete)
     *
     * @return self
     */
    public function checkServiceAccess(string $username, string $verb): self
    {
        return $this->checkResourceAccess($username, $verb, 'services');
    }
}
