<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\StorageK8sIo\V1;

use Kubernetes\API\StorageK8sIo\V1\StorageClass;

it('can create a storage class', function (): void {
    $storageClass = new StorageClass();
    expect($storageClass->getApiVersion())->toBe('storage.k8s.io/v1');
    expect($storageClass->getKind())->toBe('StorageClass');
});

it('can set and get provisioner', function (): void {
    $storageClass = new StorageClass();
    $storageClass->setProvisioner('kubernetes.io/aws-ebs');

    expect($storageClass->getProvisioner())->toBe('kubernetes.io/aws-ebs');
});

it('can set and get parameters', function (): void {
    $storageClass = new StorageClass();
    $parameters = ['type' => 'gp2', 'zone' => 'us-east-1a'];
    $storageClass->setParameters($parameters);

    expect($storageClass->getParameters())->toBe($parameters);
});

it('can add individual parameters', function (): void {
    $storageClass = new StorageClass();
    $storageClass->addParameter('type', 'gp3');
    $storageClass->addParameter('iops', '3000');

    $parameters = $storageClass->getParameters();
    expect($parameters['type'])->toBe('gp3');
    expect($parameters['iops'])->toBe('3000');
});

it('can set reclaim policy', function (): void {
    $storageClass = new StorageClass();
    $storageClass->setReclaimPolicy('Retain');

    expect($storageClass->getReclaimPolicy())->toBe('Retain');
});

it('can set volume binding mode', function (): void {
    $storageClass = new StorageClass();
    $storageClass->setVolumeBindingMode('WaitForFirstConsumer');

    expect($storageClass->getVolumeBindingMode())->toBe('WaitForFirstConsumer');
});

it('can set allowed topologies', function (): void {
    $storageClass = new StorageClass();
    $topologies = [
        [
            'matchLabelExpressions' => [
                ['key' => 'kubernetes.io/zone', 'values' => ['us-east-1a']],
            ],
        ],
    ];
    $storageClass->setAllowedTopologies($topologies);

    expect($storageClass->getAllowedTopologies())->toBe($topologies);
});

it('can add topology constraint', function (): void {
    $storageClass = new StorageClass();
    $storageClass->addTopologyConstraint(['kubernetes.io/zone' => 'us-east-1a']);

    $topologies = $storageClass->getAllowedTopologies();
    expect($topologies)->toHaveCount(1);
    expect($topologies[0]['matchLabelExpressions'][0]['key'])->toBe('kubernetes.io/zone');
    expect($topologies[0]['matchLabelExpressions'][0]['values'])->toBe(['us-east-1a']);
});

it('can set volume expansion allowance', function (): void {
    $storageClass = new StorageClass();
    $storageClass->setAllowVolumeExpansion(true);

    expect($storageClass->getAllowVolumeExpansion())->toBe(true);
});

it('can set mount options', function (): void {
    $storageClass = new StorageClass();
    $mountOptions = ['debug', 'rsize=32768'];
    $storageClass->setMountOptions($mountOptions);

    expect($storageClass->getMountOptions())->toBe($mountOptions);
});

it('can add mount option', function (): void {
    $storageClass = new StorageClass();
    $storageClass->addMountOption('noatime');
    $storageClass->addMountOption('rsize=65536');

    $options = $storageClass->getMountOptions();
    expect($options)->toHaveCount(2);
    expect($options)->toContain('noatime');
    expect($options)->toContain('rsize=65536');
});

it('can chain setter methods', function (): void {
    $storageClass = new StorageClass();
    $result = $storageClass
        ->setName('fast-ssd')
        ->setProvisioner('kubernetes.io/aws-ebs')
        ->setReclaimPolicy('Delete')
        ->setAllowVolumeExpansion(true);

    expect($result)->toBe($storageClass);
    expect($storageClass->getName())->toBe('fast-ssd');
    expect($storageClass->getProvisioner())->toBe('kubernetes.io/aws-ebs');
});
