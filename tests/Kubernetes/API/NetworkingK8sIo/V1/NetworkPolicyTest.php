<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\NetworkingK8sIo\V1;

use Kubernetes\API\NetworkingK8sIo\V1\NetworkPolicy;

it('can create a network policy', function (): void {
    $networkPolicy = new NetworkPolicy();
    expect($networkPolicy->getApiVersion())->toBe('networking.k8s.io/v1');
    expect($networkPolicy->getKind())->toBe('NetworkPolicy');
});

it('can set and get namespace', function (): void {
    $networkPolicy = new NetworkPolicy();
    $networkPolicy->setNamespace('test-namespace');
    expect($networkPolicy->getNamespace())->toBe('test-namespace');
});

it('can set and get pod selector', function (): void {
    $networkPolicy = new NetworkPolicy();
    $podSelector = [
        'matchLabels' => [
            'app' => 'web',
        ],
    ];

    $networkPolicy->setPodSelector($podSelector);
    expect($networkPolicy->getPodSelector())->toBe($podSelector);
});

it('can select all pods in namespace', function (): void {
    $networkPolicy = new NetworkPolicy();
    $networkPolicy->selectAllPods();
    expect($networkPolicy->getPodSelector())->toBe([]);
});

it('can select pods by labels', function (): void {
    $networkPolicy = new NetworkPolicy();
    $labels = ['app' => 'web', 'tier' => 'frontend'];
    $networkPolicy->selectPodsByLabels($labels);

    $podSelector = $networkPolicy->getPodSelector();
    expect($podSelector)->not->toBeNull();
    if ($podSelector !== null) {
        expect($podSelector['matchLabels'])->toBe($labels);
    }
});

it('can set and get ingress rules', function (): void {
    $networkPolicy = new NetworkPolicy();
    $ingressRules = [
        [
            'from' => [
                ['podSelector' => []],
            ],
        ],
    ];

    $networkPolicy->setIngressRules($ingressRules);
    expect($networkPolicy->getIngressRules())->toBe($ingressRules);
});

it('can add individual ingress rules', function (): void {
    $networkPolicy = new NetworkPolicy();
    $rule = [
        'from' => [
            ['namespaceSelector' => []],
        ],
    ];

    $networkPolicy->addIngressRule($rule);
    expect($networkPolicy->getIngressRules())->toBe([$rule]);
});

it('can set and get egress rules', function (): void {
    $networkPolicy = new NetworkPolicy();
    $egressRules = [
        [
            'to' => [
                ['podSelector' => []],
            ],
        ],
    ];

    $networkPolicy->setEgressRules($egressRules);
    expect($networkPolicy->getEgressRules())->toBe($egressRules);
});

it('can add individual egress rules', function (): void {
    $networkPolicy = new NetworkPolicy();
    $rule = [
        'to' => [
            ['namespaceSelector' => []],
        ],
    ];

    $networkPolicy->addEgressRule($rule);
    expect($networkPolicy->getEgressRules())->toBe([$rule]);
});

it('can set and get policy types', function (): void {
    $networkPolicy = new NetworkPolicy();
    $policyTypes = ['Ingress', 'Egress'];

    $networkPolicy->setPolicyTypes($policyTypes);
    expect($networkPolicy->getPolicyTypes())->toBe($policyTypes);
});

it('can enable both policy types', function (): void {
    $networkPolicy = new NetworkPolicy();
    $networkPolicy->enableBothPolicyTypes();
    expect($networkPolicy->getPolicyTypes())->toBe(['Ingress', 'Egress']);
});

it('can enable ingress only', function (): void {
    $networkPolicy = new NetworkPolicy();
    $networkPolicy->enableIngressOnly();
    expect($networkPolicy->getPolicyTypes())->toBe(['Ingress']);
});

it('can enable egress only', function (): void {
    $networkPolicy = new NetworkPolicy();
    $networkPolicy->enableEgressOnly();
    expect($networkPolicy->getPolicyTypes())->toBe(['Egress']);
});

it('can allow ingress from namespace', function (): void {
    $networkPolicy = new NetworkPolicy();
    $networkPolicy->allowIngressFromNamespace();

    $rules = $networkPolicy->getIngressRules();
    expect($rules)->toHaveCount(1);
    expect($rules[0]['from'][0]['namespaceSelector'])->toBe([]);
});

it('can allow ingress from pods with labels', function (): void {
    $networkPolicy = new NetworkPolicy();
    $labels = ['app' => 'database'];
    $networkPolicy->allowIngressFromPods($labels);

    $rules = $networkPolicy->getIngressRules();
    expect($rules[0]['from'][0]['podSelector']['matchLabels'])->toBe($labels);
});

it('can allow egress to all destinations', function (): void {
    $networkPolicy = new NetworkPolicy();
    $networkPolicy->allowEgressToAll();

    $rules = $networkPolicy->getEgressRules();
    expect($rules)->toHaveCount(1);
    expect($rules[0])->toBe([]);
});

it('can allow egress to specific CIDRs', function (): void {
    $networkPolicy = new NetworkPolicy();
    $cidrs = ['10.0.0.0/8', '192.168.1.0/24'];
    $networkPolicy->allowEgressToCidrs($cidrs);

    $rules = $networkPolicy->getEgressRules();
    expect($rules[0]['to'])->toHaveCount(2);
    expect($rules[0]['to'][0]['ipBlock']['cidr'])->toBe('10.0.0.0/8');
});

it('can create port specifications', function (): void {
    $networkPolicy = new NetworkPolicy();
    $portSpec = $networkPolicy->createPortSpec('TCP', 80);

    expect($portSpec['protocol'])->toBe('TCP');
    expect($portSpec['port'])->toBe(80);
});

it('can create port specifications without port number', function (): void {
    $networkPolicy = new NetworkPolicy();
    $portSpec = $networkPolicy->createPortSpec('UDP');

    expect($portSpec['protocol'])->toBe('UDP');
    expect($portSpec)->not->toHaveKey('port');
});

it('returns empty arrays when no rules are set', function (): void {
    $networkPolicy = new NetworkPolicy();
    expect($networkPolicy->getIngressRules())->toBe([]);
    expect($networkPolicy->getEgressRules())->toBe([]);
    expect($networkPolicy->getPolicyTypes())->toBe([]);
});

it('returns null when no pod selector is set', function (): void {
    $networkPolicy = new NetworkPolicy();
    expect($networkPolicy->getPodSelector())->toBeNull();
});

it('can chain setter methods', function (): void {
    $networkPolicy = new NetworkPolicy();
    $result = $networkPolicy
        ->setName('test-policy')
        ->setNamespace('default')
        ->selectAllPods()
        ->enableBothPolicyTypes();

    expect($result)->toBe($networkPolicy);
    expect($networkPolicy->getName())->toBe('test-policy');
    expect($networkPolicy->getNamespace())->toBe('default');
    expect($networkPolicy->getPodSelector())->toBe([]);
});
