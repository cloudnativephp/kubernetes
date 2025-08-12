<?php

declare(strict_types=1);

namespace Kubernetes\API\SchedulingK8sIo\V1;

/**
 * PriorityClass defines mapping from a priority class name to the priority integer value.
 *
 * @link https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.30/#priorityclass-v1-scheduling-k8s-io
 */
class PriorityClass extends AbstractAbstractResource
{
    /**
     * Get the resource kind.
     *
     * @return string
     */
    public function getKind(): string
    {
        return 'PriorityClass';
    }

    /**
     * Get the priority value.
     *
     * @return int|null
     */
    public function getValue(): ?int
    {
        return $this->spec['value'] ?? null;
    }

    /**
     * Set whether this priority class is global default.
     *
     * @param bool $globalDefault Whether this is the global default priority class
     *
     * @return self
     */
    public function setGlobalDefault(bool $globalDefault): self
    {
        $this->spec['globalDefault'] = $globalDefault;
        return $this;
    }

    /**
     * Get whether this is global default.
     *
     * @return bool
     */
    public function getGlobalDefault(): bool
    {
        return $this->spec['globalDefault'] ?? false;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->spec['description'] ?? null;
    }

    /**
     * Get preemption policy.
     *
     * @return string|null
     */
    public function getPreemptionPolicy(): ?string
    {
        return $this->spec['preemptionPolicy'] ?? null;
    }

    /**
     * Create a high priority class.
     *
     * @param string $name        Priority class name
     * @param int    $value       Priority value (e.g., 1000)
     * @param string $description Description
     *
     * @return self
     */
    public function createHighPriority(string $name, int $value, string $description): self
    {
        return $this
            ->setName($name)
            ->setValue($value)
            ->setDescription($description)
            ->setPreemptionPolicy('PreemptLowerPriority');
    }

    /**
     * Set preemption policy.
     *
     * @param string $policy PreemptLowerPriority or Never
     *
     * @return self
     */
    public function setPreemptionPolicy(string $policy): self
    {
        $this->spec['preemptionPolicy'] = $policy;
        return $this;
    }

    /**
     * Set description for this priority class.
     *
     * @param string $description Human-readable description
     *
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->spec['description'] = $description;
        return $this;
    }

    /**
     * Set the priority value.
     *
     * @param int $value Priority value (higher values indicate higher priority)
     *
     * @return self
     */
    public function setValue(int $value): self
    {
        $this->spec['value'] = $value;
        return $this;
    }

    /**
     * Create a low priority class.
     *
     * @param string $name        Priority class name
     * @param int    $value       Priority value (e.g., -10)
     * @param string $description Description
     *
     * @return self
     */
    public function createLowPriority(string $name, int $value, string $description): self
    {
        return $this
            ->setName($name)
            ->setValue($value)
            ->setDescription($description)
            ->setPreemptionPolicy('Never');
    }
}
