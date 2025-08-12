<?php

declare(strict_types=1);

use Kubernetes\API\Core\V1\LimitRange;

it('can create a limit range resource', function (): void {
    $limitRange = new LimitRange();
    expect($limitRange->getApiVersion())->toBe('v1');
    expect($limitRange->getKind())->toBe('LimitRange');
});

it('can set and get namespace', function (): void {
    $limitRange = new LimitRange();
    $result = $limitRange->setNamespace('test-namespace');

    expect($result)->toBe($limitRange);
    expect($limitRange->getNamespace())->toBe('test-namespace');
});

it('can set and get limits', function (): void {
    $limitRange = new LimitRange();
    $limits = [
        [
            'type'           => 'Container',
            'default'        => ['cpu' => '200m', 'memory' => '256Mi'],
            'defaultRequest' => ['cpu' => '100m', 'memory' => '128Mi'],
        ],
    ];

    $result = $limitRange->setLimits($limits);

    expect($result)->toBe($limitRange);
    expect($limitRange->getLimits())->toBe($limits);
});

it('can add a limit', function (): void {
    $limitRange = new LimitRange();
    $limit = [
        'type' => 'Pod',
        'max'  => ['cpu' => '2', 'memory' => '2Gi'],
    ];

    $result = $limitRange->addLimit($limit);

    expect($result)->toBe($limitRange);
    expect($limitRange->getLimits())->toHaveCount(1);
    expect($limitRange->getLimits()[0])->toBe($limit);
});

it('can add container limit with all parameters', function (): void {
    $limitRange = new LimitRange();

    $result = $limitRange->addContainerLimit(
        ['cpu' => '200m', 'memory' => '256Mi'],
        ['cpu' => '100m', 'memory' => '128Mi'],
        ['cpu' => '500m', 'memory' => '512Mi'],
        ['cpu' => '50m', 'memory' => '64Mi']
    );

    expect($result)->toBe($limitRange);
    expect($limitRange->getLimits())->toHaveCount(1);

    $limit = $limitRange->getLimits()[0];
    expect($limit['type'])->toBe('Container');
    expect($limit['default'])->toBe(['cpu' => '200m', 'memory' => '256Mi']);
    expect($limit['defaultRequest'])->toBe(['cpu' => '100m', 'memory' => '128Mi']);
    expect($limit['max'])->toBe(['cpu' => '500m', 'memory' => '512Mi']);
    expect($limit['min'])->toBe(['cpu' => '50m', 'memory' => '64Mi']);
});

it('can add container limit with minimal parameters', function (): void {
    $limitRange = new LimitRange();

    $result = $limitRange->addContainerLimit(['cpu' => '200m']);

    expect($result)->toBe($limitRange);
    expect($limitRange->getLimits())->toHaveCount(1);

    $limit = $limitRange->getLimits()[0];
    expect($limit['type'])->toBe('Container');
    expect($limit['default'])->toBe(['cpu' => '200m']);
    expect($limit)->not->toHaveKey('defaultRequest');
    expect($limit)->not->toHaveKey('max');
    expect($limit)->not->toHaveKey('min');
});

it('can add pod limit', function (): void {
    $limitRange = new LimitRange();

    $result = $limitRange->addPodLimit(
        ['cpu' => '2', 'memory' => '2Gi'],
        ['cpu' => '100m', 'memory' => '128Mi']
    );

    expect($result)->toBe($limitRange);
    expect($limitRange->getLimits())->toHaveCount(1);

    $limit = $limitRange->getLimits()[0];
    expect($limit['type'])->toBe('Pod');
    expect($limit['max'])->toBe(['cpu' => '2', 'memory' => '2Gi']);
    expect($limit['min'])->toBe(['cpu' => '100m', 'memory' => '128Mi']);
});

it('can add pvc limit', function (): void {
    $limitRange = new LimitRange();

    $result = $limitRange->addPvcLimit(
        ['storage' => '10Gi'],
        ['storage' => '1Gi']
    );

    expect($result)->toBe($limitRange);
    expect($limitRange->getLimits())->toHaveCount(1);

    $limit = $limitRange->getLimits()[0];
    expect($limit['type'])->toBe('PersistentVolumeClaim');
    expect($limit['max'])->toBe(['storage' => '10Gi']);
    expect($limit['min'])->toBe(['storage' => '1Gi']);
});

it('returns empty array when no limits are set', function (): void {
    $limitRange = new LimitRange();
    expect($limitRange->getLimits())->toBe([]);
});

it('can chain setter methods', function (): void {
    $limitRange = new LimitRange();
    $result = $limitRange
        ->setName('my-limit-range')
        ->setNamespace('default')
        ->addContainerLimit(['cpu' => '200m'])
        ->addPodLimit(['memory' => '2Gi']);

    expect($result)->toBe($limitRange);
    expect($limitRange->getLimits())->toHaveCount(2);
});
