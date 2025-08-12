<?php

declare(strict_types=1);

namespace Kubernetes\Tests\API\StorageK8sIo\V1;

use Kubernetes\API\StorageK8sIo\V1\CSIDriver;

it('can create a CSI driver', function (): void {
    $csiDriver = new CSIDriver();
    expect($csiDriver->getApiVersion())->toBe('storage.k8s.io/v1');
    expect($csiDriver->getKind())->toBe('CSIDriver');
});

it('can set and get attach required', function (): void {
    $csiDriver = new CSIDriver();
    $csiDriver->setAttachRequired(false);

    expect($csiDriver->getAttachRequired())->toBe(false);
});

it('defaults attach required to true', function (): void {
    $csiDriver = new CSIDriver();
    expect($csiDriver->getAttachRequired())->toBe(true);
});

it('can set and get pod info on mount', function (): void {
    $csiDriver = new CSIDriver();
    $csiDriver->setPodInfoOnMount(true);

    expect($csiDriver->getPodInfoOnMount())->toBe(true);
});

it('can set volume lifecycle modes', function (): void {
    $csiDriver = new CSIDriver();
    $modes = ['Persistent', 'Ephemeral'];
    $csiDriver->setVolumeLifecycleModes($modes);

    expect($csiDriver->getVolumeLifecycleModes())->toBe($modes);
});

it('can add volume lifecycle mode', function (): void {
    $csiDriver = new CSIDriver();
    $csiDriver->addVolumeLifecycleMode('Ephemeral');
    $csiDriver->addVolumeLifecycleMode('Persistent');

    $modes = $csiDriver->getVolumeLifecycleModes();
    expect($modes)->toContain('Ephemeral');
    expect($modes)->toContain('Persistent');
});

it('does not add duplicate lifecycle modes', function (): void {
    $csiDriver = new CSIDriver();
    $csiDriver->addVolumeLifecycleMode('Persistent');
    $csiDriver->addVolumeLifecycleMode('Persistent');

    $modes = $csiDriver->getVolumeLifecycleModes();
    expect($modes)->toHaveCount(1);
    expect($modes[0])->toBe('Persistent');
});

it('can set storage capacity tracking', function (): void {
    $csiDriver = new CSIDriver();
    $csiDriver->setStorageCapacity(true);

    expect($csiDriver->getStorageCapacity())->toBe(true);
});

it('can set filesystem group policy', function (): void {
    $csiDriver = new CSIDriver();
    $csiDriver->setFsGroupPolicy('File');

    expect($csiDriver->getFsGroupPolicy())->toBe('File');
});

it('can set token requests', function (): void {
    $csiDriver = new CSIDriver();
    $tokenRequests = ['audience1', 'audience2'];
    $csiDriver->setTokenRequests($tokenRequests);

    expect($csiDriver->getTokenRequests())->toBe($tokenRequests);
});

it('can set requires republish', function (): void {
    $csiDriver = new CSIDriver();
    $csiDriver->setRequiresRepublish(true);

    expect($csiDriver->getRequiresRepublish())->toBe(true);
});

it('can chain setter methods', function (): void {
    $csiDriver = new CSIDriver();
    $result = $csiDriver
        ->setName('test-driver')
        ->setAttachRequired(false)
        ->setPodInfoOnMount(true)
        ->setStorageCapacity(true);

    expect($result)->toBe($csiDriver);
    expect($csiDriver->getName())->toBe('test-driver');
});
