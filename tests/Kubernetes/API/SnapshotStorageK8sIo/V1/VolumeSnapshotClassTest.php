<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\SnapshotStorageK8sIo\V1;

use Kubernetes\API\SnapshotStorageK8sIo\V1\VolumeSnapshotClass;

it('can create a volume snapshot class', function (): void {
    $volumeSnapshotClass = new VolumeSnapshotClass();
    expect($volumeSnapshotClass->getApiVersion())->toBe('snapshot.storage.k8s.io/v1');
    expect($volumeSnapshotClass->getKind())->toBe('VolumeSnapshotClass');
});

it('can set and get driver', function (): void {
    $volumeSnapshotClass = new VolumeSnapshotClass();
    $volumeSnapshotClass->setDriver('ebs.csi.aws.com');

    expect($volumeSnapshotClass->getDriver())->toBe('ebs.csi.aws.com');
});

it('can set and get parameters', function (): void {
    $volumeSnapshotClass = new VolumeSnapshotClass();
    $parameters = ['type' => 'gp3', 'encrypted' => 'true'];
    $volumeSnapshotClass->setParameters($parameters);

    expect($volumeSnapshotClass->getParameters())->toBe($parameters);
});

it('can add individual parameters', function (): void {
    $volumeSnapshotClass = new VolumeSnapshotClass();
    $volumeSnapshotClass->addParameter('retention', '30d');
    $volumeSnapshotClass->addParameter('encrypted', 'true');

    $parameters = $volumeSnapshotClass->getParameters();
    expect($parameters['retention'])->toBe('30d');
    expect($parameters['encrypted'])->toBe('true');
});

it('can set and get deletion policy', function (): void {
    $volumeSnapshotClass = new VolumeSnapshotClass();
    $volumeSnapshotClass->setDeletionPolicy('Retain');

    expect($volumeSnapshotClass->getDeletionPolicy())->toBe('Retain');
});

it('can chain setter methods', function (): void {
    $volumeSnapshotClass = new VolumeSnapshotClass();
    $result = $volumeSnapshotClass
        ->setName('fast-snapshot-class')
        ->setDriver('ebs.csi.aws.com')
        ->setDeletionPolicy('Delete');

    expect($result)->toBe($volumeSnapshotClass);
    expect($volumeSnapshotClass->getName())->toBe('fast-snapshot-class');
    expect($volumeSnapshotClass->getDriver())->toBe('ebs.csi.aws.com');
});
