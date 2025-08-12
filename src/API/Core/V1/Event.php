<?php

declare(strict_types=1);

namespace Kubernetes\API\Core\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * Represents a Kubernetes Event resource.
 *
 * Event is a report of an event somewhere in the cluster. Events have a limited retention time
 * and triggers and messages may evolve with time.
 *
 * @see https://kubernetes.io/docs/reference/kubernetes-api/cluster-resources/event-v1/
 */
class Event extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of the resource.
     *
     * @return string The resource kind (Event)
     */
    public function getKind(): string
    {
        return 'Event';
    }

    /**
     * Get the involved object.
     *
     * @return array<string, mixed> The involved object reference
     */
    public function getInvolvedObject(): array
    {
        return $this->spec['involvedObject'] ?? [];
    }

    /**
     * Set the involved object.
     *
     * @param array<string, mixed> $involvedObject The involved object reference
     *
     * @return self
     */
    public function setInvolvedObject(array $involvedObject): self
    {
        $this->spec['involvedObject'] = $involvedObject;

        return $this;
    }

    /**
     * Get the event reason.
     *
     * @return string|null The event reason
     */
    public function getReason(): ?string
    {
        return $this->spec['reason'] ?? null;
    }

    /**
     * Set the event reason.
     *
     * @param string $reason The event reason
     *
     * @return self
     */
    public function setReason(string $reason): self
    {
        $this->spec['reason'] = $reason;

        return $this;
    }

    /**
     * Get the event message.
     *
     * @return string|null The event message
     */
    public function getMessage(): ?string
    {
        return $this->spec['message'] ?? null;
    }

    /**
     * Set the event message.
     *
     * @param string $message The event message
     *
     * @return self
     */
    public function setMessage(string $message): self
    {
        $this->spec['message'] = $message;

        return $this;
    }

    /**
     * Get the event source.
     *
     * @return array<string, string>|null The event source
     */
    public function getSource(): ?array
    {
        return $this->spec['source'] ?? null;
    }

    /**
     * Set the event source.
     *
     * @param array<string, string> $source The event source
     *
     * @return self
     */
    public function setSource(array $source): self
    {
        $this->spec['source'] = $source;

        return $this;
    }

    /**
     * Get the first timestamp.
     *
     * @return string|null The first timestamp
     */
    public function getFirstTimestamp(): ?string
    {
        return $this->spec['firstTimestamp'] ?? null;
    }

    /**
     * Set the first timestamp.
     *
     * @param string $firstTimestamp The first timestamp
     *
     * @return self
     */
    public function setFirstTimestamp(string $firstTimestamp): self
    {
        $this->spec['firstTimestamp'] = $firstTimestamp;

        return $this;
    }

    /**
     * Get the last timestamp.
     *
     * @return string|null The last timestamp
     */
    public function getLastTimestamp(): ?string
    {
        return $this->spec['lastTimestamp'] ?? null;
    }

    /**
     * Set the last timestamp.
     *
     * @param string $lastTimestamp The last timestamp
     *
     * @return self
     */
    public function setLastTimestamp(string $lastTimestamp): self
    {
        $this->spec['lastTimestamp'] = $lastTimestamp;

        return $this;
    }

    /**
     * Get the event count.
     *
     * @return int The event count
     */
    public function getCount(): int
    {
        return $this->spec['count'] ?? 1;
    }

    /**
     * Set the event count.
     *
     * @param int $count The event count
     *
     * @return self
     */
    public function setCount(int $count): self
    {
        $this->spec['count'] = $count;

        return $this;
    }

    /**
     * Set the event type.
     *
     * @param string $type The event type
     *
     * @return self
     */
    public function setType(string $type): self
    {
        $this->spec['type'] = $type;

        return $this;
    }

    /**
     * Get the event time.
     *
     * @return string|null The event time
     */
    public function getEventTime(): ?string
    {
        return $this->spec['eventTime'] ?? null;
    }

    /**
     * Set the event time.
     *
     * @param string $eventTime The event time
     *
     * @return self
     */
    public function setEventTime(string $eventTime): self
    {
        $this->spec['eventTime'] = $eventTime;

        return $this;
    }

    /**
     * Check if this is a warning event.
     *
     * @return bool True if this is a warning event
     */
    public function isWarning(): bool
    {
        return $this->getType() === 'Warning';
    }

    /**
     * Get the event type.
     *
     * @return string|null The event type (Normal, Warning)
     */
    public function getType(): ?string
    {
        return $this->spec['type'] ?? null;
    }

    /**
     * Check if this is a normal event.
     *
     * @return bool True if this is a normal event
     */
    public function isNormal(): bool
    {
        return $this->getType() === 'Normal';
    }
}
