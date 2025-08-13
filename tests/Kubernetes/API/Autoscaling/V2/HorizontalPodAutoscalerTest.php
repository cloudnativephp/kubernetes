<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\Autoscaling\V2;

use Kubernetes\API\Autoscaling\V2\HorizontalPodAutoscaler;

it('can create a horizontal pod autoscaler v2', function (): void {
    $hpa = new HorizontalPodAutoscaler();
    expect($hpa->getApiVersion())->toBe('autoscaling/v2');
    expect($hpa->getKind())->toBe('HorizontalPodAutoscaler');
});

it('can set and get namespace', function (): void {
    $hpa = new HorizontalPodAutoscaler();
    $hpa->setNamespace('production');
    expect($hpa->getNamespace())->toBe('production');
});

it('can chain setter methods', function (): void {
    $hpa = new HorizontalPodAutoscaler();
    $result = $hpa
        ->setName('web-hpa')
        ->setNamespace('default')
        ->setMinReplicas(2)
        ->setMaxReplicas(10);

    expect($result)->toBe($hpa);
});

it('can set target reference', function (): void {
    $hpa = new HorizontalPodAutoscaler();
    $hpa->setTargetRef('Deployment', 'web-app');

    $targetRef = $hpa->getTargetRef();
    expect($targetRef['kind'])->toBe('Deployment');
    expect($targetRef['name'])->toBe('web-app');
    expect($targetRef['apiVersion'])->toBe('apps/v1');
});

it('can add CPU metric', function (): void {
    $hpa = new HorizontalPodAutoscaler();
    $hpa->addCpuMetric(70);

    $metrics = $hpa->getMetrics();
    expect($metrics)->toHaveCount(1);
    expect($metrics[0]['type'])->toBe('Resource');
    expect($metrics[0]['resource']['name'])->toBe('cpu');
    expect($metrics[0]['resource']['target']['averageUtilization'])->toBe(70);
});

it('can add memory metric', function (): void {
    $hpa = new HorizontalPodAutoscaler();
    $hpa->addMemoryMetric(80);

    $metrics = $hpa->getMetrics();
    expect($metrics)->toHaveCount(1);
    expect($metrics[0]['type'])->toBe('Resource');
    expect($metrics[0]['resource']['name'])->toBe('memory');
    expect($metrics[0]['resource']['target']['averageUtilization'])->toBe(80);
});

it('can add multiple metrics', function (): void {
    $hpa = new HorizontalPodAutoscaler();
    $hpa->addCpuMetric(70)
        ->addMemoryMetric(80);

    $metrics = $hpa->getMetrics();
    expect($metrics)->toHaveCount(2);
    expect($metrics[0]['resource']['name'])->toBe('cpu');
    expect($metrics[1]['resource']['name'])->toBe('memory');
});

it('validates CPU metric percentage range', function (): void {
    $hpa = new HorizontalPodAutoscaler();

    expect(fn () => $hpa->addCpuMetric(0))
        ->toThrow(InvalidArgumentException::class, 'CPU target percentage must be between 1-100');

    expect(fn () => $hpa->addCpuMetric(101))
        ->toThrow(InvalidArgumentException::class, 'CPU target percentage must be between 1-100');
});

it('validates memory metric percentage range', function (): void {
    $hpa = new HorizontalPodAutoscaler();

    expect(fn () => $hpa->addMemoryMetric(0))
        ->toThrow(InvalidArgumentException::class, 'Memory target percentage must be between 1-100');

    expect(fn () => $hpa->addMemoryMetric(101))
        ->toThrow(InvalidArgumentException::class, 'Memory target percentage must be between 1-100');
});

it('can add pods metric', function (): void {
    $hpa = new HorizontalPodAutoscaler();
    $hpa->addPodsMetric('requests_per_second', '100', ['app' => 'web']);

    $metrics = $hpa->getMetrics();
    expect($metrics)->toHaveCount(1);
    expect($metrics[0]['type'])->toBe('Pods');
    expect($metrics[0]['pods']['metric']['name'])->toBe('requests_per_second');
    expect($metrics[0]['pods']['target']['averageValue'])->toBe('100');
    expect($metrics[0]['pods']['metric']['selector']['matchLabels']['app'])->toBe('web');
});

it('can add object metric', function (): void {
    $hpa = new HorizontalPodAutoscaler();
    $hpa->addObjectMetric('queue_size', '50', 'Service', 'queue-service', 'v1');

    $metrics = $hpa->getMetrics();
    expect($metrics)->toHaveCount(1);
    expect($metrics[0]['type'])->toBe('Object');
    expect($metrics[0]['object']['metric']['name'])->toBe('queue_size');
    expect($metrics[0]['object']['target']['value'])->toBe('50');
    expect($metrics[0]['object']['describedObject']['kind'])->toBe('Service');
    expect($metrics[0]['object']['describedObject']['name'])->toBe('queue-service');
});

it('can add external metric', function (): void {
    $hpa = new HorizontalPodAutoscaler();
    $hpa->addExternalMetric('sqs_queue_length', '30', ['queue' => 'production']);

    $metrics = $hpa->getMetrics();
    expect($metrics)->toHaveCount(1);
    expect($metrics[0]['type'])->toBe('External');
    expect($metrics[0]['external']['metric']['name'])->toBe('sqs_queue_length');
    expect($metrics[0]['external']['target']['value'])->toBe('30');
    expect($metrics[0]['external']['metric']['selector']['matchLabels']['queue'])->toBe('production');
});

it('can set scale up behavior', function (): void {
    $hpa = new HorizontalPodAutoscaler();
    $hpa->setScaleUpBehavior(60, 2, 30);

    $behavior = $hpa->getBehavior();
    expect($behavior['scaleUp']['stabilizationWindowSeconds'])->toBe(60);
    expect($behavior['scaleUp']['policies'][0]['value'])->toBe(2);
    expect($behavior['scaleUp']['policies'][0]['periodSeconds'])->toBe(30);
});

it('can set scale down behavior', function (): void {
    $hpa = new HorizontalPodAutoscaler();
    $hpa->setScaleDownBehavior(600, 1, 60);

    $behavior = $hpa->getBehavior();
    expect($behavior['scaleDown']['stabilizationWindowSeconds'])->toBe(600);
    expect($behavior['scaleDown']['policies'][0]['value'])->toBe(1);
    expect($behavior['scaleDown']['policies'][0]['periodSeconds'])->toBe(60);
});

it('can configure resource scaling with helper method', function (): void {
    $hpa = new HorizontalPodAutoscaler();
    $hpa->configureResourceScaling('Deployment', 'api-server', 3, 20, 70, 80);

    $targetRef = $hpa->getTargetRef();
    expect($targetRef['kind'])->toBe('Deployment');
    expect($targetRef['name'])->toBe('api-server');
    expect($hpa->getMinReplicas())->toBe(3);
    expect($hpa->getMaxReplicas())->toBe(20);

    $metrics = $hpa->getMetrics();
    expect($metrics)->toHaveCount(2);
    expect($metrics[0]['resource']['name'])->toBe('cpu');
    expect($metrics[0]['resource']['target']['averageUtilization'])->toBe(70);
    expect($metrics[1]['resource']['name'])->toBe('memory');
    expect($metrics[1]['resource']['target']['averageUtilization'])->toBe(80);
});

it('can access status fields', function (): void {
    $hpa = new HorizontalPodAutoscaler();

    expect($hpa->getCurrentReplicas())->toBe(0);
    expect($hpa->getDesiredReplicas())->toBe(0);
    expect($hpa->getCurrentMetrics())->toBe([]);
    expect($hpa->getLastScaleTime())->toBeNull();
    expect($hpa->getConditions())->toBe([]);
    expect($hpa->canScale())->toBeFalse();
    expect($hpa->isScalingActive())->toBeFalse();
    expect($hpa->isScalingLimited())->toBeFalse();
});

it('can detect scaling conditions', function (): void {
    $hpa = new HorizontalPodAutoscaler();

    // Use reflection to simulate status with conditions (for testing purposes)
    $reflection = new ReflectionClass($hpa);
    $statusProperty = $reflection->getProperty('status');
    $statusProperty->setAccessible(true);
    $statusProperty->setValue($hpa, [
        'conditions' => [
            [
                'type'   => 'AbleToScale',
                'status' => 'True',
            ],
            [
                'type'   => 'ScalingActive',
                'status' => 'True',
            ],
            [
                'type'   => 'ScalingLimited',
                'status' => 'False',
            ],
        ],
    ]);

    expect($hpa->canScale())->toBeTrue();
    expect($hpa->isScalingActive())->toBeTrue();
    expect($hpa->isScalingLimited())->toBeFalse();
});

it('has default values', function (): void {
    $hpa = new HorizontalPodAutoscaler();

    expect($hpa->getMinReplicas())->toBe(1);
    expect($hpa->getMaxReplicas())->toBe(1);
    expect($hpa->getTargetRef())->toBe([]);
    expect($hpa->getMetrics())->toBe([]);
    expect($hpa->getBehavior())->toBe([]);
});
