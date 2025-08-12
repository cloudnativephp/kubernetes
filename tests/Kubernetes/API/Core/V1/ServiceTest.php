<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\Core\V1;

use Kubernetes\API\Core\V1\Service;

it('can create a service', function (): void {
    $service = new Service();

    expect($service->getApiVersion())->toBe('v1');
    expect($service->getKind())->toBe('Service');
});

it('can set and get service type', function (): void {
    $service = new Service();

    expect($service->getType())->toBeNull();

    $service->setType('ClusterIP');
    expect($service->getType())->toBe('ClusterIP');

    $service->setType('LoadBalancer');
    expect($service->getType())->toBe('LoadBalancer');
});

it('can set and get selector', function (): void {
    $service = new Service();
    $selector = ['app' => 'nginx', 'version' => 'v1'];

    expect($service->getSelector())->toBe([]);

    $service->setSelector($selector);
    expect($service->getSelector())->toBe($selector);
});

it('can set and get ports', function (): void {
    $service = new Service();
    $ports = [
        ['name' => 'http', 'port' => 80, 'targetPort' => 8080],
        ['name' => 'https', 'port' => 443, 'targetPort' => 8443],
    ];

    expect($service->getPorts())->toBe([]);

    $service->setPorts($ports);
    expect($service->getPorts())->toBe($ports);
});

it('can add individual ports', function (): void {
    $service = new Service();
    $port1 = ['name' => 'http', 'port' => 80, 'targetPort' => 8080];
    $port2 = ['name' => 'https', 'port' => 443, 'targetPort' => 8443];

    $service->addPort($port1);
    expect($service->getPorts())->toBe([$port1]);

    $service->addPort($port2);
    expect($service->getPorts())->toBe([$port1, $port2]);
});

it('can set and get cluster IP', function (): void {
    $service = new Service();
    $clusterIP = '10.0.0.1';

    expect($service->getClusterIP())->toBeNull();

    $service->setClusterIP($clusterIP);
    expect($service->getClusterIP())->toBe($clusterIP);
});

it('can set and get external IPs', function (): void {
    $service = new Service();
    $externalIPs = ['192.168.1.100', '192.168.1.101'];

    expect($service->getExternalIPs())->toBe([]);

    $service->setExternalIPs($externalIPs);
    expect($service->getExternalIPs())->toBe($externalIPs);
});

it('can set and get load balancer IP', function (): void {
    $service = new Service();
    $loadBalancerIP = '203.0.113.1';

    expect($service->getLoadBalancerIP())->toBeNull();

    $service->setLoadBalancerIP($loadBalancerIP);
    expect($service->getLoadBalancerIP())->toBe($loadBalancerIP);
});

it('can set and get session affinity', function (): void {
    $service = new Service();

    expect($service->getSessionAffinity())->toBeNull();

    $service->setSessionAffinity('ClientIP');
    expect($service->getSessionAffinity())->toBe('ClientIP');

    $service->setSessionAffinity('None');
    expect($service->getSessionAffinity())->toBe('None');
});

it('can set and get external name', function (): void {
    $service = new Service();
    $externalName = 'example.com';

    expect($service->getExternalName())->toBeNull();

    $service->setExternalName($externalName);
    expect($service->getExternalName())->toBe($externalName);
});

it('can get service status', function (): void {
    $service = new Service();

    expect($service->getStatus())->toBe([]);
    expect($service->getLoadBalancerStatus())->toBeNull();
});

it('can convert service to array', function (): void {
    $service = new Service();
    $service->setName('my-service');
    $service->setNamespace('default');
    $service->setType('LoadBalancer');
    $service->setSelector(['app' => 'nginx']);
    $service->setPorts([['name' => 'http', 'port' => 80, 'targetPort' => 8080]]);

    $array = $service->toArray();

    expect($array)->toHaveKey('apiVersion');
    expect($array)->toHaveKey('kind');
    expect($array)->toHaveKey('metadata');
    expect($array)->toHaveKey('spec');
    expect($array['apiVersion'])->toBe('v1');
    expect($array['kind'])->toBe('Service');
    expect($array['metadata']['name'])->toBe('my-service');
    expect($array['metadata']['namespace'])->toBe('default');
    expect($array['spec']['type'])->toBe('LoadBalancer');
    expect($array['spec']['selector'])->toBe(['app' => 'nginx']);
    expect($array['spec']['ports'])->toBe([['name' => 'http', 'port' => 80, 'targetPort' => 8080]]);
});

it('can create service from array', function (): void {
    $data = [
        'apiVersion' => 'v1',
        'kind'       => 'Service',
        'metadata'   => [
            'name'      => 'my-service',
            'namespace' => 'default',
            'labels'    => ['app' => 'nginx'],
        ],
        'spec' => [
            'type'     => 'ClusterIP',
            'selector' => ['app' => 'nginx'],
            'ports'    => [
                ['name' => 'http', 'port' => 80, 'targetPort' => 8080],
            ],
        ],
    ];

    $service = Service::fromArray($data);

    expect($service->getName())->toBe('my-service');
    expect($service->getNamespace())->toBe('default');
    expect($service->getLabels())->toBe(['app' => 'nginx']);
    expect($service->getType())->toBe('ClusterIP');
    expect($service->getSelector())->toBe(['app' => 'nginx']);
    expect($service->getPorts())->toBe([['name' => 'http', 'port' => 80, 'targetPort' => 8080]]);
});

it('can chain setter methods', function (): void {
    $service = new Service();

    $result = $service
        ->setName('my-service')
        ->setNamespace('default')
        ->setType('LoadBalancer')
        ->setSelector(['app' => 'nginx'])
        ->addPort(['name' => 'http', 'port' => 80, 'targetPort' => 8080]);

    expect($result)->toBe($service);
    expect($service->getName())->toBe('my-service');
    expect($service->getNamespace())->toBe('default');
    expect($service->getType())->toBe('LoadBalancer');
    expect($service->getSelector())->toBe(['app' => 'nginx']);
    expect($service->getPorts())->toBe([['name' => 'http', 'port' => 80, 'targetPort' => 8080]]);
});
