<?php

declare(strict_types=1);

namespace Kubernetes\API\Apps\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * Represents a Kubernetes ReplicaSet resource.
 *
 * A ReplicaSet's purpose is to maintain a stable set of replica Pods running at any given time.
 * As such, it is often used to guarantee the availability of a specified number of identical Pods.
 *
 * @see https://kubernetes.io/docs/concepts/workloads/controllers/replicaset/
 */
class ReplicaSet extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of the resource.
     *
     * @return string The resource kind (ReplicaSet)
     */
    public function getKind(): string
    {
        return 'ReplicaSet';
    }

    /**
     * Get the selector for this ReplicaSet.
     *
     * @return array<string, mixed>|null The selector
     */
    public function getSelector(): ?array
    {
        return $this->spec['selector'] ?? null;
    }

    /**
     * Get the pod template for this ReplicaSet.
     *
     * @return array<string, mixed>|null The pod template
     */
    public function getTemplate(): ?array
    {
        return $this->spec['template'] ?? null;
    }

    /**
     * Get the minimum ready seconds.
     *
     * @return int The minimum ready seconds
     */
    public function getMinReadySeconds(): int
    {
        return $this->spec['minReadySeconds'] ?? 0;
    }

    /**
     * Set the minimum ready seconds.
     *
     * @param int $seconds The minimum ready seconds
     *
     * @return self
     */
    public function setMinReadySeconds(int $seconds): self
    {
        $this->spec['minReadySeconds'] = $seconds;

        return $this;
    }

    /**
     * Set selector using match labels.
     *
     * @param array<string, string> $labels The labels to match
     *
     * @return self
     */
    public function setSelectorMatchLabels(array $labels): self
    {
        return $this->setSelector([
            'matchLabels' => $labels,
        ]);
    }

    /**
     * Set the selector for this ReplicaSet.
     *
     * @param array<string, mixed> $selector The selector
     *
     * @return self
     */
    public function setSelector(array $selector): self
    {
        $this->spec['selector'] = $selector;

        return $this;
    }

    /**
     * Set selector using match expressions.
     *
     * @param array<int, array<string, mixed>> $expressions The match expressions
     *
     * @return self
     */
    public function setSelectorMatchExpressions(array $expressions): self
    {
        return $this->setSelector([
            'matchExpressions' => $expressions,
        ]);
    }

    /**
     * Set pod template with basic configuration.
     *
     * @param array<string, string>            $labels     Pod labels
     * @param array<int, array<string, mixed>> $containers Container specifications
     *
     * @return self
     */
    public function setPodTemplate(array $labels, array $containers): self
    {
        return $this->setTemplate([
            'metadata' => [
                'labels' => $labels,
            ],
            'spec' => [
                'containers' => $containers,
            ],
        ]);
    }

    /**
     * Set the pod template for this ReplicaSet.
     *
     * @param array<string, mixed> $template The pod template
     *
     * @return self
     */
    public function setTemplate(array $template): self
    {
        $this->spec['template'] = $template;

        return $this;
    }

    /**
     * Add a container to the pod template.
     *
     * @param string                                $name  The container name
     * @param string                                $image The container image
     * @param array<int, array<string, mixed>>|null $ports Container ports
     * @param array<string, string>|null            $env   Environment variables
     *
     * @return self
     */
    public function addContainer(
        string $name,
        string $image,
        ?array $ports = null,
        ?array $env = null
    ): self {
        $container = $this->createContainer($name, $image, $ports, $env);

        if (!isset($this->spec['template']['spec']['containers'])) {
            $this->spec['template']['spec']['containers'] = [];
        }

        $this->spec['template']['spec']['containers'][] = $container;

        return $this;
    }

    /**
     * Create a simple container specification.
     *
     * @param string                                $name  The container name
     * @param string                                $image The container image
     * @param array<int, array<string, mixed>>|null $ports Container ports
     * @param array<string, string>|null            $env   Environment variables
     *
     * @return array<string, mixed> The container specification
     */
    public function createContainer(
        string $name,
        string $image,
        ?array $ports = null,
        ?array $env = null
    ): array {
        $container = [
            'name'  => $name,
            'image' => $image,
        ];

        if ($ports !== null) {
            $container['ports'] = $ports;
        }

        if ($env !== null) {
            $container['env'] = array_map(
                fn ($key, $value) => ['name' => $key, 'value' => $value],
                array_keys($env),
                array_values($env)
            );
        }

        return $container;
    }

    /**
     * Scale the ReplicaSet to the specified number of replicas.
     *
     * @param int $replicas The desired number of replicas
     *
     * @return self
     */
    public function scale(int $replicas): self
    {
        return $this->setReplicas($replicas);
    }

    /**
     * Set the desired number of replicas.
     *
     * @param int $replicas The number of replicas
     *
     * @return self
     */
    public function setReplicas(int $replicas): self
    {
        $this->spec['replicas'] = $replicas;

        return $this;
    }

    /**
     * Scale up the ReplicaSet by the specified amount.
     *
     * @param int $amount The amount to scale up by
     *
     * @return self
     */
    public function scaleUp(int $amount = 1): self
    {
        return $this->setReplicas($this->getReplicas() + $amount);
    }

    /**
     * Get the desired number of replicas.
     *
     * @return int The number of replicas
     */
    public function getReplicas(): int
    {
        return $this->spec['replicas'] ?? 1;
    }

    /**
     * Scale down the ReplicaSet by the specified amount.
     *
     * @param int $amount The amount to scale down by
     *
     * @return self
     */
    public function scaleDown(int $amount = 1): self
    {
        $newReplicas = max(0, $this->getReplicas() - $amount);
        return $this->setReplicas($newReplicas);
    }

    /**
     * Set resource limits for the first container.
     *
     * @param array<string, string> $limits The resource limits (e.g., ['cpu' => '100m', 'memory' => '128Mi'])
     *
     * @return self
     */
    public function setResourceLimits(array $limits): self
    {
        if (!isset($this->spec['template']['spec']['containers'][0])) {
            return $this;
        }

        $this->spec['template']['spec']['containers'][0]['resources']['limits'] = $limits;

        return $this;
    }

    /**
     * Set resource requests for the first container.
     *
     * @param array<string, string> $requests The resource requests (e.g., ['cpu' => '50m', 'memory' => '64Mi'])
     *
     * @return self
     */
    public function setResourceRequests(array $requests): self
    {
        if (!isset($this->spec['template']['spec']['containers'][0])) {
            return $this;
        }

        $this->spec['template']['spec']['containers'][0]['resources']['requests'] = $requests;

        return $this;
    }

    /**
     * Set restart policy for the pod template.
     *
     * @param string $policy The restart policy (Always, OnFailure, Never)
     *
     * @return self
     */
    public function setRestartPolicy(string $policy): self
    {
        if (!isset($this->spec['template']['spec'])) {
            $this->spec['template']['spec'] = [];
        }

        $this->spec['template']['spec']['restartPolicy'] = $policy;

        return $this;
    }

    /**
     * Get the current status of the ReplicaSet.
     *
     * @return array<string, mixed> The status
     */
    public function getStatus(): array
    {
        return $this->status;
    }

    /**
     * Get the current replica count from status.
     *
     * @return int The current replica count
     */
    public function getCurrentReplicas(): int
    {
        return $this->status['replicas'] ?? 0;
    }

    /**
     * Get the fully labeled replica count from status.
     *
     * @return int The fully labeled replica count
     */
    public function getFullyLabeledReplicas(): int
    {
        return $this->status['fullyLabeledReplicas'] ?? 0;
    }

    /**
     * Get the observed generation from status.
     *
     * @return int The observed generation
     */
    public function getObservedGeneration(): int
    {
        return $this->status['observedGeneration'] ?? 0;
    }

    /**
     * Get the status conditions.
     *
     * @return array<int, array<string, mixed>> The status conditions
     */
    public function getConditions(): array
    {
        return $this->status['conditions'] ?? [];
    }

    /**
     * Check if the ReplicaSet is ready.
     *
     * @return bool True if all replicas are ready
     */
    public function isReady(): bool
    {
        return $this->getReadyReplicas() === $this->getReplicas();
    }

    /**
     * Get the ready replica count from status.
     *
     * @return int The ready replica count
     */
    public function getReadyReplicas(): int
    {
        return $this->status['readyReplicas'] ?? 0;
    }

    /**
     * Check if the ReplicaSet is available.
     *
     * @return bool True if all replicas are available
     */
    public function isAvailable(): bool
    {
        return $this->getAvailableReplicas() === $this->getReplicas();
    }

    /**
     * Get the available replica count from status.
     *
     * @return int The available replica count
     */
    public function getAvailableReplicas(): int
    {
        return $this->status['availableReplicas'] ?? 0;
    }
}
