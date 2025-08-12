<?php

declare(strict_types=1);

namespace Kubernetes\API\Apps\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * Represents a Kubernetes Deployment resource.
 */
class Deployment extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of the resource.
     */
    public function getKind(): string
    {
        return 'Deployment';
    }

    /**
     * Get the number of replicas.
     */
    public function getReplicas(): ?int
    {
        return $this->spec['replicas'] ?? null;
    }

    /**
     * Set the number of replicas.
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

    /**
     * Get the selector for the deployment.
     */
    public function getSelector(): array
    {
        return $this->spec['selector'] ?? [];
    }

    /**
     * Set the selector for the deployment.
     *
     * @param array $selector
     *
     * @return self
     */
    public function setSelector(array $selector): self
    {
        $this->spec['selector'] = $selector;

        return $this;
    }

    /**
     * Get the pod template for the deployment.
     */
    public function getTemplate(): array
    {
        return $this->spec['template'] ?? [];
    }

    /**
     * Set the pod template for the deployment.
     *
     * @param array $template
     *
     * @return self
     */
    public function setTemplate(array $template): self
    {
        $this->spec['template'] = $template;

        return $this;
    }

    /**
     * Get the deployment strategy.
     */
    public function getStrategy(): array
    {
        return $this->spec['strategy'] ?? [];
    }

    /**
     * Set the deployment strategy.
     *
     * @param array $strategy
     *
     * @return self
     */
    public function setStrategy(array $strategy): self
    {
        $this->spec['strategy'] = $strategy;

        return $this;
    }
}
