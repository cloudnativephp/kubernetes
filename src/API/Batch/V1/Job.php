<?php

declare(strict_types=1);

namespace Kubernetes\API\Batch\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * Represents a Kubernetes Job resource.
 *
 * @link https://kubernetes.io/docs/concepts/workloads/controllers/job/
 */
class Job extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of the resource.
     *
     * @return string The resource kind
     */
    public function getKind(): string
    {
        return 'Job';
    }

    /**
     * Get the number of successful completions needed.
     *
     * @return int|null The completion count
     */
    public function getCompletions(): ?int
    {
        return $this->spec['completions'] ?? null;
    }

    /**
     * Set the number of successful completions needed.
     *
     * @param int $completions The completion count
     *
     * @return self
     */
    public function setCompletions(int $completions): self
    {
        $this->spec['completions'] = $completions;

        return $this;
    }

    /**
     * Get the maximum number of parallel pods.
     *
     * @return int|null The parallelism count
     */
    public function getParallelism(): ?int
    {
        return $this->spec['parallelism'] ?? null;
    }

    /**
     * Set the maximum number of parallel pods.
     *
     * @param int $parallelism The parallelism count
     *
     * @return self
     */
    public function setParallelism(int $parallelism): self
    {
        $this->spec['parallelism'] = $parallelism;

        return $this;
    }

    /**
     * Get the pod template for the job.
     *
     * @return array<string, mixed> The pod template
     */
    public function getTemplate(): array
    {
        return $this->spec['template'] ?? [];
    }

    /**
     * Set the pod template for the job.
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
     * Get the active deadline in seconds.
     *
     * @return int|null The active deadline
     */
    public function getActiveDeadlineSeconds(): ?int
    {
        return $this->spec['activeDeadlineSeconds'] ?? null;
    }

    /**
     * Set the active deadline in seconds.
     *
     * @param int $seconds The active deadline
     *
     * @return self
     */
    public function setActiveDeadlineSeconds(int $seconds): self
    {
        $this->spec['activeDeadlineSeconds'] = $seconds;

        return $this;
    }

    /**
     * Get the number of retry attempts.
     *
     * @return int|null The backoff limit
     */
    public function getBackoffLimit(): ?int
    {
        return $this->spec['backoffLimit'] ?? null;
    }

    /**
     * Set the number of retry attempts.
     *
     * @param int $limit The backoff limit
     *
     * @return self
     */
    public function setBackoffLimit(int $limit): self
    {
        $this->spec['backoffLimit'] = $limit;

        return $this;
    }
}
