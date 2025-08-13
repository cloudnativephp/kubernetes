<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\NetworkingK8sIo\V1;

use Kubernetes\API\NetworkingK8sIo\V1\Ingress;

it('can create an ingress', function (): void {
    $ingress = new Ingress();
    expect($ingress->getApiVersion())->toBe('networking.k8s.io/v1');
    expect($ingress->getKind())->toBe('Ingress');
});

it('can set and get namespace', function (): void {
    $ingress = new Ingress();
    $ingress->setNamespace('test-namespace');
    expect($ingress->getNamespace())->toBe('test-namespace');
});

it('can set and get ingress class name', function (): void {
    $ingress = new Ingress();
    $ingress->setIngressClassName('nginx');
    expect($ingress->getIngressClassName())->toBe('nginx');
});

it('can set and get default backend', function (): void {
    $ingress = new Ingress();
    $defaultBackend = [
        'service' => [
            'name' => 'default-service',
            'port' => ['number' => 80],
        ],
    ];

    $ingress->setDefaultBackend($defaultBackend);
    expect($ingress->getDefaultBackend())->toBe($defaultBackend);
});

it('can set default backend service with helper method', function (): void {
    $ingress = new Ingress();
    $ingress->setDefaultBackendService('my-service', 8080);

    $backend = $ingress->getDefaultBackend();
    expect($backend)->not->toBeNull();
    if ($backend !== null) {
        expect($backend['service']['name'])->toBe('my-service');
        expect($backend['service']['port']['number'])->toBe(8080);
    }
});

it('can set and get TLS configuration', function (): void {
    $ingress = new Ingress();
    $tlsConfig = [
        [
            'hosts'      => ['example.com'],
            'secretName' => 'example-tls',
        ],
    ];

    $ingress->setTls($tlsConfig);
    expect($ingress->getTls())->toBe($tlsConfig);
});

it('can add individual TLS configurations', function (): void {
    $ingress = new Ingress();
    $tlsConfig = [
        'hosts'      => ['api.example.com'],
        'secretName' => 'api-tls',
    ];

    $ingress->addTls($tlsConfig);
    expect($ingress->getTls())->toBe([$tlsConfig]);
});

it('can add TLS config with helper method', function (): void {
    $ingress = new Ingress();
    $hosts = ['www.example.com', 'example.com'];
    $ingress->addTlsConfig($hosts, 'example-tls');

    $tls = $ingress->getTls();
    expect($tls[0]['hosts'])->toBe($hosts);
    expect($tls[0]['secretName'])->toBe('example-tls');
});

it('can set and get rules', function (): void {
    $ingress = new Ingress();
    $rules = [
        [
            'host' => 'example.com',
            'http' => [
                'paths' => [
                    [
                        'path'     => '/',
                        'pathType' => 'Prefix',
                        'backend'  => [
                            'service' => [
                                'name' => 'web-service',
                                'port' => ['number' => 80],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $ingress->setRules($rules);
    expect($ingress->getRules())->toBe($rules);
});

it('can add individual rules', function (): void {
    $ingress = new Ingress();
    $rule = [
        'host' => 'api.example.com',
        'http' => [
            'paths' => [],
        ],
    ];

    $ingress->addRule($rule);
    expect($ingress->getRules())->toBe([$rule]);
});

it('can add HTTP rule with helper method', function (): void {
    $ingress = new Ingress();
    $ingress->addHttpRule('example.com', '/api', 'Prefix', 'api-service', 8080);

    $rules = $ingress->getRules();
    expect($rules)->toHaveCount(1);
    expect($rules[0]['host'])->toBe('example.com');
    expect($rules[0]['http']['paths'][0]['path'])->toBe('/api');
    expect($rules[0]['http']['paths'][0]['pathType'])->toBe('Prefix');
    expect($rules[0]['http']['paths'][0]['backend']['service']['name'])->toBe('api-service');
    expect($rules[0]['http']['paths'][0]['backend']['service']['port']['number'])->toBe(8080);
});

it('can add path to existing rule', function (): void {
    $ingress = new Ingress();
    $ingress->addHttpRule('example.com', '/', 'Prefix', 'web-service', 80);
    $ingress->addPathToRule(0, '/api', 'Prefix', 'api-service', 8080);

    $rules = $ingress->getRules();
    expect($rules[0]['http']['paths'])->toHaveCount(2);
    expect($rules[0]['http']['paths'][1]['path'])->toBe('/api');
});

it('can create simple ingress', function (): void {
    $ingress = new Ingress();
    $ingress->createSimpleIngress('example.com', 'web-service', 80, 'nginx');

    expect($ingress->getIngressClassName())->toBe('nginx');
    $rules = $ingress->getRules();
    expect($rules[0]['host'])->toBe('example.com');
    expect($rules[0]['http']['paths'][0]['backend']['service']['name'])->toBe('web-service');
});

it('can enable TLS with helper method', function (): void {
    $ingress = new Ingress();
    $hosts = ['secure.example.com'];
    $ingress->enableTls($hosts, 'secure-tls');

    $tls = $ingress->getTls();
    expect($tls[0]['hosts'])->toBe($hosts);
    expect($tls[0]['secretName'])->toBe('secure-tls');
});

it('can get all hostnames', function (): void {
    $ingress = new Ingress();
    $ingress->addHttpRule('example.com', '/', 'Prefix', 'service1', 80);
    $ingress->addHttpRule('api.example.com', '/', 'Prefix', 'service2', 80);

    $hostnames = $ingress->getHostnames();
    expect($hostnames)->toContain('example.com', 'api.example.com');
    expect($hostnames)->toHaveCount(2);
});

it('can check if TLS is enabled for host', function (): void {
    $ingress = new Ingress();
    $ingress->addTlsConfig(['secure.example.com'], 'secure-tls');

    expect($ingress->isTlsEnabledForHost('secure.example.com'))->toBeTrue();
    expect($ingress->isTlsEnabledForHost('insecure.example.com'))->toBeFalse();
});

it('can get load balancer status', function (): void {
    $ingress = new Ingress();
    expect($ingress->getLoadBalancer())->toBeNull();
});

it('returns empty arrays when no configuration is set', function (): void {
    $ingress = new Ingress();
    expect($ingress->getTls())->toBe([]);
    expect($ingress->getRules())->toBe([]);
    expect($ingress->getHostnames())->toBe([]);
});

it('returns null when no configuration is set', function (): void {
    $ingress = new Ingress();
    expect($ingress->getIngressClassName())->toBeNull();
    expect($ingress->getDefaultBackend())->toBeNull();
    expect($ingress->getLoadBalancer())->toBeNull();
});

it('can chain setter methods', function (): void {
    $ingress = new Ingress();
    $result = $ingress
        ->setName('test-ingress')
        ->setNamespace('default')
        ->setIngressClassName('nginx')
        ->addHttpRule('example.com', '/', 'Prefix', 'web-service', 80);

    expect($result)->toBe($ingress);
    expect($ingress->getName())->toBe('test-ingress');
    expect($ingress->getNamespace())->toBe('default');
    expect($ingress->getIngressClassName())->toBe('nginx');
});
