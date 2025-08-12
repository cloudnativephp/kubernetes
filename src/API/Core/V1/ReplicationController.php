<?php

declare(strict_types=1);

namespace Kubernetes\API\Core\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * ReplicationController ensures that a specified number of pod replicas are running at any one time.
 *
 * Note: ReplicationController is largely deprecated in favor of Deployment and ReplicaSet,
 * but is still part of the Core/v1 API for backward compatibility.
 *
 * @see https://kubernetes.io/docs/reference/kubernetes-api/workload-resources/replication-controller-v1/
 */
class ReplicationController extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of the resource.
     *
     * @return string
     */
    public function getKind(): string
    {
        return 'ReplicationController';
    }

    /**
     * Get the number of desired replicas.
     *
     * @return int
     */
    public function getReplicas(): int
    {
        return $this->spec['replicas'] ?? 1;
    }

    /**
     * Get the selector for identifying pods managed by this controller.
     *
     * @return array<string, string>
     */
    public function getSelector(): array
    {
        return $this->spec['selector'] ?? [];
    }

    /**
     * Set the selector for identifying pods managed by this controller.
     *
     * @param array<string, string> $selector
     *
     * @return self
     */
    public function setSelector(array $selector): self
    {
        $this->spec['selector'] = $selector;

        return $this;
    }

    /**
     * Get the pod template for creating new pods.
     *
     * @return array<string, mixed>|null
     */
    public function getTemplate(): ?array
    {
        return $this->spec['template'] ?? null;
    }

    /**
     * Get the minimum number of seconds for which a newly created pod should be ready.
     *
     * @return int
     */
    public function getMinReadySeconds(): int
    {
        return $this->spec['minReadySeconds'] ?? 0;
    }

    /**
     * Set the minimum number of seconds for which a newly created pod should be ready.
     *
     * @param int $seconds
     *
     * @return self
     */
    public function setMinReadySeconds(int $seconds): self
    {
        $this->spec['minReadySeconds'] = $seconds;

        return $this;
    }

    /**
     * Get the number of available replicas.
     *
     * @return int
     */
    public function getAvailableReplicas(): int
    {
        return $this->status['availableReplicas'] ?? 0;
    }

    /**
     * Get the number of fully labeled replicas.
     *
     * @return int
     */
    public function getFullyLabeledReplicas(): int
    {
        return $this->status['fullyLabeledReplicas'] ?? 0;
    }

    /**
     * Get the number of ready replicas.
     *
     * @return int
     */
    public function getReadyReplicas(): int
    {
        return $this->status['readyReplicas'] ?? 0;
    }

    /**
     * Get the most recent generation observed by the controller.
     *
     * @return int
     */
    public function getObservedGeneration(): int
    {
        return $this->status['observedGeneration'] ?? 0;
    }

    /**
     * Get the list of conditions of this controller.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getConditions(): array
    {
        return $this->status['conditions'] ?? [];
    }

    /**
     * Set a simple pod template with basic container configuration.
     *
     * @param string                           $containerName
     * @param string                           $image
     * @param array<string, string>            $labels
     * @param array<int, array<string, mixed>> $ports
     *
     * @return self
     */
    public function setPodTemplate(
        string $containerName,
        string $image,
        array $labels = [],
        array $ports = []
    ): self {
        $container = [
            'name'  => $containerName,
            'image' => $image,
        ];

        if (!empty($ports)) {
            $container['ports'] = $ports;
        }

        $template = [
            'metadata' => [
                'labels' => $labels,
            ],
            'spec' => [
                'containers' => [$container],
            ],
        ];

        return $this->setTemplate($template);
    }

    /**
     * Set the pod template for creating new pods.
     *
     * @param array<string, mixed> $template
     *
     * @return self
     */
    public function setTemplate(array $template): self
    {
        $this->spec['template'] = $template;

        return $this;
    }

    /**
     * Scale the replication controller to the specified number of replicas.
     *
     * @param int $replicas
     *
     * @return self
     */
    public function scale(int $replicas): self
    {
        return $this->setReplicas($replicas);
    }

    /**
     * Set the number of desired replicas.
     *
     * @param int $replicas
     *
     * @return self
     */
    public function setReplicas(int $replicas): self
    {
        $this->spec['replicas'] = $replicas;

        return $this;
    }
}
