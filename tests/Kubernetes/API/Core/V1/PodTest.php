<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\Core\V1;

use Kubernetes\API\Core\V1\Pod;

it('can create a pod with basic configuration', function (): void {
    $pod = new Pod();

    expect($pod->getApiVersion())->toBe('v1');
    expect($pod->getKind())->toBe('Pod');
    expect($pod->getMetadata())->toBe([]);
    expect($pod->getContainers())->toBe([]);
});

it('can set and get pod metadata', function (): void {
    $pod = new Pod();
    $metadata = [
        'name'      => 'test-pod',
        'namespace' => 'default',
        'labels'    => ['app' => 'test'],
    ];

    $pod->setMetadata($metadata);

    expect($pod->getMetadata())->toBe($metadata);
    expect($pod->getName())->toBe('test-pod');
    expect($pod->getNamespace())->toBe('default');
    expect($pod->getLabels())->toBe(['app' => 'test']);
});

it('can add containers to pod spec', function (): void {
    $pod = new Pod();
    $container = [
        'name'  => 'app',
        'image' => 'nginx:latest',
        'ports' => [['containerPort' => 80]],
    ];

    $pod->addContainer($container);

    expect($pod->getContainers())->toBe([$container]);
});

it('can set restart policy', function (): void {
    $pod = new Pod();

    $pod->setRestartPolicy('Always');

    expect($pod->getRestartPolicy())->toBe('Always');
});

it('can convert to array', function (): void {
    $pod = new Pod();
    $pod->setName('test-pod')
        ->setNamespace('default')
        ->addContainer([
            'name'  => 'app',
            'image' => 'nginx:latest',
        ])
        ->setRestartPolicy('Always');

    $array = $pod->toArray();

    expect($array)->toHaveKey('apiVersion', 'v1');
    expect($array)->toHaveKey('kind', 'Pod');
    expect($array)->toHaveKey('metadata.name', 'test-pod');
    expect($array)->toHaveKey('metadata.namespace', 'default');
    expect($array)->toHaveKey('spec.containers');
    expect($array)->toHaveKey('spec.restartPolicy', 'Always');
});

it('can create from array', function (): void {
    $data = [
        'apiVersion' => 'v1',
        'kind'       => 'Pod',
        'metadata'   => [
            'name'      => 'test-pod',
            'namespace' => 'default',
        ],
        'spec' => [
            'containers' => [
                [
                    'name'  => 'app',
                    'image' => 'nginx:latest',
                ],
            ],
            'restartPolicy' => 'Always',
        ],
    ];

    $pod = Pod::fromArray($data);

    expect($pod->getName())->toBe('test-pod');
    expect($pod->getNamespace())->toBe('default');
    expect($pod->getContainers())->toBe($data['spec']['containers']);
    expect($pod->getRestartPolicy())->toBe('Always');
});
