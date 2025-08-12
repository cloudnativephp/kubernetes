<?php

declare(strict_types=1);

use Kubernetes\API\Core\V1\ResourceQuota;

it('can create a resource quota resource', function (): void {
    $resourceQuota = new ResourceQuota();
    expect($resourceQuota->getApiVersion())->toBe('v1');
    expect($resourceQuota->getKind())->toBe('ResourceQuota');
});

it('can set and get namespace', function (): void {
    $resourceQuota = new ResourceQuota();
    $result = $resourceQuota->setNamespace('test-namespace');

    expect($result)->toBe($resourceQuota);
    expect($resourceQuota->getNamespace())->toBe('test-namespace');
});

it('can set and get hard limits', function (): void {
    $resourceQuota = new ResourceQuota();
    $hard = [
        'requests.cpu'    => '4',
        'requests.memory' => '8Gi',
        'limits.cpu'      => '8',
        'limits.memory'   => '16Gi',
    ];

    $result = $resourceQuota->setHard($hard);

    expect($result)->toBe($resourceQuota);
    expect($resourceQuota->getHard())->toBe($hard);
});

it('can add hard limit', function (): void {
    $resourceQuota = new ResourceQuota();

    $result = $resourceQuota->addHardLimit('requests.cpu', '2');

    expect($result)->toBe($resourceQuota);
    expect($resourceQuota->getHard())->toBe(['requests.cpu' => '2']);
});

it('can set and get scope selector', function (): void {
    $resourceQuota = new ResourceQuota();
    $scopeSelector = [
        'matchExpressions' => [
            [
                'operator'  => 'In',
                'scopeName' => 'PriorityClass',
                'values'    => ['high'],
            ],
        ],
    ];

    $result = $resourceQuota->setScopeSelector($scopeSelector);

    expect($result)->toBe($resourceQuota);
    expect($resourceQuota->getScopeSelector())->toBe($scopeSelector);
});

it('can set and get scopes', function (): void {
    $resourceQuota = new ResourceQuota();
    $scopes = ['Terminating', 'NotTerminating'];

    $result = $resourceQuota->setScopes($scopes);

    expect($result)->toBe($resourceQuota);
    expect($resourceQuota->getScopes())->toBe($scopes);
});

it('can add scope', function (): void {
    $resourceQuota = new ResourceQuota();

    $result = $resourceQuota->addScope('Terminating');

    expect($result)->toBe($resourceQuota);
    expect($resourceQuota->getScopes())->toBe(['Terminating']);
});

it('can set compute limits', function (): void {
    $resourceQuota = new ResourceQuota();

    $result = $resourceQuota->setComputeLimits('8', '16Gi');

    expect($result)->toBe($resourceQuota);
    $hard = $resourceQuota->getHard();
    expect($hard['limits.cpu'])->toBe('8');
    expect($hard['limits.memory'])->toBe('16Gi');
});

it('can set compute requests', function (): void {
    $resourceQuota = new ResourceQuota();

    $result = $resourceQuota->setComputeRequests('4', '8Gi');

    expect($result)->toBe($resourceQuota);
    $hard = $resourceQuota->getHard();
    expect($hard['requests.cpu'])->toBe('4');
    expect($hard['requests.memory'])->toBe('8Gi');
});

it('can set object limits', function (): void {
    $resourceQuota = new ResourceQuota();

    $result = $resourceQuota->setObjectLimits(10, 5, 20, 15);

    expect($result)->toBe($resourceQuota);
    $hard = $resourceQuota->getHard();
    expect($hard['count/pods'])->toBe('10');
    expect($hard['count/services'])->toBe('5');
    expect($hard['count/secrets'])->toBe('20');
    expect($hard['count/configmaps'])->toBe('15');
});

it('can set object limits with zero values', function (): void {
    $resourceQuota = new ResourceQuota();

    $result = $resourceQuota->setObjectLimits(5, 0, 0, 10);

    expect($result)->toBe($resourceQuota);
    $hard = $resourceQuota->getHard();
    expect($hard['count/pods'])->toBe('5');
    expect($hard['count/configmaps'])->toBe('10');
    expect($hard)->not->toHaveKey('count/services');
    expect($hard)->not->toHaveKey('count/secrets');
});

it('returns empty arrays when nothing is set', function (): void {
    $resourceQuota = new ResourceQuota();

    expect($resourceQuota->getHard())->toBe([]);
    expect($resourceQuota->getScopes())->toBe([]);
    expect($resourceQuota->getScopeSelector())->toBeNull();
    expect($resourceQuota->getUsed())->toBe([]);
});

it('can chain setter methods', function (): void {
    $resourceQuota = new ResourceQuota();
    $result = $resourceQuota
        ->setName('my-quota')
        ->setNamespace('default')
        ->setComputeLimits('8', '16Gi')
        ->setObjectLimits(10, 5)
        ->addScope('Terminating');

    expect($result)->toBe($resourceQuota);
});
