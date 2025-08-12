<?php

declare(strict_types=1);

namespace Kubernetes\API\CoordinationK8sIo\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * Lease defines a lease concept.
 *
 * Lease is used for leader election and coordination between multiple replicas
 * of the same component. It provides distributed locking and coordination
 * capabilities for Kubernetes controllers and operators.
 *
 * @link https://kubernetes.io/docs/reference/kubernetes-api/cluster-resources/lease-v1/
 */
class Lease extends AbstractAbstractResource
{
    use IsNamespacedResource;

    /**
     * Get the kind of this resource.
     *
     * @return string
     */
    public function getKind(): string
    {
        return 'Lease';
    }

    /**
     * Get the acquire time.
     *
     * @return string|null
     */
    public function getAcquireTime(): ?string
    {
        return $this->spec['acquireTime'] ?? null;
    }

    /**
     * Get the lease transitions count.
     *
     * @return int|null
     */
    public function getLeaseTransitions(): ?int
    {
        return $this->spec['leaseTransitions'] ?? null;
    }

    /**
     * Renew the lease for the current holder.
     *
     * @return self
     */
    public function renewLease(): self
    {
        $this->setRenewTime(date('c')); // RFC3339 format
        return $this;
    }

    /**
     * Set the renew time for the lease.
     *
     * @param string $renewTime RFC3339 timestamp when the lease was last renewed
     *
     * @return self
     */
    public function setRenewTime(string $renewTime): self
    {
        $this->spec['renewTime'] = $renewTime;
        return $this;
    }

    /**
     * Check if the lease is currently held by the specified holder.
     *
     * @param string $holderIdentity Identifier to check
     *
     * @return bool
     */
    public function isHeldBy(string $holderIdentity): bool
    {
        return $this->getHolderIdentity() === $holderIdentity;
    }

    /**
     * Get the holder identity.
     *
     * @return string|null
     */
    public function getHolderIdentity(): ?string
    {
        return $this->spec['holderIdentity'] ?? null;
    }

    /**
     * Check if the lease has expired based on current time.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        $renewTime = $this->getRenewTime();
        $duration = $this->getLeaseDurationSeconds();

        if ($renewTime === null || $duration === null) {
            return true;
        }

        $renewTimestamp = strtotime($renewTime);
        $expiryTime = $renewTimestamp + $duration;

        return time() > $expiryTime;
    }

    /**
     * Get the renew time.
     *
     * @return string|null
     */
    public function getRenewTime(): ?string
    {
        return $this->spec['renewTime'] ?? null;
    }

    /**
     * Get the lease duration in seconds.
     *
     * @return int|null
     */
    public function getLeaseDurationSeconds(): ?int
    {
        return $this->spec['leaseDurationSeconds'] ?? null;
    }

    /**
     * Get the remaining time until lease expiry in seconds.
     *
     * @return int|null Seconds until expiry, null if not set, negative if expired
     */
    public function getRemainingTime(): ?int
    {
        $expiryTime = $this->getExpiryTime();

        if ($expiryTime === null) {
            return null;
        }

        return $expiryTime - time();
    }

    /**
     * Get the expiry time as a timestamp.
     *
     * @return int|null Unix timestamp when the lease expires, null if not set
     */
    public function getExpiryTime(): ?int
    {
        $renewTime = $this->getRenewTime();
        $duration = $this->getLeaseDurationSeconds();

        if ($renewTime === null || $duration === null) {
            return null;
        }

        return strtotime($renewTime) + $duration;
    }

    /**
     * Helper method to create a leader election lease.
     *
     * @param string $componentName  Name of the component using leader election
     * @param string $holderIdentity Identifier of the current leader
     * @param string $namespace      Namespace for the lease
     * @param int    $duration       Lease duration in seconds
     *
     * @return self
     */
    public function createLeaderElectionLease(
        string $componentName,
        string $holderIdentity,
        string $namespace,
        int $duration = 15
    ): self {
        return $this
            ->setName("{$componentName}-leader")
            ->setNamespace($namespace)
            ->acquireLease($holderIdentity, $duration)
            ->setLeaseTransitions(0);
    }

    /**
     * Set the lease transitions count.
     *
     * @param int $leaseTransitions Number of times the lease has changed holders
     *
     * @return self
     */
    public function setLeaseTransitions(int $leaseTransitions): self
    {
        $this->spec['leaseTransitions'] = $leaseTransitions;
        return $this;
    }

    /**
     * Acquire the lease for a holder.
     *
     * @param string $holderIdentity Identifier of the lease holder
     * @param int    $duration       Lease duration in seconds
     *
     * @return self
     */
    public function acquireLease(string $holderIdentity, int $duration = 15): self
    {
        $currentTime = date('c'); // RFC3339 format

        return $this
            ->setHolderIdentity($holderIdentity)
            ->setLeaseDurationSeconds($duration)
            ->setAcquireTime($currentTime)
            ->setRenewTime($currentTime);
    }

    /**
     * Set the acquire time for the lease.
     *
     * @param string $acquireTime RFC3339 timestamp when the lease was acquired
     *
     * @return self
     */
    public function setAcquireTime(string $acquireTime): self
    {
        $this->spec['acquireTime'] = $acquireTime;
        return $this;
    }

    /**
     * Set the lease duration in seconds.
     *
     * @param int $leaseDurationSeconds Duration of the lease in seconds
     *
     * @return self
     */
    public function setLeaseDurationSeconds(int $leaseDurationSeconds): self
    {
        $this->spec['leaseDurationSeconds'] = $leaseDurationSeconds;
        return $this;
    }

    /**
     * Set the holder identity for the lease.
     *
     * @param string $holderIdentity Identifier of the current lease holder
     *
     * @return self
     */
    public function setHolderIdentity(string $holderIdentity): self
    {
        $this->spec['holderIdentity'] = $holderIdentity;
        return $this;
    }

    /**
     * Helper method to create a coordination lease for controller managers.
     *
     * @param string $controllerName Name of the controller
     * @param string $instanceId     Unique identifier for this controller instance
     * @param int    $duration       Lease duration in seconds
     *
     * @return self
     */
    public function createControllerLease(
        string $controllerName,
        string $instanceId,
        int $duration = 30
    ): self {
        return $this
            ->setName($controllerName)
            ->setNamespace('kube-system')
            ->acquireLease($instanceId, $duration)
            ->setLeaseTransitions(0);
    }
}
