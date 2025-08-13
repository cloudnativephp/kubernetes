<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\Autoscaling\V1;

use Kubernetes\API\Autoscaling\V1\HorizontalPodAutoscaler;

it('can create a horizontal pod autoscaler', function (): void {
    $hpa = new HorizontalPodAutoscaler();
    expect($hpa->getApiVersion())->toBe('autoscaling/v1');
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

it('can set min and max replicas', function (): void {
    $hpa = new HorizontalPodAutoscaler();
    $hpa->setMinReplicas(3)->setMaxReplicas(15);

    expect($hpa->getMinReplicas())->toBe(3);
    expect($hpa->getMaxReplicas())->toBe(15);
});

it('validates min replicas is at least 1', function (): void {
    $hpa = new HorizontalPodAutoscaler();

    expect(fn () => $hpa->setMinReplicas(0))
        ->toThrow(InvalidArgumentException::class, 'minReplicas must be at least 1');
});

it('validates max replicas is at least min replicas', function (): void {
    $hpa = new HorizontalPodAutoscaler();
    $hpa->setMinReplicas(5);

    expect(fn () => $hpa->setMaxReplicas(3))
        ->toThrow(InvalidArgumentException::class, 'maxReplicas (3) must be >= minReplicas (5)');
});

it('can set target CPU utilization percentage', function (): void {
    $hpa = new HorizontalPodAutoscaler();
    $hpa->setTargetCPUUtilizationPercentage(70);

    expect($hpa->getTargetCPUUtilizationPercentage())->toBe(70);
});

it('validates CPU utilization percentage range', function (): void {
    $hpa = new HorizontalPodAutoscaler();

    expect(fn () => $hpa->setTargetCPUUtilizationPercentage(0))
        ->toThrow(InvalidArgumentException::class, 'targetCPUUtilizationPercentage must be between 1-100');

    expect(fn () => $hpa->setTargetCPUUtilizationPercentage(101))
        ->toThrow(InvalidArgumentException::class, 'targetCPUUtilizationPercentage must be between 1-100');
});

it('can configure CPU scaling with helper method', function (): void {
    $hpa = new HorizontalPodAutoscaler();
    $hpa->configureCPUScaling('Deployment', 'web-app', 2, 10, 80);

    $targetRef = $hpa->getTargetRef();
    expect($targetRef['kind'])->toBe('Deployment');
    expect($targetRef['name'])->toBe('web-app');
    expect($hpa->getMinReplicas())->toBe(2);
    expect($hpa->getMaxReplicas())->toBe(10);
    expect($hpa->getTargetCPUUtilizationPercentage())->toBe(80);
});

it('has default values', function (): void {
    $hpa = new HorizontalPodAutoscaler();

    expect($hpa->getMinReplicas())->toBe(1);
    expect($hpa->getMaxReplicas())->toBe(1);
    expect($hpa->getTargetCPUUtilizationPercentage())->toBeNull();
    expect($hpa->getTargetRef())->toBe([]);
});

it('can access status fields', function (): void {
    $hpa = new HorizontalPodAutoscaler();

    expect($hpa->getCurrentReplicas())->toBe(0);
    expect($hpa->getDesiredReplicas())->toBe(0);
    expect($hpa->getCurrentCPUUtilizationPercentage())->toBeNull();
    expect($hpa->getLastScaleTime())->toBeNull();
    expect($hpa->getConditions())->toBe([]);
    expect($hpa->canScale())->toBeFalse();
    expect($hpa->isScalingActive())->toBeFalse();
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
        ],
    ]);

    expect($hpa->canScale())->toBeTrue();
    expect($hpa->isScalingActive())->toBeTrue();
});
