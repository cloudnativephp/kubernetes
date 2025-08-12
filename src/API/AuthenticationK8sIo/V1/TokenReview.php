<?php

declare(strict_types=1);

namespace Kubernetes\API\AuthenticationK8sIo\V1;

/**
 * TokenReview attempts to authenticate a token to a known user.
 *
 * TokenReview is used by webhook token authenticators to validate
 * bearer tokens and return user information if the token is valid.
 *
 * @link https://kubernetes.io/docs/reference/kubernetes-api/authentication-resources/token-review-v1/
 */
class TokenReview extends AbstractAbstractResource
{
    /**
     * Get the kind of this resource.
     *
     * @return string
     */
    public function getKind(): string
    {
        return 'TokenReview';
    }

    /**
     * Get the token being reviewed.
     *
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->spec['token'] ?? null;
    }

    /**
     * Get the token audiences.
     *
     * @return array<string>
     */
    public function getAudiences(): array
    {
        return $this->spec['audiences'] ?? [];
    }

    /**
     * Add an audience for token validation.
     *
     * @param string $audience The audience identifier
     *
     * @return self
     */
    public function addAudience(string $audience): self
    {
        $this->spec['audiences'][] = $audience;
        return $this;
    }

    /**
     * Check if the token was authenticated successfully.
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return $this->status['authenticated'] ?? false;
    }

    /**
     * Get the username of the authenticated user.
     *
     * @return string|null
     */
    public function getUsername(): ?string
    {
        $user = $this->getUser();
        return $user['username'] ?? null;
    }

    /**
     * Get the authenticated user information.
     *
     * @return array<string, mixed>|null
     */
    public function getUser(): ?array
    {
        return $this->status['user'] ?? null;
    }

    /**
     * Get the UID of the authenticated user.
     *
     * @return string|null
     */
    public function getUserUid(): ?string
    {
        $user = $this->getUser();
        return $user['uid'] ?? null;
    }

    /**
     * Get the extra attributes of the authenticated user.
     *
     * @return array<string, array<string>>
     */
    public function getUserExtra(): array
    {
        $user = $this->getUser();
        return $user['extra'] ?? [];
    }

    /**
     * Get the authentication error message.
     *
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->status['error'] ?? null;
    }

    /**
     * Get the audiences that were used for validation.
     *
     * @return array<string>
     */
    public function getValidatedAudiences(): array
    {
        return $this->status['audiences'] ?? [];
    }

    /**
     * Helper method to create a token review request.
     *
     * @param string        $token     Token to validate
     * @param array<string> $audiences Required audiences
     *
     * @return self
     */
    public function createTokenReview(string $token, array $audiences = []): self
    {
        $this->setToken($token);

        if (!empty($audiences)) {
            $this->setAudiences($audiences);
        }

        return $this;
    }

    /**
     * Set the token to be reviewed.
     *
     * @param string $token Bearer token to validate
     *
     * @return self
     */
    public function setToken(string $token): self
    {
        $this->spec['token'] = $token;
        return $this;
    }

    /**
     * Set the audiences for token validation.
     *
     * @param array<string> $audiences List of audiences the token must be valid for
     *
     * @return self
     */
    public function setAudiences(array $audiences): self
    {
        $this->spec['audiences'] = $audiences;
        return $this;
    }

    /**
     * Check if the user has a specific group membership.
     *
     * @param string $group Group name to check
     *
     * @return bool
     */
    public function userHasGroup(string $group): bool
    {
        return in_array($group, $this->getUserGroups(), true);
    }

    /**
     * Get the groups of the authenticated user.
     *
     * @return array<string>
     */
    public function getUserGroups(): array
    {
        $user = $this->getUser();
        return $user['groups'] ?? [];
    }

    /**
     * Check if the user has any of the specified groups.
     *
     * @param array<string> $groups Groups to check
     *
     * @return bool
     */
    public function userHasAnyGroup(array $groups): bool
    {
        $userGroups = $this->getUserGroups();
        return !empty(array_intersect($groups, $userGroups));
    }
}
