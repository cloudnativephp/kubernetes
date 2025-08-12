<?php

declare(strict_types=1);

namespace Kubernetes\API\Apps\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * Represents a Kubernetes DaemonSet resource.
 *
 * A DaemonSet ensures that all (or some) Nodes run a copy of a Pod.
 * As nodes are added to the cluster, Pods are added to them.
 * As nodes are removed from the cluster, those Pods are garbage collected.
 *
 * @see https://kubernetes.io/docs/concepts/workloads/controllers/daemonset/
 */
class DaemonSet extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of the resource.
     *
     * @return string The resource kind (DaemonSet)
     */
    public function getKind(): string
    {
        return 'DaemonSet';
    }

    /**
     * Get the selector for this DaemonSet.
     *
     * @return array<string, mixed>|null The selector
     */
    public function getSelector(): ?array
    {
        return $this->spec['selector'] ?? null;
    }

    /**
     * Get the pod template for this DaemonSet.
     *
     * @return array<string, mixed>|null The pod template
     */
    public function getTemplate(): ?array
    {
        return $this->spec['template'] ?? null;
    }

    /**
     * Get the update strategy for this DaemonSet.
     *
     * @return array<string, mixed>|null The update strategy
     */
    public function getUpdateStrategy(): ?array
    {
        return $this->spec['updateStrategy'] ?? null;
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
     * Set the selector for this DaemonSet.
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
     * Set the pod template for this DaemonSet.
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
     * @param int|string|null $maxUnavailable Maximum unavailable pods during update
     * @param int|null        $maxSurge       Maximum surge pods during update
     *
     * @return self
     */
    public function setRollingUpdateStrategy($maxUnavailable = null, ?int $maxSurge = null): self
    {
        $strategy = [
            'type'          => 'RollingUpdate',
            'rollingUpdate' => [],
        ];

        if ($maxUnavailable !== null) {
            $strategy['rollingUpdate']['maxUnavailable'] = $maxUnavailable;
        }

        if ($maxSurge !== null) {
            $strategy['rollingUpdate']['maxSurge'] = $maxSurge;
        }

        return $this->setUpdateStrategy($strategy);
    }

    /**
     * Set the update strategy for this DaemonSet.
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
     * Configure DaemonSet to run on all nodes (including master/control-plane).
     *
     * @return self
     */
    public function runOnAllNodes(): self
    {
        // Tolerate master/control-plane taints
        $this->addToleration($this->createToleration('node-role.kubernetes.io/master', 'Exists', null, 'NoSchedule'));
        $this->addToleration($this->createToleration('node-role.kubernetes.io/control-plane', 'Exists', null, 'NoSchedule'));

        return $this;
    }

    /**
     * Add toleration to the pod template.
     *
     * @param array<string, mixed> $toleration The toleration
     *
     * @return self
     */
    public function addToleration(array $toleration): self
    {
        if (!isset($this->spec['template']['spec']['tolerations'])) {
            $this->spec['template']['spec']['tolerations'] = [];
        }

        $this->spec['template']['spec']['tolerations'][] = $toleration;

        return $this;
    }

    /**
     * Create a toleration for a specific taint.
     *
     * @param string      $key               The taint key
     * @param string      $operator          The operator (Equal, Exists)
     * @param string|null $value             The taint value (for Equal operator)
     * @param string|null $effect            The taint effect (NoSchedule, PreferNoSchedule, NoExecute)
     * @param int|null    $tolerationSeconds How long to tolerate the taint
     *
     * @return array<string, mixed> The toleration
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
     * Configure DaemonSet to run only on worker nodes.
     *
     * @return self
     */
    public function runOnWorkerNodesOnly(): self
    {
        return $this->addNodeSelector([
            'node-role.kubernetes.io/worker' => 'true',
        ]);
    }

    /**
     * Add node selector to the pod template.
     *
     * @param array<string, string> $nodeSelector The node selector
     *
     * @return self
     */
    public function addNodeSelector(array $nodeSelector): self
    {
        if (!isset($this->spec['template']['spec'])) {
            $this->spec['template']['spec'] = [];
        }

        $this->spec['template']['spec']['nodeSelector'] = $nodeSelector;

        return $this;
    }

    /**
     * Set privileged security context for the first container.
     *
     * @param bool $privileged Whether to run as privileged
     *
     * @return self
     */
    public function setPrivileged(bool $privileged = true): self
    {
        if (!isset($this->spec['template']['spec']['containers'][0])) {
            return $this;
        }

        $this->spec['template']['spec']['containers'][0]['securityContext'] = [
            'privileged' => $privileged,
        ];

        return $this;
    }

    /**
     * Set host network mode.
     *
     * @param bool $hostNetwork Whether to use host network
     *
     * @return self
     */
    public function setHostNetwork(bool $hostNetwork = true): self
    {
        if (!isset($this->spec['template']['spec'])) {
            $this->spec['template']['spec'] = [];
        }

        $this->spec['template']['spec']['hostNetwork'] = $hostNetwork;

        return $this;
    }

    /**
     * Set host PID mode.
     *
     * @param bool $hostPID Whether to use host PID namespace
     *
     * @return self
     */
    public function setHostPID(bool $hostPID = true): self
    {
        if (!isset($this->spec['template']['spec'])) {
            $this->spec['template']['spec'] = [];
        }

        $this->spec['template']['spec']['hostPID'] = $hostPID;

        return $this;
    }

    /**
     * Get the current status of the DaemonSet.
     *
     * @return array<string, mixed> The status
     */
    public function getStatus(): array
    {
        return $this->status;
    }

    /**
     * Get the current number of nodes scheduled.
     *
     * @return int The current number scheduled
     */
    public function getCurrentNumberScheduled(): int
    {
        return $this->status['currentNumberScheduled'] ?? 0;
    }

    /**
     * Get the desired number of nodes scheduled.
     *
     * @return int The desired number scheduled
     */
    public function getDesiredNumberScheduled(): int
    {
        return $this->status['desiredNumberScheduled'] ?? 0;
    }

    /**
     * Get the number of nodes with available pods.
     *
     * @return int The number ready
     */
    public function getNumberReady(): int
    {
        return $this->status['numberReady'] ?? 0;
    }

    /**
     * Get the number of nodes with updated pods.
     *
     * @return int The updated number scheduled
     */
    public function getUpdatedNumberScheduled(): int
    {
        return $this->status['updatedNumberScheduled'] ?? 0;
    }

    /**
     * Get the number of nodes with available updated pods.
     *
     * @return int The number available
     */
    public function getNumberAvailable(): int
    {
        return $this->status['numberAvailable'] ?? 0;
    }

    /**
     * Get the number of nodes with unavailable pods.
     *
     * @return int The number unavailable
     */
    public function getNumberUnavailable(): int
    {
        return $this->status['numberUnavailable'] ?? 0;
    }
}
