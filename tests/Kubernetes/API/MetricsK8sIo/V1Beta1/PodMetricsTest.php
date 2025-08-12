<?php

declare(strict_types=1);

namespace Kubernetes\Tests\API\MetricsK8sIo\V1Beta1;

use Kubernetes\API\MetricsK8sIo\V1Beta1\PodMetrics;

it('can create pod metrics', function (): void {
    $podMetrics = new PodMetrics();
    expect($podMetrics->getApiVersion())->toBe('metrics.k8s.io/v1beta1');
    expect($podMetrics->getKind())->toBe('PodMetrics');
});

it('uses namespaced resource trait', function (): void {
    $podMetrics = new PodMetrics();
    $podMetrics->setNamespace('monitoring');

    expect($podMetrics->getNamespace())->toBe('monitoring');
});

it('can set and get timestamp', function (): void {
    $podMetrics = new PodMetrics();
    $timestamp = '2023-01-01T12:00:00Z';
    $podMetrics->setTimestamp($timestamp);

    expect($podMetrics->getTimestamp())->toBe($timestamp);
});

it('can set and get window', function (): void {
    $podMetrics = new PodMetrics();
    $podMetrics->setWindow('1m');

    expect($podMetrics->getWindow())->toBe('1m');
});

it('can set and get containers', function (): void {
    $podMetrics = new PodMetrics();
    $containers = [
        ['name' => 'app', 'usage' => ['cpu' => '100m', 'memory' => '128Mi']],
        ['name' => 'sidecar', 'usage' => ['cpu' => '50m', 'memory' => '64Mi']],
    ];
    $podMetrics->setContainers($containers);

    expect($podMetrics->getContainers())->toBe($containers);
});

it('can add container metrics', function (): void {
    $podMetrics = new PodMetrics();
    $podMetrics->addContainerMetrics('app', ['cpu' => '200m', 'memory' => '256Mi']);
    $podMetrics->addContainerMetrics('sidecar', ['cpu' => '100m', 'memory' => '128Mi']);

    $containers = $podMetrics->getContainers();
    expect($containers)->toHaveCount(2);
    expect($containers[0]['name'])->toBe('app');
    expect($containers[0]['usage']['cpu'])->toBe('200m');
    expect($containers[1]['name'])->toBe('sidecar');
    expect($containers[1]['usage']['memory'])->toBe('128Mi');
});

it('can get CPU usage for specific container', function (): void {
    $podMetrics = new PodMetrics();
    $podMetrics->addContainerMetrics('app', ['cpu' => '300m', 'memory' => '512Mi']);
    $podMetrics->addContainerMetrics('sidecar', ['cpu' => '150m', 'memory' => '256Mi']);

    expect($podMetrics->getContainerCpuUsage('app'))->toBe('300m');
    expect($podMetrics->getContainerCpuUsage('sidecar'))->toBe('150m');
    expect($podMetrics->getContainerCpuUsage('nonexistent'))->toBeNull();
});

it('can get memory usage for specific container', function (): void {
    $podMetrics = new PodMetrics();
    $podMetrics->addContainerMetrics('app', ['cpu' => '300m', 'memory' => '512Mi']);
    $podMetrics->addContainerMetrics('sidecar', ['cpu' => '150m', 'memory' => '256Mi']);

    expect($podMetrics->getContainerMemoryUsage('app'))->toBe('512Mi');
    expect($podMetrics->getContainerMemoryUsage('sidecar'))->toBe('256Mi');
    expect($podMetrics->getContainerMemoryUsage('nonexistent'))->toBeNull();
});

it('can get total CPU usage', function (): void {
    $podMetrics = new PodMetrics();
    $podMetrics->addContainerMetrics('app', ['cpu' => '100m', 'memory' => '256Mi']);
    $podMetrics->addContainerMetrics('sidecar', ['cpu' => '50m', 'memory' => '128Mi']);

    expect($podMetrics->getTotalCpuUsage())->toBe('150m');
});

it('can get total memory usage', function (): void {
    $podMetrics = new PodMetrics();
    $podMetrics->addContainerMetrics('app', ['cpu' => '100m', 'memory' => '256Mi']);
    $podMetrics->addContainerMetrics('sidecar', ['cpu' => '50m', 'memory' => '128Mi']);

    expect($podMetrics->getTotalMemoryUsage())->toBe('384Mi');
});

it('returns null for total usage when no containers', function (): void {
    $podMetrics = new PodMetrics();

    expect($podMetrics->getTotalCpuUsage())->toBeNull();
    expect($podMetrics->getTotalMemoryUsage())->toBeNull();
});

it('can chain setter methods', function (): void {
    $podMetrics = new PodMetrics();
    $result = $podMetrics
        ->setName('test-pod-metrics')
        ->setNamespace('default')
        ->setTimestamp('2023-01-01T12:00:00Z')
        ->setWindow('30s');

    expect($result)->toBe($podMetrics);
    expect($podMetrics->getName())->toBe('test-pod-metrics');
    expect($podMetrics->getNamespace())->toBe('default');
});
