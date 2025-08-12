<?php

declare(strict_types=1);

namespace Kubernetes\API\NodeK8sIo\V1;

/**
 * RuntimeClass defines a class of container runtime supported in the cluster.
 *
 * @link https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.30/#runtimeclass-v1-node-k8s-io
 */
class RuntimeClass extends AbstractAbstractResource
{
    /**
     * Get the resource kind.
     *
     * @return string
     */
    public function getKind(): string
    {
        return 'RuntimeClass';
    }

    /**
     * Set the handler for this RuntimeClass.
     *
     * @param string $handler The name of the underlying runtime and configuration
     *
     * @return self
     */
    public function setHandler(string $handler): self
    {
        $this->spec['handler'] = $handler;
        return $this;
    }

    /**
     * Get the handler for this RuntimeClass.
     *
     * @return string|null
     */
    public function getHandler(): ?string
    {
        return $this->spec['handler'] ?? null;
    }

    /**
     * Set scheduling constraints for pods using this RuntimeClass.
     *
     * @param array<string, mixed> $scheduling Scheduling configuration
     *
     * @return self
     */
    public function setScheduling(array $scheduling): self
    {
        $this->spec['scheduling'] = $scheduling;
        return $this;
    }

    /**
     * Get scheduling constraints.
     *
     * @return array<string, mixed>|null
     */
    public function getScheduling(): ?array
    {
        return $this->spec['scheduling'] ?? null;
    }

    /**
     * Add node selector term to scheduling constraints.
     *
     * @param array<string, string> $nodeSelector Node selector labels
     *
     * @return self
     */
    public function addNodeSelector(array $nodeSelector): self
    {
        if (!isset($this->spec['scheduling']['nodeSelector'])) {
            $this->spec['scheduling']['nodeSelector'] = [];
        }
        $this->spec['scheduling']['nodeSelector'] = array_merge(
            $this->spec['scheduling']['nodeSelector'],
            $nodeSelector
        );
        return $this;
    }

    /**
     * Add toleration to scheduling constraints.
     *
     * @param array<string, mixed> $toleration Toleration specification
     *
     * @return self
     */
    public function addToleration(array $toleration): self
    {
        if (!isset($this->spec['scheduling']['tolerations'])) {
            $this->spec['scheduling']['tolerations'] = [];
        }
        $this->spec['scheduling']['tolerations'][] = $toleration;
        return $this;
    }

    /**
     * Create a toleration configuration.
     *
     * @param string      $key               Taint key
     * @param string      $operator          Equal or Exists
     * @param string|null $value             Taint value (for Equal operator)
     * @param string|null $effect            NoSchedule, PreferNoSchedule, or NoExecute
     * @param int|null    $tolerationSeconds Grace period for NoExecute effect
     *
     * @return array<string, mixed>
     */
    public function createToleration(
        string $key,
        string $operator = 'Equal',
        ?string $value = null,
        ?string $effect = null,
        ?int $tolerationSeconds = null
    ): array {
        $toleration = [
            'key'      => $key,
            'operator' => $operator,
        ];

        if ($value !== null) {
            $toleration['value'] = $value;
        }

        if ($effect !== null) {
            $toleration['effect'] = $effect;
        }

        if ($tolerationSeconds !== null) {
            $toleration['tolerationSeconds'] = $tolerationSeconds;
        }

        return $toleration;
    }

    /**
     * Set overhead configuration for pods using this RuntimeClass.
     *
     * @param array<string, string> $overhead Resource overhead
     *
     * @return self
     */
    public function setOverhead(array $overhead): self
    {
        $this->spec['overhead']['podFixed'] = $overhead;
        return $this;
    }

    /**
     * Get overhead configuration.
     *
     * @return array<string, string>|null
     */
    public function getOverhead(): ?array
    {
        return $this->spec['overhead']['podFixed'] ?? null;
    }

    /**
     * Set CPU overhead.
     *
     * @param string $cpu CPU overhead (e.g., "100m")
     *
     * @return self
     */
    public function setCpuOverhead(string $cpu): self
    {
        if (!isset($this->spec['overhead']['podFixed'])) {
            $this->spec['overhead']['podFixed'] = [];
        }
        $this->spec['overhead']['podFixed']['cpu'] = $cpu;
        return $this;
    }

    /**
     * Set memory overhead.
     *
     * @param string $memory Memory overhead (e.g., "128Mi")
     *
     * @return self
     */
    public function setMemoryOverhead(string $memory): self
    {
        if (!isset($this->spec['overhead']['podFixed'])) {
            $this->spec['overhead']['podFixed'] = [];
        }
        $this->spec['overhead']['podFixed']['memory'] = $memory;
        return $this;
    }
}
