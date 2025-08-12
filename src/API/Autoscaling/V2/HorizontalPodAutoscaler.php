<?php

declare(strict_types=1);

namespace Kubernetes\API\Autoscaling\V2;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * HorizontalPodAutoscaler automatically scales the number of pods in a replication controller,
 * deployment, replica set or stateful set based on observed metrics.
 *
 * This is the v2 API version which supports multiple metric types including CPU, memory,
 * and custom metrics.
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
     * Add CPU utilization metric.
     *
     * @param int $targetPercentage Target CPU utilization percentage (1-100)
     *
     * @return self
     *
     * @throws \InvalidArgumentException If percentage is not between 1-100
     */
    public function addCpuMetric(int $targetPercentage): self
    {
        if ($targetPercentage < 1 || $targetPercentage > 100) {
            throw new \InvalidArgumentException('CPU target percentage must be between 1-100');
        }

        $this->spec['metrics'][] = [
            'type'     => 'Resource',
            'resource' => [
                'name'   => 'cpu',
                'target' => [
                    'type'               => 'Utilization',
                    'averageUtilization' => $targetPercentage,
                ],
            ],
        ];

        return $this;
    }

    /**
     * Add memory utilization metric.
     *
     * @param int $targetPercentage Target memory utilization percentage (1-100)
     *
     * @return self
     *
     * @throws \InvalidArgumentException If percentage is not between 1-100
     */
    public function addMemoryMetric(int $targetPercentage): self
    {
        if ($targetPercentage < 1 || $targetPercentage > 100) {
            throw new \InvalidArgumentException('Memory target percentage must be between 1-100');
        }

        $this->spec['metrics'][] = [
            'type'     => 'Resource',
            'resource' => [
                'name'   => 'memory',
                'target' => [
                    'type'               => 'Utilization',
                    'averageUtilization' => $targetPercentage,
                ],
            ],
        ];

        return $this;
    }

    /**
     * Add custom metric for scaling.
     *
     * @param string $metricName The name of the custom metric
     * @param string $targetType The type of target (Pods, Object, External)
     * @param string $targetValue The target value for the metric
     * @param array  $selector   Optional selector for the metric
     *
     * @return self
     */
    public function addCustomMetric(string $metricName, string $targetType, string $targetValue, array $selector = []): self
    {
        $metric = [
            'type' => $targetType,
            'pods' => [
                'metric' => ['name' => $metricName],
                'target' => [
                    'type'         => 'AverageValue',
                    'averageValue' => $targetValue,
                ],
            ],
        ];

        if (!empty($selector)) {
            $metric['pods']['metric']['selector'] = $selector;
        }

        // Adjust structure based on target type
        if ($targetType === 'Object') {
            $metric = [
                'type'   => 'Object',
                'object' => [
                    'metric' => ['name' => $metricName],
                    'target' => [
                        'type'  => 'Value',
                        'value' => $targetValue,
                    ],
                ],
            ];
        } elseif ($targetType === 'External') {
            $metric = [
                'type'     => 'External',
                'external' => [
                    'metric' => ['name' => $metricName],
                    'target' => [
                        'type'  => 'Value',
                        'value' => $targetValue,
                    ],
                ],
            ];
        }

        $this->spec['metrics'][] = $metric;

        return $this;
    }

    /**
     * Add a pods metric for scaling based on per-pod metrics.
     *
     * @param string $metricName  The name of the metric
     * @param string $targetValue The target average value per pod
     * @param array  $selector    Optional label selector for pods
     *
     * @return self
     */
    public function addPodsMetric(string $metricName, string $targetValue, array $selector = []): self
    {
        $metric = [
            'type' => 'Pods',
            'pods' => [
                'metric' => ['name' => $metricName],
                'target' => [
                    'type'         => 'AverageValue',
                    'averageValue' => $targetValue,
                ],
            ],
        ];

        if (!empty($selector)) {
            $metric['pods']['metric']['selector'] = [
                'matchLabels' => $selector,
            ];
        }

        $this->spec['metrics'][] = $metric;

        return $this;
    }

    /**
     * Add an object metric for scaling based on a single object's metric.
     *
     * @param string $metricName   The name of the metric
     * @param string $targetValue  The target value
     * @param string $objectKind   The kind of the described object
     * @param string $objectName   The name of the described object
     * @param string $objectApiVersion The API version of the described object
     *
     * @return self
     */
    public function addObjectMetric(
        string $metricName,
        string $targetValue,
        string $objectKind,
        string $objectName,
        string $objectApiVersion = 'v1'
    ): self {
        $this->spec['metrics'][] = [
            'type'   => 'Object',
            'object' => [
                'metric' => ['name' => $metricName],
                'target' => [
                    'type'  => 'Value',
                    'value' => $targetValue,
                ],
                'describedObject' => [
                    'kind'       => $objectKind,
                    'name'       => $objectName,
                    'apiVersion' => $objectApiVersion,
                ],
            ],
        ];

        return $this;
    }

    /**
     * Add an external metric for scaling based on metrics from external systems.
     *
     * @param string $metricName  The name of the external metric
     * @param string $targetValue The target value
     * @param array  $selector    Optional selector for the metric
     *
     * @return self
     */
    public function addExternalMetric(string $metricName, string $targetValue, array $selector = []): self
    {
        $metric = [
            'type'     => 'External',
            'external' => [
                'metric' => ['name' => $metricName],
                'target' => [
                    'type'  => 'Value',
                    'value' => $targetValue,
                ],
            ],
        ];

        if (!empty($selector)) {
            $metric['external']['metric']['selector'] = [
                'matchLabels' => $selector,
            ];
        }

        $this->spec['metrics'][] = $metric;

        return $this;
    }

    /**
     * Set scaling behavior configuration.
     *
     * @param array $behavior Scaling behavior configuration
     *
     * @return self
     */
    public function setBehavior(array $behavior): self
    {
        $this->spec['behavior'] = $behavior;

        return $this;
    }

    /**
     * Set scale-up behavior.
     *
     * @param int $stabilizationWindowSeconds Stabilization window for scale up
     * @param int $maxReplicasToAdd           Maximum replicas to add per scaling event
     * @param int $periodSeconds              Period for scaling decisions
     *
     * @return self
     */
    public function setScaleUpBehavior(
        int $stabilizationWindowSeconds = 0,
        int $maxReplicasToAdd = 4,
        int $periodSeconds = 60
    ): self {
        $this->spec['behavior']['scaleUp'] = [
            'stabilizationWindowSeconds' => $stabilizationWindowSeconds,
            'policies'                   => [
                [
                    'type'          => 'Pods',
                    'value'         => $maxReplicasToAdd,
                    'periodSeconds' => $periodSeconds,
                ],
            ],
        ];

        return $this;
    }

    /**
     * Set scale-down behavior.
     *
     * @param int $stabilizationWindowSeconds Stabilization window for scale down
     * @param int $maxReplicasToRemove        Maximum replicas to remove per scaling event
     * @param int $periodSeconds              Period for scaling decisions
     *
     * @return self
     */
    public function setScaleDownBehavior(
        int $stabilizationWindowSeconds = 300,
        int $maxReplicasToRemove = 1,
        int $periodSeconds = 60
    ): self {
        $this->spec['behavior']['scaleDown'] = [
            'stabilizationWindowSeconds' => $stabilizationWindowSeconds,
            'policies'                   => [
                [
                    'type'          => 'Pods',
                    'value'         => $maxReplicasToRemove,
                    'periodSeconds' => $periodSeconds,
                ],
            ],
        ];

        return $this;
    }

    /**
     * Configure comprehensive autoscaling with CPU and memory metrics.
     *
     * @param string $targetKind     The kind of resource to scale
     * @param string $targetName     The name of the resource to scale
     * @param int    $minReplicas    Minimum replicas
     * @param int    $maxReplicas    Maximum replicas
     * @param int    $cpuPercentage  Target CPU utilization percentage
     * @param int    $memoryPercentage Target memory utilization percentage
     *
     * @return self
     */
    public function configureResourceScaling(
        string $targetKind,
        string $targetName,
        int $minReplicas,
        int $maxReplicas,
        int $cpuPercentage,
        int $memoryPercentage
    ): self {
        return $this
            ->setTargetRef($targetKind, $targetName)
            ->setMinReplicas($minReplicas)
            ->setMaxReplicas($maxReplicas)
            ->addCpuMetric($cpuPercentage)
            ->addMemoryMetric($memoryPercentage);
    }

    /**
     * Get all configured metrics.
     *
     * @return array
     */
    public function getMetrics(): array
    {
        return $this->spec['metrics'] ?? [];
    }

    /**
     * Get scaling behavior configuration.
     *
     * @return array
     */
    public function getBehavior(): array
    {
        return $this->spec['behavior'] ?? [];
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
     * Get current metrics from the HPA status.
     *
     * @return array
     */
    public function getCurrentMetrics(): array
    {
        return $this->status['currentMetrics'] ?? [];
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
     * Check if HPA is scaling limited.
     *
     * @return bool
     */
    public function isScalingLimited(): bool
    {
        $conditions = $this->getConditions();
        foreach ($conditions as $condition) {
            if ($condition['type'] === 'ScalingLimited') {
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
