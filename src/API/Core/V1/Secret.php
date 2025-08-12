<?php

declare(strict_types=1);

namespace Kubernetes\API\Core\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * Represents a Kubernetes Secret resource.
 *
 * Secrets let you store and manage sensitive information, such as passwords, OAuth tokens, and SSH keys.
 *
 * @see https://kubernetes.io/docs/concepts/configuration/secret/
 */
class Secret extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of the resource.
     *
     * @return string The resource kind (Secret)
     */
    public function getKind(): string
    {
        return 'Secret';
    }

    /**
     * Get the secret type.
     *
     * @return string|null The secret type (Opaque, kubernetes.io/service-account-token, etc.)
     */
    public function getType(): ?string
    {
        return $this->spec['type'] ?? null;
    }

    /**
     * Set the secret type.
     *
     * @param string $type The secret type
     *
     * @return self
     */
    public function setType(string $type): self
    {
        $this->spec['type'] = $type;

        return $this;
    }

    /**
     * Get the secret data.
     *
     * @return array<string, string> The secret data (base64 encoded)
     */
    public function getData(): array
    {
        return $this->spec['data'] ?? [];
    }

    /**
     * Set the secret data.
     *
     * @param array<string, string> $data The secret data (base64 encoded)
     *
     * @return self
     */
    public function setData(array $data): self
    {
        $this->spec['data'] = $data;

        return $this;
    }

    /**
     * Get the secret string data (plain text).
     *
     * @return array<string, string> The secret string data
     */
    public function getStringData(): array
    {
        return $this->spec['stringData'] ?? [];
    }

    /**
     * Set the secret string data (plain text).
     *
     * @param array<string, string> $stringData The secret string data
     *
     * @return self
     */
    public function setStringData(array $stringData): self
    {
        $this->spec['stringData'] = $stringData;

        return $this;
    }

    /**
     * Add a data entry to the secret.
     *
     * @param string $key   The data key
     * @param string $value The data value (base64 encoded)
     *
     * @return self
     */
    public function addData(string $key, string $value): self
    {
        if (!isset($this->spec['data'])) {
            $this->spec['data'] = [];
        }

        $this->spec['data'][$key] = $value;

        return $this;
    }

    /**
     * Add a string data entry to the secret.
     *
     * @param string $key   The data key
     * @param string $value The data value (plain text)
     *
     * @return self
     */
    public function addStringData(string $key, string $value): self
    {
        if (!isset($this->spec['stringData'])) {
            $this->spec['stringData'] = [];
        }

        $this->spec['stringData'][$key] = $value;

        return $this;
    }

    /**
     * Check if the secret is immutable.
     *
     * @return bool True if the secret is immutable
     */
    public function isImmutable(): bool
    {
        return $this->spec['immutable'] ?? false;
    }

    /**
     * Set the secret as immutable.
     *
     * @param bool $immutable Whether the secret is immutable
     *
     * @return self
     */
    public function setImmutable(bool $immutable = true): self
    {
        $this->spec['immutable'] = $immutable;

        return $this;
    }
}
