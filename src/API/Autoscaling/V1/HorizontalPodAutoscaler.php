<?php

declare(strict_types=1);

namespace Kubernetes\API\Autoscaling\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * HorizontalPodAutoscaler automatically scales the number of pods in a replication controller,
 * deployment, replica set or stateful set based on observed CPU utilization.
 *
 * This is the v1 API version which supports only CPU-based scaling.
 */
class HorizontalPodAutoscaler extends AbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of Kubernetes resource.
     *
     * @return string
     */
    public function getKind(): string
    {
        return 'HorizontalPodAutoscaler';
    }

    /**
     * Set the target reference for scaling.
     *
     * @param string $kind The kind of resource to scale (Deployment, ReplicaSet, etc.)
     * @param string $name The name of the resource to scale
     *
     * @return self
     */
    public function setTargetRef(string $kind, string $name): self
    {
        $this->spec['scaleTargetRef'] = [
            'kind'       => $kind,
            'name'       => $name,
            'apiVersion' => $this->getApiVersionForKind($kind),
        ];

        return $this;
    }

    /**
     * Get the target reference.
     *
     * @return array
     */
    public function getTargetRef(): array
    {
        return $this->spec['scaleTargetRef'] ?? [];
    }

    /**
     * Set the minimum number of replicas.
     *
     * @param int $minReplicas Minimum number of replicas (must be >= 1)
     *
     * @return self
     *
     * @throws \InvalidArgumentException If minReplicas is less than 1
     */
    public function setMinReplicas(int $minReplicas): self
    {
        if ($minReplicas < 1) {
            throw new \InvalidArgumentException('minReplicas must be at least 1');
        }

        $this->spec['minReplicas'] = $minReplicas;

        return $this;
    }

    /**
     * Get the minimum number of replicas.
     *
     * @return int
     */
    public function getMinReplicas(): int
    {
        return $this->spec['minReplicas'] ?? 1;
    }

    /**
     * Set the maximum number of replicas.
     *
     * @param int $maxReplicas Maximum number of replicas (must be >= minReplicas)
     *
     * @return self
     *
     * @throws \InvalidArgumentException If maxReplicas is invalid
     */
    public function setMaxReplicas(int $maxReplicas): self
    {
        if ($maxReplicas < 1) {
            throw new \InvalidArgumentException('maxReplicas must be at least 1');
        }

        $minReplicas = $this->getMinReplicas();
        if ($maxReplicas < $minReplicas) {
            throw new \InvalidArgumentException("maxReplicas ($maxReplicas) must be >= minReplicas ($minReplicas)");
        }

        $this->spec['maxReplicas'] = $maxReplicas;

        return $this;
    }

    /**
     * Get the maximum number of replicas.
     *
     * @return int
     */
    public function getMaxReplicas(): int
    {
        return $this->spec['maxReplicas'] ?? 1;
    }

    /**
     * Set the target CPU utilization percentage.
     *
     * @param int $targetCPUUtilizationPercentage Target CPU utilization (1-100)
     *
     * @return self
     *
     * @throws \InvalidArgumentException If percentage is not between 1-100
     */
    public function setTargetCPUUtilizationPercentage(int $targetCPUUtilizationPercentage): self
    {
        if ($targetCPUUtilizationPercentage < 1 || $targetCPUUtilizationPercentage > 100) {
            throw new \InvalidArgumentException('targetCPUUtilizationPercentage must be between 1-100');
        }

        $this->spec['targetCPUUtilizationPercentage'] = $targetCPUUtilizationPercentage;

        return $this;
    }

    /**
     * Get the target CPU utilization percentage.
     *
     * @return int|null
     */
    public function getTargetCPUUtilizationPercentage(): ?int
    {
        return $this->spec['targetCPUUtilizationPercentage'] ?? null;
    }

    /**
     * Configure basic CPU-based autoscaling.
     *
     * @param string $targetKind        The kind of resource to scale
     * @param string $targetName        The name of the resource to scale
     * @param int    $minReplicas       Minimum replicas
     * @param int    $maxReplicas       Maximum replicas
     * @param int    $cpuPercentage     Target CPU utilization percentage
     *
     * @return self
     */
    public function configureCPUScaling(
        string $targetKind,
        string $targetName,
        int $minReplicas,
        int $maxReplicas,
        int $cpuPercentage
    ): self {
        return $this
            ->setTargetRef($targetKind, $targetName)
            ->setMinReplicas($minReplicas)
            ->setMaxReplicas($maxReplicas)
            ->setTargetCPUUtilizationPercentage($cpuPercentage);
    }

    // Status methods (read-only)

    /**
     * Get the current number of replicas.
     *
     * @return int
     */
    public function getCurrentReplicas(): int
    {
        return $this->status['currentReplicas'] ?? 0;
    }

    /**
     * Get the desired number of replicas.
     *
     * @return int
     */
    public function getDesiredReplicas(): int
    {
        return $this->status['desiredReplicas'] ?? 0;
    }

    /**
     * Get the current CPU utilization percentage.
     *
     * @return int|null
     */
    public function getCurrentCPUUtilizationPercentage(): ?int
    {
        return $this->status['currentCPUUtilizationPercentage'] ?? null;
    }

    /**
     * Get the last scale time.
     *
     * @return string|null
     */
    public function getLastScaleTime(): ?string
    {
        return $this->status['lastScaleTime'] ?? null;
    }

    /**
     * Get HPA conditions.
     *
     * @return array
     */
    public function getConditions(): array
    {
        return $this->status['conditions'] ?? [];
    }

    /**
     * Check if HPA is able to scale.
     *
     * @return bool
     */
    public function canScale(): bool
    {
        $conditions = $this->getConditions();
        foreach ($conditions as $condition) {
            if ($condition['type'] === 'AbleToScale') {
                return $condition['status'] === 'True';
            }
        }

        return false;
    }

    /**
     * Check if scaling is active.
     *
     * @return bool
     */
    public function isScalingActive(): bool
    {
        $conditions = $this->getConditions();
        foreach ($conditions as $condition) {
            if ($condition['type'] === 'ScalingActive') {
                return $condition['status'] === 'True';
            }
        }

        return false;
    }

    /**
     * Get the appropriate API version for a given resource kind.
     *
     * @param string $kind The resource kind
     *
     * @return string
     */
    private function getApiVersionForKind(string $kind): string
    {
        return match ($kind) {
            'Deployment', 'ReplicaSet', 'StatefulSet', 'DaemonSet' => 'apps/v1',
            'ReplicationController' => 'v1',
            default                 => 'apps/v1',
        };
    }
}
