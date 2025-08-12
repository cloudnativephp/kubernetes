<?php

declare(strict_types=1);

namespace Kubernetes\API\AuthenticationK8sIo\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * TokenRequest requests a token for a given service account.
 *
 * TokenRequest is used to request bound service account tokens for
 * authentication to the Kubernetes API or external services.
 *
 * @link https://kubernetes.io/docs/reference/kubernetes-api/authentication-resources/token-request-v1/
 */
class TokenRequest extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of this resource.
     *
     * @return string
     */
    public function getKind(): string
    {
        return 'TokenRequest';
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
     * Add an audience to the token.
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
     * Get the expiration seconds.
     *
     * @return int|null
     */
    public function getExpirationSeconds(): ?int
    {
        return $this->spec['expirationSeconds'] ?? null;
    }

    /**
     * Get the bound object reference.
     *
     * @return array<string, string>|null
     */
    public function getBoundObjectRef(): ?array
    {
        return $this->spec['boundObjectRef'] ?? null;
    }

    /**
     * Get the token from the status.
     *
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->status['token'] ?? null;
    }

    /**
     * Get the token expiration time.
     *
     * @return string|null
     */
    public function getExpirationTimestamp(): ?string
    {
        return $this->status['expirationTimestamp'] ?? null;
    }

    /**
     * Helper method to create a simple token request.
     *
     * @param array<string> $audiences         Token audiences
     * @param int           $expirationSeconds Token validity period
     *
     * @return self
     */
    public function createSimpleTokenRequest(array $audiences, int $expirationSeconds = 3600): self
    {
        return $this
            ->setAudiences($audiences)
            ->setExpirationSeconds($expirationSeconds);
    }

    /**
     * Set the expiration seconds for the token.
     *
     * @param int $seconds Number of seconds the token will be valid
     *
     * @return self
     */
    public function setExpirationSeconds(int $seconds): self
    {
        $this->spec['expirationSeconds'] = $seconds;
        return $this;
    }

    /**
     * Set the audience for the token.
     *
     * @param array<string> $audiences List of the identifiers that the token can be used for
     *
     * @return self
     */
    public function setAudiences(array $audiences): self
    {
        $this->spec['audiences'] = $audiences;
        return $this;
    }

    /**
     * Helper method to create a pod-bound token request.
     *
     * @param string        $podName           Name of the pod to bind to
     * @param array<string> $audiences         Token audiences
     * @param int           $expirationSeconds Token validity period
     *
     * @return self
     */
    public function createPodBoundTokenRequest(string $podName, array $audiences, int $expirationSeconds = 3600): self
    {
        return $this
            ->setAudiences($audiences)
            ->setExpirationSeconds($expirationSeconds)
            ->setBoundObjectRef('Pod', $podName, 'v1');
    }

    /**
     * Set the bound object reference.
     *
     * @param string      $kind       Kind of the bound object
     * @param string      $name       Name of the bound object
     * @param string|null $apiVersion API version of the bound object
     * @param string|null $uid        UID of the bound object
     *
     * @return self
     */
    public function setBoundObjectRef(string $kind, string $name, ?string $apiVersion = null, ?string $uid = null): self
    {
        $boundObject = [
            'kind' => $kind,
            'name' => $name,
        ];

        if ($apiVersion !== null) {
            $boundObject['apiVersion'] = $apiVersion;
        }

        if ($uid !== null) {
            $boundObject['uid'] = $uid;
        }

        $this->spec['boundObjectRef'] = $boundObject;
        return $this;
    }
}
