<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\Core\V1;

use Kubernetes\API\Core\V1\Endpoints;

it('can create an endpoints resource', function (): void {
    $endpoints = new Endpoints();
    expect($endpoints->getApiVersion())->toBe('v1');
    expect($endpoints->getKind())->toBe('Endpoints');
});

it('can set and get namespace', function (): void {
    $endpoints = new Endpoints();
    $result = $endpoints->setNamespace('test-namespace');

    expect($result)->toBe($endpoints);
    expect($endpoints->getNamespace())->toBe('test-namespace');
});

it('can set and get subsets', function (): void {
    $endpoints = new Endpoints();
    $subsets = [
        [
            'addresses' => [
                ['ip' => '10.0.0.1'],
                ['ip' => '10.0.0.2'],
            ],
            'ports' => [
                ['port' => 80, 'protocol' => 'TCP'],
            ],
        ],
    ];

    $result = $endpoints->setSubsets($subsets);

    expect($result)->toBe($endpoints);
    expect($endpoints->getSubsets())->toBe($subsets);
});

it('can add a subset', function (): void {
    $endpoints = new Endpoints();
    $subset = [
        'addresses' => [['ip' => '10.0.0.1']],
        'ports'     => [['port' => 80, 'protocol' => 'TCP']],
    ];

    $result = $endpoints->addSubset($subset);

    expect($result)->toBe($endpoints);
    expect($endpoints->getSubsets())->toHaveCount(1);
    expect($endpoints->getSubsets()[0])->toBe($subset);
});

it('can get addresses from all subsets', function (): void {
    $endpoints = new Endpoints();
    $endpoints->addSubset([
        'addresses' => [['ip' => '10.0.0.1'], ['ip' => '10.0.0.2']],
        'ports'     => [['port' => 80]],
    ]);
    $endpoints->addSubset([
        'addresses' => [['ip' => '10.0.0.3']],
        'ports'     => [['port' => 8080]],
    ]);

    $addresses = $endpoints->getAddresses();

    expect($addresses)->toHaveCount(3);
    expect($addresses[0]['ip'])->toBe('10.0.0.1');
    expect($addresses[1]['ip'])->toBe('10.0.0.2');
    expect($addresses[2]['ip'])->toBe('10.0.0.3');
});

it('can get ports from all subsets', function (): void {
    $endpoints = new Endpoints();
    $endpoints->addSubset([
        'addresses' => [['ip' => '10.0.0.1']],
        'ports'     => [['port' => 80], ['port' => 443]],
    ]);
    $endpoints->addSubset([
        'addresses' => [['ip' => '10.0.0.2']],
        'ports'     => [['port' => 8080]],
    ]);

    $ports = $endpoints->getPorts();

    expect($ports)->toHaveCount(3);
    expect($ports[0]['port'])->toBe(80);
    expect($ports[1]['port'])->toBe(443);
    expect($ports[2]['port'])->toBe(8080);
});

it('returns empty arrays when no subsets are set', function (): void {
    $endpoints = new Endpoints();

    expect($endpoints->getSubsets())->toBe([]);
    expect($endpoints->getAddresses())->toBe([]);
    expect($endpoints->getPorts())->toBe([]);
});

it('can chain setter methods', function (): void {
    $endpoints = new Endpoints();
    $result = $endpoints
        ->setName('my-endpoints')
        ->setNamespace('default')
        ->setSubsets([]);

    expect($result)->toBe($endpoints);
});
