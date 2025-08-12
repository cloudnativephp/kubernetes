<?php

declare(strict_types=1);

namespace Kubernetes\API\EventsK8sIo\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * Event is a report of an event somewhere in the cluster.
 *
 * Event provides enhanced event reporting capabilities compared to the
 * core/v1 Event, with better performance and additional fields for
 * detailed cluster observability.
 *
 * @link https://kubernetes.io/docs/reference/kubernetes-api/cluster-resources/event-v1/
 */
class Event extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of this resource.
     *
     * @return string
     */
    public function getKind(): string
    {
        return 'Event';
    }

    /**
     * Get the event time.
     *
     * @return string|null
     */
    public function getEventTime(): ?string
    {
        return $this->spec['eventTime'] ?? null;
    }

    /**
     * Set the series information for aggregated events.
     *
     * @param int    $count            Number of occurrences
     * @param string $lastObservedTime Time when last occurrence was observed
     *
     * @return self
     */
    public function setSeries(int $count, string $lastObservedTime): self
    {
        $this->spec['series'] = [
            'count'            => $count,
            'lastObservedTime' => $lastObservedTime,
        ];
        return $this;
    }

    /**
     * Get the series information.
     *
     * @return array<string, mixed>|null
     */
    public function getSeries(): ?array
    {
        return $this->spec['series'] ?? null;
    }

    /**
     * Get the reporting controller.
     *
     * @return string|null
     */
    public function getReportingController(): ?string
    {
        return $this->spec['reportingController'] ?? null;
    }

    /**
     * Get the reporting instance.
     *
     * @return string|null
     */
    public function getReportingInstance(): ?string
    {
        return $this->spec['reportingInstance'] ?? null;
    }

    /**
     * Get the action.
     *
     * @return string|null
     */
    public function getAction(): ?string
    {
        return $this->spec['action'] ?? null;
    }

    /**
     * Get the reason.
     *
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->spec['reason'] ?? null;
    }

    /**
     * Get the regarding object reference.
     *
     * @return array<string, string>|null
     */
    public function getRegarding(): ?array
    {
        return $this->spec['regarding'] ?? null;
    }

    /**
     * Set the related object reference.
     *
     * @param string $apiVersion API version of the related object
     * @param string $kind       Kind of the related object
     * @param string $name       Name of the related object
     * @param string $namespace  Namespace of the related object (if applicable)
     * @param string $uid        UID of the related object
     *
     * @return self
     */
    public function setRelated(
        string $apiVersion,
        string $kind,
        string $name,
        string $namespace,
        string $uid
    ): self {
        $this->spec['related'] = [
            'apiVersion' => $apiVersion,
            'kind'       => $kind,
            'name'       => $name,
            'namespace'  => $namespace,
            'uid'        => $uid,
        ];
        return $this;
    }

    /**
     * Get the related object reference.
     *
     * @return array<string, string>|null
     */
    public function getRelated(): ?array
    {
        return $this->spec['related'] ?? null;
    }

    /**
     * Get the note.
     *
     * @return string|null
     */
    public function getNote(): ?string
    {
        return $this->spec['note'] ?? null;
    }

    /**
     * Set the deprecation warning information.
     *
     * @param string $targetGroupVersionKind The deprecated GroupVersionKind
     * @param string $warning                Warning message about deprecation
     *
     * @return self
     */
    public function setDeprecatedSourceComponent(string $targetGroupVersionKind, string $warning): self
    {
        $this->spec['deprecatedSource'] = [
            'component' => $targetGroupVersionKind,
        ];
        $this->spec['deprecatedSourceHost'] = $warning;
        return $this;
    }

    /**
     * Helper method to create a normal event.
     *
     * @param string $reason    Event reason
     * @param string $note      Event description
     * @param string $action    Action performed
     * @param string $component Reporting component
     *
     * @return self
     */
    public function createNormalEvent(
        string $reason,
        string $note,
        string $action,
        string $component
    ): self {
        return $this
            ->setType('Normal')
            ->setReason($reason)
            ->setNote($note)
            ->setAction($action)
            ->setReportingController($component, $component)
            ->setEventTime(date('c'));
    }

    /**
     * Set the event time when the event was first observed.
     *
     * @param string $eventTime RFC3339 timestamp of the event
     *
     * @return self
     */
    public function setEventTime(string $eventTime): self
    {
        $this->spec['eventTime'] = $eventTime;
        return $this;
    }

    /**
     * Set the reporting controller that created the event.
     *
     * @param string $reportingController Name of the controller
     * @param string $reportingInstance   Instance identifier of the controller
     *
     * @return self
     */
    public function setReportingController(string $reportingController, string $reportingInstance): self
    {
        $this->spec['reportingController'] = $reportingController;
        $this->spec['reportingInstance'] = $reportingInstance;
        return $this;
    }

    /**
     * Set the action that was performed.
     *
     * @param string $action Description of the action
     *
     * @return self
     */
    public function setAction(string $action): self
    {
        $this->spec['action'] = $action;
        return $this;
    }

    /**
     * Set the human-readable note describing the event.
     *
     * @param string $note Descriptive note about the event
     *
     * @return self
     */
    public function setNote(string $note): self
    {
        $this->spec['note'] = $note;
        return $this;
    }

    /**
     * Set the reason for the event.
     *
     * @param string $reason Short machine-readable reason
     *
     * @return self
     */
    public function setReason(string $reason): self
    {
        $this->spec['reason'] = $reason;
        return $this;
    }

    /**
     * Set the event type (Normal or Warning).
     *
     * @param string $type Event type
     *
     * @return self
     */
    public function setType(string $type): self
    {
        $this->spec['type'] = $type;
        return $this;
    }

    /**
     * Helper method to create a warning event.
     *
     * @param string $reason    Event reason
     * @param string $note      Event description
     * @param string $action    Action performed
     * @param string $component Reporting component
     *
     * @return self
     */
    public function createWarningEvent(
        string $reason,
        string $note,
        string $action,
        string $component
    ): self {
        return $this
            ->setType('Warning')
            ->setReason($reason)
            ->setNote($note)
            ->setAction($action)
            ->setReportingController($component, $component)
            ->setEventTime(date('c'));
    }

    /**
     * Helper method to create a pod event.
     *
     * @param string $podName   Name of the pod
     * @param string $podUid    UID of the pod
     * @param string $namespace Namespace of the pod
     * @param string $reason    Event reason
     * @param string $note      Event description
     * @param string $type      Event type (Normal or Warning)
     *
     * @return self
     */
    public function createPodEvent(
        string $podName,
        string $podUid,
        string $namespace,
        string $reason,
        string $note,
        string $type = 'Normal'
    ): self {
        return $this
            ->setNamespace($namespace)
            ->setRegarding('v1', 'Pod', $podName, $namespace, $podUid)
            ->setType($type)
            ->setReason($reason)
            ->setNote($note)
            ->setEventTime(date('c'));
    }

    /**
     * Set the object that the event is about.
     *
     * @param string $apiVersion API version of the object
     * @param string $kind       Kind of the object
     * @param string $name       Name of the object
     * @param string $namespace  Namespace of the object (if applicable)
     * @param string $uid        UID of the object
     *
     * @return self
     */
    public function setRegarding(
        string $apiVersion,
        string $kind,
        string $name,
        string $namespace,
        string $uid
    ): self {
        $this->spec['regarding'] = [
            'apiVersion' => $apiVersion,
            'kind'       => $kind,
            'name'       => $name,
            'namespace'  => $namespace,
            'uid'        => $uid,
        ];
        return $this;
    }

    /**
     * Helper method to create a deployment event.
     *
     * @param string $deploymentName Name of the deployment
     * @param string $deploymentUid  UID of the deployment
     * @param string $namespace      Namespace of the deployment
     * @param string $reason         Event reason
     * @param string $note           Event description
     * @param string $type           Event type (Normal or Warning)
     *
     * @return self
     */
    public function createDeploymentEvent(
        string $deploymentName,
        string $deploymentUid,
        string $namespace,
        string $reason,
        string $note,
        string $type = 'Normal'
    ): self {
        return $this
            ->setNamespace($namespace)
            ->setRegarding('apps/v1', 'Deployment', $deploymentName, $namespace, $deploymentUid)
            ->setType($type)
            ->setReason($reason)
            ->setNote($note)
            ->setAction('Update')
            ->setReportingController('deployment-controller', 'deployment-controller')
            ->setEventTime(date('c'));
    }

    /**
     * Check if this is a warning event.
     *
     * @return bool
     */
    public function isWarning(): bool
    {
        return $this->getType() === 'Warning';
    }

    /**
     * Get the event type.
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->spec['type'] ?? null;
    }

    /**
     * Check if this is a normal event.
     *
     * @return bool
     */
    public function isNormal(): bool
    {
        return $this->getType() === 'Normal';
    }
}
