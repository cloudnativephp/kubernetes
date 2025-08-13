<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\MetricsK8sIo\V1Beta1;

use Kubernetes\API\MetricsK8sIo\V1Beta1\NodeMetrics;

it('can create node metrics', function (): void {
    $nodeMetrics = new NodeMetrics();
    expect($nodeMetrics->getApiVersion())->toBe('metrics.k8s.io/v1beta1');
    expect($nodeMetrics->getKind())->toBe('NodeMetrics');
});

it('can set and get timestamp', function (): void {
    $nodeMetrics = new NodeMetrics();
    $timestamp = '2023-01-01T12:00:00Z';
    $nodeMetrics->setTimestamp($timestamp);

    expect($nodeMetrics->getTimestamp())->toBe($timestamp);
});

it('can set and get window', function (): void {
    $nodeMetrics = new NodeMetrics();
    $nodeMetrics->setWindow('1m');

    expect($nodeMetrics->getWindow())->toBe('1m');
});

it('can set and get usage', function (): void {
    $nodeMetrics = new NodeMetrics();
    $usage = ['cpu' => '2.5', 'memory' => '8Gi'];
    $nodeMetrics->setUsage($usage);

    expect($nodeMetrics->getUsage())->toBe($usage);
});

it('can set CPU usage individually', function (): void {
    $nodeMetrics = new NodeMetrics();
    $nodeMetrics->setCpuUsage('1.8');

    expect($nodeMetrics->getCpuUsage())->toBe('1.8');
});

it('can set memory usage individually', function (): void {
    $nodeMetrics = new NodeMetrics();
    $nodeMetrics->setMemoryUsage('6Gi');

    expect($nodeMetrics->getMemoryUsage())->toBe('6Gi');
});

it('can check if CPU usage is high', function (): void {
    $nodeMetrics = new NodeMetrics();
    $nodeMetrics->setCpuUsage('800m');

    expect($nodeMetrics->isCpuUsageHigh('700m'))->toBe(true);
    expect($nodeMetrics->isCpuUsageHigh('900m'))->toBe(false);
});

it('can check if memory usage is high', function (): void {
    $nodeMetrics = new NodeMetrics();
    $nodeMetrics->setMemoryUsage('4Gi');

    expect($nodeMetrics->isMemoryUsageHigh('3Gi'))->toBe(true);
    expect($nodeMetrics->isMemoryUsageHigh('5Gi'))->toBe(false);
});

it('returns false for high usage checks when no usage set', function (): void {
    $nodeMetrics = new NodeMetrics();

    expect($nodeMetrics->isCpuUsageHigh('50%'))->toBe(false);
    expect($nodeMetrics->isMemoryUsageHigh('80%'))->toBe(false);
});

it('can chain setter methods', function (): void {
    $nodeMetrics = new NodeMetrics();
    $result = $nodeMetrics
        ->setName('node-1-metrics')
        ->setTimestamp('2023-01-01T12:00:00Z')
        ->setWindow('30s')
        ->setCpuUsage('2.1')
        ->setMemoryUsage('7Gi');

    expect($result)->toBe($nodeMetrics);
    expect($nodeMetrics->getName())->toBe('node-1-metrics');
    expect($nodeMetrics->getCpuUsage())->toBe('2.1');
    expect($nodeMetrics->getMemoryUsage())->toBe('7Gi');
});
