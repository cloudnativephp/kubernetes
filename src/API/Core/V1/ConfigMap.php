<?php

declare(strict_types=1);

namespace Kubernetes\API\Core\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * Represents a Kubernetes ConfigMap resource.
 *
 * @link https://kubernetes.io/docs/concepts/configuration/configmap/
 */
class ConfigMap extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of the resource.
     *
     * @return string The resource kind
     */
    public function getKind(): string
    {
        return 'ConfigMap';
    }

    /**
     * Get the data stored in the ConfigMap.
     *
     * @return array<string, string> The ConfigMap data
     */
    public function getData(): array
    {
        return $this->spec['data'] ?? [];
    }

    /**
     * Set the data for the ConfigMap.
     *
     * @param array<string, string> $data The ConfigMap data
     *
     * @return self
     */
    public function setData(array $data): self
    {
        $this->spec['data'] = $data;

        return $this;
    }

    /**
     * Add a key-value pair to the ConfigMap data.
     *
     * @param string $key   The data key
     * @param string $value The data value
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
     * Get binary data stored in the ConfigMap.
     *
     * @return array<string, string> The ConfigMap binary data
     */
    public function getBinaryData(): array
    {
        return $this->spec['binaryData'] ?? [];
    }

    /**
     * Set binary data for the ConfigMap.
     *
     * @param array<string, string> $binaryData The ConfigMap binary data
     *
     * @return self
     */
    public function setBinaryData(array $binaryData): self
    {
        $this->spec['binaryData'] = $binaryData;

        return $this;
    }

    /**
     * Check if the ConfigMap is immutable.
     *
     * @return bool True if immutable, false otherwise
     */
    public function isImmutable(): bool
    {
        return $this->spec['immutable'] ?? false;
    }

    /**
     * Set the ConfigMap as immutable.
     *
     * @param bool $immutable Whether the ConfigMap should be immutable
     *
     * @return self
     */
    public function setImmutable(bool $immutable = true): self
    {
        $this->spec['immutable'] = $immutable;

        return $this;
    }
}
