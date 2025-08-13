<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\Core\V1;

use Kubernetes\API\Core\V1\ReplicationController;

it('can create a replication controller resource', function (): void {
    $rc = new ReplicationController();
    expect($rc->getApiVersion())->toBe('v1');
    expect($rc->getKind())->toBe('ReplicationController');
});

it('can set and get namespace', function (): void {
    $rc = new ReplicationController();
    $result = $rc->setNamespace('test-namespace');

    expect($result)->toBe($rc);
    expect($rc->getNamespace())->toBe('test-namespace');
});

it('can set and get replicas', function (): void {
    $rc = new ReplicationController();

    $result = $rc->setReplicas(3);

    expect($result)->toBe($rc);
    expect($rc->getReplicas())->toBe(3);
});

it('defaults to 1 replica when not set', function (): void {
    $rc = new ReplicationController();
    expect($rc->getReplicas())->toBe(1);
});

it('can set and get selector', function (): void {
    $rc = new ReplicationController();
    $selector = ['app' => 'my-app', 'version' => 'v1'];

    $result = $rc->setSelector($selector);

    expect($result)->toBe($rc);
    expect($rc->getSelector())->toBe($selector);
});

it('can set and get template', function (): void {
    $rc = new ReplicationController();
    $template = [
        'metadata' => ['labels' => ['app' => 'my-app']],
        'spec'     => [
            'containers' => [
                ['name' => 'web', 'image' => 'nginx:1.20'],
            ],
        ],
    ];

    $result = $rc->setTemplate($template);

    expect($result)->toBe($rc);
    expect($rc->getTemplate())->toBe($template);
});

it('can set and get min ready seconds', function (): void {
    $rc = new ReplicationController();

    $result = $rc->setMinReadySeconds(30);

    expect($result)->toBe($rc);
    expect($rc->getMinReadySeconds())->toBe(30);
});

it('defaults to 0 min ready seconds when not set', function (): void {
    $rc = new ReplicationController();
    expect($rc->getMinReadySeconds())->toBe(0);
});

it('can set pod template with helper method', function (): void {
    $rc = new ReplicationController();
    $labels = ['app' => 'my-app', 'version' => 'v1'];
    $ports = [['containerPort' => 80, 'protocol' => 'TCP']];

    $result = $rc->setPodTemplate('web', 'nginx:1.20', $labels, $ports);

    expect($result)->toBe($rc);

    $template = $rc->getTemplate();
    expect($template)->not->toBeNull();

    if ($template !== null) {
        expect($template['metadata']['labels'])->toBe($labels);
        expect($template['spec']['containers'])->toHaveCount(1);
        expect($template['spec']['containers'][0]['name'])->toBe('web');
        expect($template['spec']['containers'][0]['image'])->toBe('nginx:1.20');
        expect($template['spec']['containers'][0]['ports'])->toBe($ports);
    }
});

it('can set pod template without ports', function (): void {
    $rc = new ReplicationController();
    $labels = ['app' => 'my-app'];

    $result = $rc->setPodTemplate('web', 'nginx:1.20', $labels);

    expect($result)->toBe($rc);

    $template = $rc->getTemplate();
    expect($template)->not->toBeNull();

    if ($template !== null) {
        expect($template['spec']['containers'][0])->not->toHaveKey('ports');
    }
});

it('can scale using helper method', function (): void {
    $rc = new ReplicationController();

    $result = $rc->scale(5);

    expect($result)->toBe($rc);
    expect($rc->getReplicas())->toBe(5);
});

it('returns default values for status fields', function (): void {
    $rc = new ReplicationController();

    expect($rc->getAvailableReplicas())->toBe(0);
    expect($rc->getFullyLabeledReplicas())->toBe(0);
    expect($rc->getReadyReplicas())->toBe(0);
    expect($rc->getObservedGeneration())->toBe(0);
    expect($rc->getConditions())->toBe([]);
});

it('returns empty arrays when nothing is set', function (): void {
    $rc = new ReplicationController();

    expect($rc->getSelector())->toBe([]);
    expect($rc->getTemplate())->toBeNull();
});

it('can chain setter methods', function (): void {
    $rc = new ReplicationController();
    $result = $rc
        ->setName('my-rc')
        ->setNamespace('default')
        ->setReplicas(3)
        ->setSelector(['app' => 'my-app'])
        ->setMinReadySeconds(10);

    expect($result)->toBe($rc);
});
