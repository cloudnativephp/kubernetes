<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\SchedulingK8sIo\V1;

use Kubernetes\API\SchedulingK8sIo\V1\PriorityClass;

it('can create a priority class', function (): void {
    $priorityClass = new PriorityClass();
    expect($priorityClass->getApiVersion())->toBe('scheduling.k8s.io/v1');
    expect($priorityClass->getKind())->toBe('PriorityClass');
});

it('can set and get priority value', function (): void {
    $priorityClass = new PriorityClass();
    $priorityClass->setValue(1000);

    expect($priorityClass->getValue())->toBe(1000);
});

it('can set and get global default', function (): void {
    $priorityClass = new PriorityClass();
    $priorityClass->setGlobalDefault(true);

    expect($priorityClass->getGlobalDefault())->toBe(true);
});

it('defaults global default to false', function (): void {
    $priorityClass = new PriorityClass();
    expect($priorityClass->getGlobalDefault())->toBe(false);
});

it('can set and get description', function (): void {
    $priorityClass = new PriorityClass();
    $description = 'High priority class for critical workloads';
    $priorityClass->setDescription($description);

    expect($priorityClass->getDescription())->toBe($description);
});

it('can set and get preemption policy', function (): void {
    $priorityClass = new PriorityClass();
    $priorityClass->setPreemptionPolicy('Never');

    expect($priorityClass->getPreemptionPolicy())->toBe('Never');
});

it('can create high priority class', function (): void {
    $priorityClass = new PriorityClass();
    $result = $priorityClass->createHighPriority('high-priority', 1000, 'Critical workloads');

    expect($result)->toBe($priorityClass);
    expect($priorityClass->getName())->toBe('high-priority');
    expect($priorityClass->getValue())->toBe(1000);
    expect($priorityClass->getDescription())->toBe('Critical workloads');
    expect($priorityClass->getPreemptionPolicy())->toBe('PreemptLowerPriority');
});

it('can create low priority class', function (): void {
    $priorityClass = new PriorityClass();
    $result = $priorityClass->createLowPriority('low-priority', -10, 'Low priority workloads');

    expect($result)->toBe($priorityClass);
    expect($priorityClass->getName())->toBe('low-priority');
    expect($priorityClass->getValue())->toBe(-10);
    expect($priorityClass->getDescription())->toBe('Low priority workloads');
    expect($priorityClass->getPreemptionPolicy())->toBe('Never');
});

it('can chain setter methods', function (): void {
    $priorityClass = new PriorityClass();
    $result = $priorityClass
        ->setName('test-priority')
        ->setValue(500)
        ->setDescription('Test priority class')
        ->setPreemptionPolicy('PreemptLowerPriority');

    expect($result)->toBe($priorityClass);
    expect($priorityClass->getName())->toBe('test-priority');
    expect($priorityClass->getValue())->toBe(500);
});
