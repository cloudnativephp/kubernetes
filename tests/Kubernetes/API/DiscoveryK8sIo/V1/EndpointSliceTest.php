<?php

declare(strict_types=1);

use Kubernetes\API\DiscoveryK8sIo\V1\EndpointSlice;

it('can create an EndpointSlice', function (): void {
    $slice = new EndpointSlice();
    expect($slice->getApiVersion())->toBe('discovery.k8s.io/v1');
    expect($slice->getKind())->toBe('EndpointSlice');
});

it('can set namespace', function (): void {
    $slice = new EndpointSlice();
    $result = $slice->setNamespace('default');
    expect($result)->toBe($slice);
    expect($slice->getNamespace())->toBe('default');
});

it('can set and get address type', function (): void {
    $slice = new EndpointSlice();
    $slice->setAddressType('IPv4');
    expect($slice->getAddressType())->toBe('IPv4');
});

it('can set and get endpoints', function (): void {
    $slice = new EndpointSlice();
    $endpoints = [
        ['addresses' => ['192.168.1.1'], 'conditions' => ['ready' => true]],
        ['addresses' => ['192.168.1.2'], 'conditions' => ['ready' => false]],
    ];
    $slice->setEndpoints($endpoints);
    expect($slice->getEndpoints())->toBe($endpoints);
});

it('can add endpoint', function (): void {
    $slice = new EndpointSlice();
    $endpoint = ['addresses' => ['192.168.1.1'], 'conditions' => ['ready' => true]];
    $slice->addEndpoint($endpoint);

    $endpoints = $slice->getEndpoints();
    expect($endpoints)->toHaveCount(1);
    expect($endpoints[0])->toBe($endpoint);
});

it('can set and get ports', function (): void {
    $slice = new EndpointSlice();
    $ports = [
        ['name' => 'http', 'port' => 80, 'protocol' => 'TCP'],
        ['name' => 'https', 'port' => 443, 'protocol' => 'TCP'],
    ];
    $slice->setPorts($ports);
    expect($slice->getPorts())->toBe($ports);
});

it('can add port', function (): void {
    $slice = new EndpointSlice();
    $port = ['name' => 'http', 'port' => 80, 'protocol' => 'TCP'];
    $slice->addPort($port);

    $ports = $slice->getPorts();
    expect($ports)->toHaveCount(1);
    expect($ports[0])->toBe($port);
});

it('can create endpoint with helper', function (): void {
    $slice = new EndpointSlice();
    $endpoint = $slice->createEndpoint(
        ['192.168.1.1', '192.168.1.2'],
        true,
        true,
        false,
        'worker-1',
        'us-west-2a'
    );

    expect($endpoint['addresses'])->toBe(['192.168.1.1', '192.168.1.2']);
    expect($endpoint['conditions']['ready'])->toBe(true);
    expect($endpoint['conditions']['serving'])->toBe(true);
    expect($endpoint['conditions']['terminating'])->toBe(false);
    expect($endpoint['nodeName'])->toBe('worker-1');
    expect($endpoint['zone'])->toBe('us-west-2a');
});

it('can add simple endpoint', function (): void {
    $slice = new EndpointSlice();
    $result = $slice->addSimpleEndpoint(['192.168.1.1'], true, true, 'worker-1');

    expect($result)->toBe($slice);
    $endpoints = $slice->getEndpoints();
    expect($endpoints)->toHaveCount(1);
    expect($endpoints[0]['addresses'])->toBe(['192.168.1.1']);
    expect($endpoints[0]['nodeName'])->toBe('worker-1');
});

it('can create port with helper', function (): void {
    $slice = new EndpointSlice();
    $port = $slice->createPort('http', 8080, 'TCP', 'HTTP');

    expect($port['name'])->toBe('http');
    expect($port['port'])->toBe(8080);
    expect($port['protocol'])->toBe('TCP');
    expect($port['appProtocol'])->toBe('HTTP');
});

it('can add simple port', function (): void {
    $slice = new EndpointSlice();
    $result = $slice->addSimplePort('https', 443, 'TCP', 'HTTPS');

    expect($result)->toBe($slice);
    $ports = $slice->getPorts();
    expect($ports)->toHaveCount(1);
    expect($ports[0]['name'])->toBe('https');
    expect($ports[0]['appProtocol'])->toBe('HTTPS');
});

it('can get ready endpoints', function (): void {
    $slice = new EndpointSlice();
    $slice->addEndpoint(['addresses' => ['192.168.1.1'], 'conditions' => ['ready' => true, 'serving' => true]]);
    $slice->addEndpoint(['addresses' => ['192.168.1.2'], 'conditions' => ['ready' => false, 'serving' => true]]);

    $ready = $slice->getReadyEndpoints();
    expect($ready)->toHaveCount(1);
    expect($ready[0]['addresses'])->toBe(['192.168.1.1']);
});

it('can get serving endpoints', function (): void {
    $slice = new EndpointSlice();
    $slice->addEndpoint(['addresses' => ['192.168.1.1'], 'conditions' => ['ready' => true, 'serving' => true]]);
    $slice->addEndpoint(['addresses' => ['192.168.1.2'], 'conditions' => ['ready' => true, 'serving' => false]]);

    $serving = $slice->getServingEndpoints();
    expect($serving)->toHaveCount(1);
    expect($serving[0]['addresses'])->toBe(['192.168.1.1']);
});

it('can get all addresses', function (): void {
    $slice = new EndpointSlice();
    $slice->addEndpoint(['addresses' => ['192.168.1.1', '192.168.1.2']]);
    $slice->addEndpoint(['addresses' => ['192.168.1.3']]);

    $addresses = $slice->getAllAddresses();
    expect($addresses)->toBe(['192.168.1.1', '192.168.1.2', '192.168.1.3']);
});

it('can get ready addresses', function (): void {
    $slice = new EndpointSlice();
    $slice->addEndpoint(['addresses' => ['192.168.1.1'], 'conditions' => ['ready' => true]]);
    $slice->addEndpoint(['addresses' => ['192.168.1.2'], 'conditions' => ['ready' => false]]);

    $addresses = $slice->getReadyAddresses();
    expect($addresses)->toBe(['192.168.1.1']);
});

it('can create IPv4 endpoint slice', function (): void {
    $slice = new EndpointSlice();
    $result = $slice->createIPv4EndpointSlice('web-service', 'production');

    expect($result)->toBe($slice);
    expect($slice->getName())->toBe('web-service-ipv4');
    expect($slice->getNamespace())->toBe('production');
    expect($slice->getAddressType())->toBe('IPv4');
    expect($slice->getLabels()['kubernetes.io/service-name'])->toBe('web-service');
});

it('can create IPv6 endpoint slice', function (): void {
    $slice = new EndpointSlice();
    $result = $slice->createIPv6EndpointSlice('api-service', 'staging');

    expect($result)->toBe($slice);
    expect($slice->getName())->toBe('api-service-ipv6');
    expect($slice->getNamespace())->toBe('staging');
    expect($slice->getAddressType())->toBe('IPv6');
});

it('can create web service endpoint slice', function (): void {
    $slice = new EndpointSlice();
    $result = $slice->createWebServiceEndpointSlice(
        'frontend',
        'default',
        ['192.168.1.10', '192.168.1.11'],
        8080,
        8443
    );

    expect($result)->toBe($slice);
    expect($slice->getName())->toBe('frontend-ipv4');
    expect($slice->getNamespace())->toBe('default');

    $ports = $slice->getPorts();
    expect($ports)->toHaveCount(2);
    expect($ports[0]['name'])->toBe('http');
    expect($ports[0]['port'])->toBe(8080);
    expect($ports[1]['name'])->toBe('https');
    expect($ports[1]['port'])->toBe(8443);

    $endpoints = $slice->getEndpoints();
    expect($endpoints)->toHaveCount(1);
    expect($endpoints[0]['addresses'])->toBe(['192.168.1.10', '192.168.1.11']);
});

it('can chain setter methods', function (): void {
    $slice = new EndpointSlice();
    $result = $slice
        ->setName('test-slice')
        ->setNamespace('default')
        ->setAddressType('IPv4')
        ->addSimplePort('http', 80);

    expect($result)->toBe($slice);
});
