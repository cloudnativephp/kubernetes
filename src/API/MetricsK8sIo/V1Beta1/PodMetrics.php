<?php

declare(strict_types=1);

namespace Kubernetes\API\MetricsK8sIo\V1Beta1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * PodMetrics represents resource usage metrics for a pod.
 *
 * @link https://kubernetes.io/docs/reference/generated/kubernetes-api/v1.30/#podmetrics-v1beta1-metrics-k8s-io
 */
class PodMetrics extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the resource kind.
     *
     * @return string
     */
    public function getKind(): string
    {
        return 'PodMetrics';
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
     * Set container metrics.
     *
     * @param array<int, array<string, mixed>> $containers Container metrics data
     *
     * @return self
     */
    public function setContainers(array $containers): self
    {
        $this->spec['containers'] = $containers;
        return $this;
    }

    /**
     * Add container metrics.
     *
     * @param string                $name  Container name
     * @param array<string, string> $usage Resource usage (cpu, memory)
     *
     * @return self
     */
    public function addContainerMetrics(string $name, array $usage): self
    {
        if (!isset($this->spec['containers'])) {
            $this->spec['containers'] = [];
        }

        $this->spec['containers'][] = [
            'name'  => $name,
            'usage' => $usage,
        ];

        return $this;
    }

    /**
     * Get CPU usage for a specific container.
     *
     * @param string $containerName Container name
     *
     * @return string|null
     */
    public function getContainerCpuUsage(string $containerName): ?string
    {
        foreach ($this->getContainers() as $container) {
            if ($container['name'] === $containerName) {
                return $container['usage']['cpu'] ?? null;
            }
        }
        return null;
    }

    /**
     * Get container metrics.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getContainers(): array
    {
        return $this->spec['containers'] ?? [];
    }

    /**
     * Get memory usage for a specific container.
     *
     * @param string $containerName Container name
     *
     * @return string|null
     */
    public function getContainerMemoryUsage(string $containerName): ?string
    {
        foreach ($this->getContainers() as $container) {
            if ($container['name'] === $containerName) {
                return $container['usage']['memory'] ?? null;
            }
        }
        return null;
    }

    /**
     * Get total CPU usage across all containers.
     *
     * @return string|null
     */
    public function getTotalCpuUsage(): ?string
    {
        $totalNano = 0;
        foreach ($this->getContainers() as $container) {
            $cpu = $container['usage']['cpu'] ?? null;
            if ($cpu) {
                // Convert CPU values to nanocores for addition
                $totalNano += $this->cpuToNanocores($cpu);
            }
        }
        return $totalNano > 0 ? $this->nanocoresToCpu($totalNano) : null;
    }

    /**
     * Convert CPU string to nanocores.
     *
     * @param string $cpu CPU value (e.g., "100m", "1")
     *
     * @return int
     */
    private function cpuToNanocores(string $cpu): int
    {
        if (str_ends_with($cpu, 'm')) {
            return (int) rtrim($cpu, 'm') * 1000000;
        }
        return (int) $cpu * 1000000000;
    }

    /**
     * Convert nanocores to CPU string.
     *
     * @param int $nanocores Nanocores value
     *
     * @return string
     */
    private function nanocoresToCpu(int $nanocores): string
    {
        if ($nanocores < 1000000000) {
            return (int) ($nanocores / 1000000) . 'm';
        }
        return (string) ($nanocores / 1000000000);
    }

    /**
     * Get total memory usage across all containers.
     *
     * @return string|null
     */
    public function getTotalMemoryUsage(): ?string
    {
        $totalBytes = 0;
        foreach ($this->getContainers() as $container) {
            $memory = $container['usage']['memory'] ?? null;
            if ($memory) {
                // Convert memory values to bytes for addition
                $totalBytes += $this->memoryToBytes($memory);
            }
        }
        return $totalBytes > 0 ? $this->bytesToMemory($totalBytes) : null;
    }

    /**
     * Convert memory string to bytes.
     *
     * @param string $memory Memory value (e.g., "128Mi", "1Gi")
     *
     * @return int
     */
    private function memoryToBytes(string $memory): int
    {
        $units = ['Ki' => 1024, 'Mi' => 1048576, 'Gi' => 1073741824];
        foreach ($units as $unit => $multiplier) {
            if (str_ends_with($memory, $unit)) {
                return (int) rtrim($memory, $unit) * $multiplier;
            }
        }
        return (int) $memory;
    }

    /**
     * Convert bytes to memory string.
     *
     * @param int $bytes Bytes value
     *
     * @return string
     */
    private function bytesToMemory(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return (int) ($bytes / 1073741824) . 'Gi';
        }
        if ($bytes >= 1048576) {
            return (int) ($bytes / 1048576) . 'Mi';
        }
        if ($bytes >= 1024) {
            return (int) ($bytes / 1024) . 'Ki';
        }
        return (string) $bytes;
    }
}
