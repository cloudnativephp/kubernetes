<?php

declare(strict_types=1);

namespace Kubernetes\API\Apps\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * Represents a Kubernetes StatefulSet resource.
 *
 * StatefulSet is the workload API object used to manage stateful applications.
 * It manages the deployment and scaling of a set of Pods, and provides guarantees
 * about the ordering and uniqueness of these Pods.
 *
 * @see https://kubernetes.io/docs/concepts/workloads/controllers/statefulset/
 */
class StatefulSet extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of the resource.
     *
     * @return string The resource kind (StatefulSet)
     */
    public function getKind(): string
    {
        return 'StatefulSet';
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
     * Get the service name for this StatefulSet.
     *
     * @return string|null The service name
     */
    public function getServiceName(): ?string
    {
        return $this->spec['serviceName'] ?? null;
    }

    /**
     * Set the service name for this StatefulSet.
     *
     * @param string $serviceName The service name
     *
     * @return self
     */
    public function setServiceName(string $serviceName): self
    {
        $this->spec['serviceName'] = $serviceName;

        return $this;
    }

    /**
     * Get the selector for this StatefulSet.
     *
     * @return array<string, mixed>|null The selector
     */
    public function getSelector(): ?array
    {
        return $this->spec['selector'] ?? null;
    }

    /**
     * Get the pod template for this StatefulSet.
     *
     * @return array<string, mixed>|null The pod template
     */
    public function getTemplate(): ?array
    {
        return $this->spec['template'] ?? null;
    }

    /**
     * Get the volume claim templates for this StatefulSet.
     *
     * @return array<int, array<string, mixed>> The volume claim templates
     */
    public function getVolumeClaimTemplates(): array
    {
        return $this->spec['volumeClaimTemplates'] ?? [];
    }

    /**
     * Set the volume claim templates for this StatefulSet.
     *
     * @param array<int, array<string, mixed>> $templates The volume claim templates
     *
     * @return self
     */
    public function setVolumeClaimTemplates(array $templates): self
    {
        $this->spec['volumeClaimTemplates'] = $templates;

        return $this;
    }

    /**
     * Get the update strategy for this StatefulSet.
     *
     * @return array<string, mixed>|null The update strategy
     */
    public function getUpdateStrategy(): ?array
    {
        return $this->spec['updateStrategy'] ?? null;
    }

    /**
     * Get the pod management policy.
     *
     * @return string|null The pod management policy (OrderedReady or Parallel)
     */
    public function getPodManagementPolicy(): ?string
    {
        return $this->spec['podManagementPolicy'] ?? null;
    }

    /**
     * Set the pod management policy.
     *
     * @param string $policy The pod management policy (OrderedReady or Parallel)
     *
     * @return self
     */
    public function setPodManagementPolicy(string $policy): self
    {
        $this->spec['podManagementPolicy'] = $policy;

        return $this;
    }

    /**
     * Get the revision history limit.
     *
     * @return int|null The revision history limit
     */
    public function getRevisionHistoryLimit(): ?int
    {
        return $this->spec['revisionHistoryLimit'] ?? null;
    }

    /**
     * Set the revision history limit.
     *
     * @param int $limit The revision history limit
     *
     * @return self
     */
    public function setRevisionHistoryLimit(int $limit): self
    {
        $this->spec['revisionHistoryLimit'] = $limit;

        return $this;
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
     * Set the selector for this StatefulSet.
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
     * Set the pod template for this StatefulSet.
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
     * Set rolling update strategy.
     *
     * @param int|null $partition      The partition for rolling updates
     * @param int|null $maxUnavailable Maximum unavailable pods during update
     *
     * @return self
     */
    public function setRollingUpdateStrategy(?int $partition = null, ?int $maxUnavailable = null): self
    {
        $strategy = [
            'type'          => 'RollingUpdate',
            'rollingUpdate' => [],
        ];

        if ($partition !== null) {
            $strategy['rollingUpdate']['partition'] = $partition;
        }

        if ($maxUnavailable !== null) {
            $strategy['rollingUpdate']['maxUnavailable'] = $maxUnavailable;
        }

        return $this->setUpdateStrategy($strategy);
    }

    /**
     * Set the update strategy for this StatefulSet.
     *
     * @param array<string, mixed> $strategy The update strategy
     *
     * @return self
     */
    public function setUpdateStrategy(array $strategy): self
    {
        $this->spec['updateStrategy'] = $strategy;

        return $this;
    }

    /**
     * Set on delete update strategy.
     *
     * @return self
     */
    public function setOnDeleteUpdateStrategy(): self
    {
        return $this->setUpdateStrategy([
            'type' => 'OnDelete',
        ]);
    }

    /**
     * Add a persistent volume claim template with common configuration.
     *
     * @param string        $name         The PVC name
     * @param string        $storageClass The storage class
     * @param string        $size         The storage size (e.g., "10Gi")
     * @param array<string> $accessModes  The access modes
     *
     * @return self
     */
    public function addPvcTemplate(
        string $name,
        string $storageClass,
        string $size,
        array $accessModes = ['ReadWriteOnce']
    ): self {
        $template = $this->createPvcTemplate($name, $storageClass, $size, $accessModes);
        return $this->addVolumeClaimTemplate($template);
    }

    /**
     * Create a persistent volume claim template.
     *
     * @param string        $name         The PVC name
     * @param string        $storageClass The storage class
     * @param string        $size         The storage size (e.g., "10Gi")
     * @param array<string> $accessModes  The access modes
     *
     * @return array<string, mixed> The PVC template
     */
    public function createPvcTemplate(
        string $name,
        string $storageClass,
        string $size,
        array $accessModes = ['ReadWriteOnce']
    ): array {
        return [
            'metadata' => [
                'name' => $name,
            ],
            'spec' => [
                'accessModes'      => $accessModes,
                'storageClassName' => $storageClass,
                'resources'        => [
                    'requests' => [
                        'storage' => $size,
                    ],
                ],
            ],
        ];
    }

    /**
     * Add a volume claim template to this StatefulSet.
     *
     * @param array<string, mixed> $template The volume claim template
     *
     * @return self
     */
    public function addVolumeClaimTemplate(array $template): self
    {
        $this->spec['volumeClaimTemplates'][] = $template;

        return $this;
    }

    /**
     * Scale the StatefulSet to the specified number of replicas.
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
     * Get the current status of the StatefulSet.
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
        return $this->status['currentReplicas'] ?? 0;
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
     * Get the updated replica count from status.
     *
     * @return int The updated replica count
     */
    public function getUpdatedReplicas(): int
    {
        return $this->status['updatedReplicas'] ?? 0;
    }
}
