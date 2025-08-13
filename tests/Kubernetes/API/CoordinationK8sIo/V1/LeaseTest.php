<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\CoordinationK8sIo\V1;

use Kubernetes\API\CoordinationK8sIo\V1\Lease;

it('can create a Lease', function (): void {
    $lease = new Lease();
    expect($lease->getApiVersion())->toBe('coordination.k8s.io/v1');
    expect($lease->getKind())->toBe('Lease');
});

it('can set namespace', function (): void {
    $lease = new Lease();
    $result = $lease->setNamespace('kube-system');
    expect($result)->toBe($lease);
    expect($lease->getNamespace())->toBe('kube-system');
});

it('can set and get holder identity', function (): void {
    $lease = new Lease();
    $lease->setHolderIdentity('controller-manager-1');
    expect($lease->getHolderIdentity())->toBe('controller-manager-1');
});

it('can set and get lease duration seconds', function (): void {
    $lease = new Lease();
    $lease->setLeaseDurationSeconds(30);
    expect($lease->getLeaseDurationSeconds())->toBe(30);
});

it('can set and get acquire time', function (): void {
    $lease = new Lease();
    $time = '2025-08-12T12:00:00Z';
    $lease->setAcquireTime($time);
    expect($lease->getAcquireTime())->toBe($time);
});

it('can set and get renew time', function (): void {
    $lease = new Lease();
    $time = '2025-08-12T12:05:00Z';
    $lease->setRenewTime($time);
    expect($lease->getRenewTime())->toBe($time);
});

it('can set and get lease transitions', function (): void {
    $lease = new Lease();
    $lease->setLeaseTransitions(5);
    expect($lease->getLeaseTransitions())->toBe(5);
});

it('can acquire lease', function (): void {
    $lease = new Lease();
    $result = $lease->acquireLease('holder-1', 60);

    expect($result)->toBe($lease);
    expect($lease->getHolderIdentity())->toBe('holder-1');
    expect($lease->getLeaseDurationSeconds())->toBe(60);
    expect($lease->getAcquireTime())->not->toBeNull();
    expect($lease->getRenewTime())->not->toBeNull();
});

it('can renew lease', function (): void {
    $lease = new Lease();
    $lease->setRenewTime('2025-08-12T12:00:00Z');

    $result = $lease->renewLease();
    expect($result)->toBe($lease);
    expect($lease->getRenewTime())->not->toBe('2025-08-12T12:00:00Z');
});

it('can check if held by specific holder', function (): void {
    $lease = new Lease();
    $lease->setHolderIdentity('holder-1');

    expect($lease->isHeldBy('holder-1'))->toBe(true);
    expect($lease->isHeldBy('holder-2'))->toBe(false);
});

it('can check if lease is expired', function (): void {
    $lease = new Lease();

    // No time set - should be expired
    expect($lease->isExpired())->toBe(true);

    // Set recent time - should not be expired
    $lease->setRenewTime(date('c'))
        ->setLeaseDurationSeconds(3600);
    expect($lease->isExpired())->toBe(false);
});

it('can get expiry time', function (): void {
    $lease = new Lease();
    expect($lease->getExpiryTime())->toBeNull();

    $renewTime = '2025-08-12T12:00:00Z';
    $lease->setRenewTime($renewTime)
        ->setLeaseDurationSeconds(3600);

    $expectedExpiry = strtotime($renewTime) + 3600;
    expect($lease->getExpiryTime())->toBe($expectedExpiry);
});

it('can get remaining time', function (): void {
    $lease = new Lease();
    expect($lease->getRemainingTime())->toBeNull();

    // Set future expiry
    $futureTime = date('c', time() + 1800); // 30 minutes from now
    $lease->setRenewTime($futureTime)
        ->setLeaseDurationSeconds(3600);

    $remaining = $lease->getRemainingTime();
    expect($remaining)->toBeGreaterThan(0);
});

it('can create leader election lease', function (): void {
    $lease = new Lease();
    $result = $lease->createLeaderElectionLease('controller-manager', 'instance-1', 'kube-system', 30);

    expect($result)->toBe($lease);
    expect($lease->getName())->toBe('controller-manager-leader');
    expect($lease->getNamespace())->toBe('kube-system');
    expect($lease->getHolderIdentity())->toBe('instance-1');
    expect($lease->getLeaseDurationSeconds())->toBe(30);
    expect($lease->getLeaseTransitions())->toBe(0);
});

it('can create controller lease', function (): void {
    $lease = new Lease();
    $result = $lease->createControllerLease('my-controller', 'pod-123', 45);

    expect($result)->toBe($lease);
    expect($lease->getName())->toBe('my-controller');
    expect($lease->getNamespace())->toBe('kube-system');
    expect($lease->getHolderIdentity())->toBe('pod-123');
    expect($lease->getLeaseDurationSeconds())->toBe(45);
    expect($lease->getLeaseTransitions())->toBe(0);
});

it('can chain setter methods', function (): void {
    $lease = new Lease();
    $result = $lease
        ->setName('test-lease')
        ->setNamespace('default')
        ->setHolderIdentity('holder-1')
        ->setLeaseDurationSeconds(30);

    expect($result)->toBe($lease);
});
