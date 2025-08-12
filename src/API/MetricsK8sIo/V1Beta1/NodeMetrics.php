<?php

declare(strict_types=1);

namespace Kubernetes\API\MetricsK8sIo\V1Beta1;

/**
 * NodeMetrics represents resource usage metrics for a node.
 *
 * @link https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.30/#nodemetrics-v1beta1-metrics-k8s-io
 */
class NodeMetrics extends AbstractAbstractResource
{
    /**
     * Get the resource kind.
     *
     * @return string
     */
    public function getKind(): string
    {
        return 'NodeMetrics';
    }

    /**
     * Set the timestamp when metrics were collected.
     *
     * @param string $timestamp ISO 8601 timestamp
     *
     * @return self
     */
    public function setTimestamp(string $timestamp): self
    {
        $this->spec['timestamp'] = $timestamp;
        return $this;
    }

    /**
     * Get the timestamp.
     *
     * @return string|null
     */
    public function getTimestamp(): ?string
    {
        return $this->spec['timestamp'] ?? null;
    }

    /**
     * Set the time window for metrics collection.
     *
     * @param string $window Duration (e.g., "1m")
     *
     * @return self
     */
    public function setWindow(string $window): self
    {
        $this->spec['window'] = $window;
        return $this;
    }

    /**
     * Get the time window.
     *
     * @return string|null
     */
    public function getWindow(): ?string
    {
        return $this->spec['window'] ?? null;
    }

    /**
     * Set resource usage metrics.
     *
     * @param array<string, string> $usage Resource usage (cpu, memory)
     *
     * @return self
     */
    public function setUsage(array $usage): self
    {
        $this->spec['usage'] = $usage;
        return $this;
    }

    /**
     * Get resource usage.
     *
     * @return array<string, string>
     */
    public function getUsage(): array
    {
        return $this->spec['usage'] ?? [];
    }

    /**
     * Set CPU usage.
     *
     * @param string $cpu CPU usage (e.g., "100m", "1.5")
     *
     * @return self
     */
    public function setCpuUsage(string $cpu): self
    {
        if (!isset($this->spec['usage'])) {
            $this->spec['usage'] = [];
        }
        $this->spec['usage']['cpu'] = $cpu;
        return $this;
    }

    /**
     * Set memory usage.
     *
     * @param string $memory Memory usage (e.g., "1Gi", "512Mi")
     *
     * @return self
     */
    public function setMemoryUsage(string $memory): self
    {
        if (!isset($this->spec['usage'])) {
            $this->spec['usage'] = [];
        }
        $this->spec['usage']['memory'] = $memory;
        return $this;
    }

    /**
     * Check if CPU usage exceeds threshold.
     *
     * @param string $threshold CPU threshold (e.g., "80%", "800m")
     *
     * @return bool
     */
    public function isCpuUsageHigh(string $threshold): bool
    {
        $usage = $this->getCpuUsage();
        if (!$usage) {
            return false;
        }

        // Simple comparison for demonstration - in practice would need more sophisticated parsing
        return $this->parseResourceValue($usage) > $this->parseResourceValue($threshold);
    }

    /**
     * Get CPU usage.
     *
     * @return string|null
     */
    public function getCpuUsage(): ?string
    {
        return $this->spec['usage']['cpu'] ?? null;
    }

    /**
     * Parse resource value for comparison.
     *
     * @param string $value Resource value
     *
     * @return float
     */
    private function parseResourceValue(string $value): float
    {
        // Simplified parsing - extract numeric part
        return (float) preg_replace('/[^0-9.]/', '', $value);
    }

    /**
     * Check if memory usage exceeds threshold.
     *
     * @param string $threshold Memory threshold (e.g., "80%", "4Gi")
     *
     * @return bool
     */
    public function isMemoryUsageHigh(string $threshold): bool
    {
        $usage = $this->getMemoryUsage();
        if (!$usage) {
            return false;
        }

        // Simple comparison for demonstration - in practice would need more sophisticated parsing
        return $this->parseResourceValue($usage) > $this->parseResourceValue($threshold);
    }

    /**
     * Get memory usage.
     *
     * @return string|null
     */
    public function getMemoryUsage(): ?string
    {
        return $this->spec['usage']['memory'] ?? null;
    }
}
