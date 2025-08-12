<?php

declare(strict_types=1);

namespace Kubernetes\Tests\API\StorageK8sIo\V1;

use Kubernetes\API\StorageK8sIo\V1\VolumeAttachment;
use ReflectionClass;

it('can create a volume attachment', function (): void {
    $volumeAttachment = new VolumeAttachment();
    expect($volumeAttachment->getApiVersion())->toBe('storage.k8s.io/v1');
    expect($volumeAttachment->getKind())->toBe('VolumeAttachment');
});

it('can set and get attacher', function (): void {
    $volumeAttachment = new VolumeAttachment();
    $volumeAttachment->setAttacher('ebs.csi.aws.com');

    expect($volumeAttachment->getAttacher())->toBe('ebs.csi.aws.com');
});

it('can set and get node name', function (): void {
    $volumeAttachment = new VolumeAttachment();
    $volumeAttachment->setNodeName('worker-node-1');

    expect($volumeAttachment->getNodeName())->toBe('worker-node-1');
});

it('can set CSI volume source', function (): void {
    $volumeAttachment = new VolumeAttachment();
    $volumeAttachment->setCsiSource('ebs.csi.aws.com', 'vol-123456789', ['fsType' => 'ext4']);

    $source = $volumeAttachment->getSource();
    expect($source['csi']['driver'])->toBe('ebs.csi.aws.com');
    expect($source['csi']['volumeHandle'])->toBe('vol-123456789');
    expect($source['csi']['volumeAttributes']['fsType'])->toBe('ext4');
});

it('can set persistent volume source', function (): void {
    $volumeAttachment = new VolumeAttachment();
    $volumeAttachment->setPersistentVolumeSource('pv-test');

    $source = $volumeAttachment->getSource();
    expect($source['persistentVolumeName'])->toBe('pv-test');
});

it('can check attachment status', function (): void {
    $volumeAttachment = new VolumeAttachment();

    // Use reflection to set protected status property
    $reflection = new ReflectionClass($volumeAttachment);
    $statusProperty = $reflection->getProperty('status');
    $statusProperty->setAccessible(true);
    $statusProperty->setValue($volumeAttachment, ['attached' => true]);

    expect($volumeAttachment->isAttached())->toBe(true);
});

it('can get attachment metadata', function (): void {
    $volumeAttachment = new VolumeAttachment();
    $metadata = ['devicePath' => '/dev/xvdf'];

    // Use reflection to set protected status property
    $reflection = new ReflectionClass($volumeAttachment);
    $statusProperty = $reflection->getProperty('status');
    $statusProperty->setAccessible(true);
    $statusProperty->setValue($volumeAttachment, ['attachmentMetadata' => $metadata]);

    expect($volumeAttachment->getAttachmentMetadata())->toBe($metadata);
});

it('can get attach and detach errors', function (): void {
    $volumeAttachment = new VolumeAttachment();
    $attachError = ['message' => 'Failed to attach volume'];
    $detachError = ['message' => 'Failed to detach volume'];

    // Use reflection to set protected status property
    $reflection = new ReflectionClass($volumeAttachment);
    $statusProperty = $reflection->getProperty('status');
    $statusProperty->setAccessible(true);
    $statusProperty->setValue($volumeAttachment, [
        'attachError' => $attachError,
        'detachError' => $detachError,
    ]);

    expect($volumeAttachment->getAttachError())->toBe($attachError);
    expect($volumeAttachment->getDetachError())->toBe($detachError);
});

it('can chain setter methods', function (): void {
    $volumeAttachment = new VolumeAttachment();
    $result = $volumeAttachment
        ->setName('test-attachment')
        ->setAttacher('ebs.csi.aws.com')
        ->setNodeName('worker-1');

    expect($result)->toBe($volumeAttachment);
    expect($volumeAttachment->getName())->toBe('test-attachment');
    expect($volumeAttachment->getAttacher())->toBe('ebs.csi.aws.com');
});
