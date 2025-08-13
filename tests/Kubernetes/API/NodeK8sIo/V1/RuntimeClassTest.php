<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\NodeK8sIo\V1;

use Kubernetes\API\NodeK8sIo\V1\RuntimeClass;

it('can create a runtime class', function (): void {
    $runtimeClass = new RuntimeClass();
    expect($runtimeClass->getApiVersion())->toBe('node.k8s.io/v1');
    expect($runtimeClass->getKind())->toBe('RuntimeClass');
});

it('can set and get handler', function (): void {
    $runtimeClass = new RuntimeClass();
    $runtimeClass->setHandler('runc');

    expect($runtimeClass->getHandler())->toBe('runc');
});

it('can set scheduling constraints', function (): void {
    $runtimeClass = new RuntimeClass();
    $scheduling = [
        'nodeSelector' => ['kubernetes.io/arch' => 'amd64'],
        'tolerations'  => [
            ['key' => 'special-node', 'operator' => 'Equal', 'value' => 'true', 'effect' => 'NoSchedule'],
        ],
    ];

    $runtimeClass->setScheduling($scheduling);
    expect($runtimeClass->getScheduling())->toBe($scheduling);
});

it('can add node selector', function (): void {
    $runtimeClass = new RuntimeClass();
    $runtimeClass->addNodeSelector(['kubernetes.io/arch' => 'amd64']);

    $scheduling = $runtimeClass->getScheduling();
    expect($scheduling)->not->toBeNull();
    if ($scheduling !== null && isset($scheduling['nodeSelector'])) {
        expect($scheduling['nodeSelector']['kubernetes.io/arch'])->toBe('amd64');
    }
});

it('can add toleration', function (): void {
    $runtimeClass = new RuntimeClass();
    $toleration = ['key' => 'special-node', 'operator' => 'Equal', 'value' => 'true', 'effect' => 'NoSchedule'];
    $runtimeClass->addToleration($toleration);

    $scheduling = $runtimeClass->getScheduling();
    expect($scheduling)->not->toBeNull();
    if ($scheduling !== null && isset($scheduling['tolerations'])) {
        expect($scheduling['tolerations'])->toHaveCount(1);
        expect($scheduling['tolerations'][0])->toBe($toleration);
    }
});

it('can create toleration with helper method', function (): void {
    $runtimeClass = new RuntimeClass();
    $toleration = $runtimeClass->createToleration('gpu', 'Equal', 'nvidia', 'NoSchedule', 300);

    expect($toleration)->toBe([
        'key'               => 'gpu',
        'operator'          => 'Equal',
        'value'             => 'nvidia',
        'effect'            => 'NoSchedule',
        'tolerationSeconds' => 300,
    ]);
});

it('can set overhead configuration', function (): void {
    $runtimeClass = new RuntimeClass();
    $overhead = ['cpu' => '100m', 'memory' => '128Mi'];
    $runtimeClass->setOverhead($overhead);

    expect($runtimeClass->getOverhead())->toBe($overhead);
});

it('can set CPU and memory overhead separately', function (): void {
    $runtimeClass = new RuntimeClass();
    $runtimeClass->setCpuOverhead('50m');
    $runtimeClass->setMemoryOverhead('64Mi');

    $overhead = $runtimeClass->getOverhead();
    expect($overhead)->not->toBeNull();

    // PHPStan-friendly null check
    if ($overhead !== null) {
        expect($overhead['cpu'])->toBe('50m');
        expect($overhead['memory'])->toBe('64Mi');
    }
});

it('can chain setter methods', function (): void {
    $runtimeClass = new RuntimeClass();
    $result = $runtimeClass
        ->setName('test-runtime')
        ->setHandler('containerd')
        ->setCpuOverhead('100m')
        ->setMemoryOverhead('128Mi');

    expect($result)->toBe($runtimeClass);
    expect($runtimeClass->getName())->toBe('test-runtime');
    expect($runtimeClass->getHandler())->toBe('containerd');
});
