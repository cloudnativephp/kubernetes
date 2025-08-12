<?php

declare(strict_types=1);

namespace Kubernetes\API\Core\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * Represents a Kubernetes ServiceAccount resource.
 *
 * A ServiceAccount provides an identity for processes that run in a Pod.
 *
 * @see https://kubernetes.io/docs/concepts/security/service-accounts/
 */
class ServiceAccount extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of the resource.
     *
     * @return string The resource kind (ServiceAccount)
     */
    public function getKind(): string
    {
        return 'ServiceAccount';
    }

    /**
     * Get the secrets.
     *
     * @return array<int, array<string, string>> The secrets
     */
    public function getSecrets(): array
    {
        return $this->spec['secrets'] ?? [];
    }

    /**
     * Set the secrets.
     *
     * @param array<int, array<string, string>> $secrets The secrets
     *
     * @return self
     */
    public function setSecrets(array $secrets): self
    {
        $this->spec['secrets'] = $secrets;

        return $this;
    }

    /**
     * Add a secret reference.
     *
     * @param string $name The secret name
     *
     * @return self
     */
    public function addSecret(string $name): self
    {
        if (!isset($this->spec['secrets'])) {
            $this->spec['secrets'] = [];
        }

        $this->spec['secrets'][] = ['name' => $name];

        return $this;
    }

    /**
     * Get the image pull secrets.
     *
     * @return array<int, array<string, string>> The image pull secrets
     */
    public function getImagePullSecrets(): array
    {
        return $this->spec['imagePullSecrets'] ?? [];
    }

    /**
     * Set the image pull secrets.
     *
     * @param array<int, array<string, string>> $imagePullSecrets The image pull secrets
     *
     * @return self
     */
    public function setImagePullSecrets(array $imagePullSecrets): self
    {
        $this->spec['imagePullSecrets'] = $imagePullSecrets;

        return $this;
    }

    /**
     * Add an image pull secret reference.
     *
     * @param string $name The image pull secret name
     *
     * @return self
     */
    public function addImagePullSecret(string $name): self
    {
        if (!isset($this->spec['imagePullSecrets'])) {
            $this->spec['imagePullSecrets'] = [];
        }

        $this->spec['imagePullSecrets'][] = ['name' => $name];

        return $this;
    }

    /**
     * Check if token auto mount is enabled.
     *
     * @return bool|null True if auto mount is enabled, null if not set
     */
    public function getAutomountServiceAccountToken(): ?bool
    {
        return $this->spec['automountServiceAccountToken'] ?? null;
    }

    /**
     * Set token auto mount.
     *
     * @param bool $automount Whether to auto mount the service account token
     *
     * @return self
     */
    public function setAutomountServiceAccountToken(bool $automount): self
    {
        $this->spec['automountServiceAccountToken'] = $automount;

        return $this;
    }
}
