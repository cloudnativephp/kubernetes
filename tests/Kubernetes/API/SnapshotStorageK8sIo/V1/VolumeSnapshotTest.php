<?php

declare(strict_types=1);

namespace Kubernetes\Tests\API\SnapshotStorageK8sIo\V1;

use Kubernetes\API\SnapshotStorageK8sIo\V1\VolumeSnapshot;
use ReflectionClass;

it('can create a volume snapshot', function (): void {
    $volumeSnapshot = new VolumeSnapshot();
    expect($volumeSnapshot->getApiVersion())->toBe('snapshot.storage.k8s.io/v1');
    expect($volumeSnapshot->getKind())->toBe('VolumeSnapshot');
});

it('uses namespaced resource trait', function (): void {
    $volumeSnapshot = new VolumeSnapshot();
    $volumeSnapshot->setNamespace('test-namespace');

    expect($volumeSnapshot->getNamespace())->toBe('test-namespace');
});

it('can set and get volume snapshot class name', function (): void {
    $volumeSnapshot = new VolumeSnapshot();
    $volumeSnapshot->setVolumeSnapshotClassName('fast-snapshot-class');

    expect($volumeSnapshot->getVolumeSnapshotClassName())->toBe('fast-snapshot-class');
});

it('can set persistent volume claim source', function (): void {
    $volumeSnapshot = new VolumeSnapshot();
    $volumeSnapshot->setPersistentVolumeClaimSource('my-pvc');

    $source = $volumeSnapshot->getSource();
    expect($source['persistentVolumeClaimName'])->toBe('my-pvc');
});

it('can set volume snapshot content source', function (): void {
    $volumeSnapshot = new VolumeSnapshot();
    $volumeSnapshot->setVolumeSnapshotContentSource('snap-content-123');

    $source = $volumeSnapshot->getSource();
    expect($source['volumeSnapshotContentName'])->toBe('snap-content-123');
});

it('can check if snapshot is ready to use', function (): void {
    $volumeSnapshot = new VolumeSnapshot();

    // Use reflection to set protected status property
    $reflection = new ReflectionClass($volumeSnapshot);
    $statusProperty = $reflection->getProperty('status');
    $statusProperty->setAccessible(true);
    $statusProperty->setValue($volumeSnapshot, ['readyToUse' => true]);

    expect($volumeSnapshot->isReadyToUse())->toBe(true);
});

it('can get bound volume snapshot content name', function (): void {
    $volumeSnapshot = new VolumeSnapshot();

    // Use reflection to set protected status property
    $reflection = new ReflectionClass($volumeSnapshot);
    $statusProperty = $reflection->getProperty('status');
    $statusProperty->setAccessible(true);
    $statusProperty->setValue($volumeSnapshot, ['boundVolumeSnapshotContentName' => 'snapcontent-123']);

    expect($volumeSnapshot->getBoundVolumeSnapshotContentName())->toBe('snapcontent-123');
});

it('can get creation time', function (): void {
    $volumeSnapshot = new VolumeSnapshot();

    // Use reflection to set protected status property
    $reflection = new ReflectionClass($volumeSnapshot);
    $statusProperty = $reflection->getProperty('status');
    $statusProperty->setAccessible(true);
    $statusProperty->setValue($volumeSnapshot, ['creationTime' => '2023-01-01T12:00:00Z']);

    expect($volumeSnapshot->getCreationTime())->toBe('2023-01-01T12:00:00Z');
});

it('can get restore size', function (): void {
    $volumeSnapshot = new VolumeSnapshot();

    // Use reflection to set protected status property
    $reflection = new ReflectionClass($volumeSnapshot);
    $statusProperty = $reflection->getProperty('status');
    $statusProperty->setAccessible(true);
    $statusProperty->setValue($volumeSnapshot, ['restoreSize' => '10Gi']);

    expect($volumeSnapshot->getRestoreSize())->toBe('10Gi');
});

it('can get snapshot error', function (): void {
    $volumeSnapshot = new VolumeSnapshot();
    $error = ['message' => 'Snapshot failed'];

    // Use reflection to set protected status property
    $reflection = new ReflectionClass($volumeSnapshot);
    $statusProperty = $reflection->getProperty('status');
    $statusProperty->setAccessible(true);
    $statusProperty->setValue($volumeSnapshot, ['error' => $error]);

    expect($volumeSnapshot->getError())->toBe($error);
});

it('can chain setter methods', function (): void {
    $volumeSnapshot = new VolumeSnapshot();
    $result = $volumeSnapshot
        ->setName('test-snapshot')
        ->setNamespace('default')
        ->setVolumeSnapshotClassName('fast-class');

    expect($result)->toBe($volumeSnapshot);
    expect($volumeSnapshot->getName())->toBe('test-snapshot');
    expect($volumeSnapshot->getNamespace())->toBe('default');
});
