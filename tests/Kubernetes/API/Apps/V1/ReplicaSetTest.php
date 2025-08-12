<?php

declare(strict_types=1);

use Kubernetes\API\Apps\V1\ReplicaSet;

it('can create a replica set', function (): void {
    $replicaSet = new ReplicaSet();
    expect($replicaSet->getApiVersion())->toBe('apps/v1');
    expect($replicaSet->getKind())->toBe('ReplicaSet');
});

it('can set and get namespace', function (): void {
    $replicaSet = new ReplicaSet();
    $replicaSet->setNamespace('test-namespace');
    expect($replicaSet->getNamespace())->toBe('test-namespace');
});

it('can set and get replicas', function (): void {
    $replicaSet = new ReplicaSet();
    $replicaSet->setReplicas(5);
    expect($replicaSet->getReplicas())->toBe(5);
});

it('defaults to 1 replica when not set', function (): void {
    $replicaSet = new ReplicaSet();
    expect($replicaSet->getReplicas())->toBe(1);
});

it('can set and get selector', function (): void {
    $replicaSet = new ReplicaSet();
    $selector = [
        'matchLabels' => [
            'app' => 'web',
        ],
    ];

    $replicaSet->setSelector($selector);
    expect($replicaSet->getSelector())->toBe($selector);
});

it('can set selector with match labels helper', function (): void {
    $replicaSet = new ReplicaSet();
    $labels = ['app' => 'api', 'version' => 'v1'];
    $replicaSet->setSelectorMatchLabels($labels);

    $selector = $replicaSet->getSelector();
    expect($selector)->not->toBeNull();
    if ($selector !== null) {
        expect($selector['matchLabels'])->toBe($labels);
    }
});

it('can set selector with match expressions', function (): void {
    $replicaSet = new ReplicaSet();
    $expressions = [
        [
            'key'      => 'environment',
            'operator' => 'In',
            'values'   => ['prod', 'staging'],
        ],
    ];

    $replicaSet->setSelectorMatchExpressions($expressions);

    $selector = $replicaSet->getSelector();
    expect($selector)->not->toBeNull();
    if ($selector !== null) {
        expect($selector['matchExpressions'])->toBe($expressions);
    }
});

it('can set and get template', function (): void {
    $replicaSet = new ReplicaSet();
    $template = [
        'metadata' => [
            'labels' => ['app' => 'web'],
        ],
        'spec' => [
            'containers' => [
                [
                    'name'  => 'nginx',
                    'image' => 'nginx:1.20',
                ],
            ],
        ],
    ];

    $replicaSet->setTemplate($template);
    expect($replicaSet->getTemplate())->toBe($template);
});

it('can set pod template with helper method', function (): void {
    $replicaSet = new ReplicaSet();
    $labels = ['app' => 'web'];
    $containers = [
        [
            'name'  => 'nginx',
            'image' => 'nginx:latest',
        ],
    ];

    $replicaSet->setPodTemplate($labels, $containers);

    $template = $replicaSet->getTemplate();
    expect($template)->not->toBeNull();
    if ($template !== null) {
        expect($template['metadata']['labels'])->toBe($labels);
        expect($template['spec']['containers'])->toBe($containers);
    }
});

it('can set and get min ready seconds', function (): void {
    $replicaSet = new ReplicaSet();
    $replicaSet->setMinReadySeconds(5);
    expect($replicaSet->getMinReadySeconds())->toBe(5);
});

it('defaults to 0 min ready seconds when not set', function (): void {
    $replicaSet = new ReplicaSet();
    expect($replicaSet->getMinReadySeconds())->toBe(0);
});

it('can create container with helper method', function (): void {
    $replicaSet = new ReplicaSet();
    $container = $replicaSet->createContainer(
        'web',
        'nginx:1.20',
        [['containerPort' => 80]],
        ['ENV_VAR' => 'value']
    );

    expect($container['name'])->toBe('web');
    expect($container['image'])->toBe('nginx:1.20');
    expect($container['ports'])->toBe([['containerPort' => 80]]);
    expect($container['env'])->toBe([['name' => 'ENV_VAR', 'value' => 'value']]);
});

it('can add container with helper method', function (): void {
    $replicaSet = new ReplicaSet();
    $replicaSet->addContainer('web', 'nginx:1.20');

    $template = $replicaSet->getTemplate();
    expect($template)->not->toBeNull();
    if ($template !== null) {
        expect($template['spec']['containers'])->toHaveCount(1);
        expect($template['spec']['containers'][0]['name'])->toBe('web');
        expect($template['spec']['containers'][0]['image'])->toBe('nginx:1.20');
    }
});

it('can scale with helper method', function (): void {
    $replicaSet = new ReplicaSet();
    $replicaSet->scale(10);
    expect($replicaSet->getReplicas())->toBe(10);
});

it('can scale up', function (): void {
    $replicaSet = new ReplicaSet();
    $replicaSet->setReplicas(3);
    $replicaSet->scaleUp(2);
    expect($replicaSet->getReplicas())->toBe(5);
});

it('can scale down', function (): void {
    $replicaSet = new ReplicaSet();
    $replicaSet->setReplicas(5);
    $replicaSet->scaleDown(2);
    expect($replicaSet->getReplicas())->toBe(3);
});

it('cannot scale below zero', function (): void {
    $replicaSet = new ReplicaSet();
    $replicaSet->setReplicas(1);
    $replicaSet->scaleDown(5);
    expect($replicaSet->getReplicas())->toBe(0);
});

it('can set resource limits', function (): void {
    $replicaSet = new ReplicaSet();
    $replicaSet->addContainer('test', 'test:latest');
    $limits = ['cpu' => '500m', 'memory' => '256Mi'];
    $replicaSet->setResourceLimits($limits);

    $template = $replicaSet->getTemplate();
    expect($template)->not->toBeNull();
    if ($template !== null) {
        expect($template['spec']['containers'][0]['resources']['limits'])->toBe($limits);
    }
});

it('can set resource requests', function (): void {
    $replicaSet = new ReplicaSet();
    $replicaSet->addContainer('test', 'test:latest');
    $requests = ['cpu' => '100m', 'memory' => '128Mi'];
    $replicaSet->setResourceRequests($requests);

    $template = $replicaSet->getTemplate();
    expect($template)->not->toBeNull();
    if ($template !== null) {
        expect($template['spec']['containers'][0]['resources']['requests'])->toBe($requests);
    }
});

it('can set restart policy', function (): void {
    $replicaSet = new ReplicaSet();
    $replicaSet->setRestartPolicy('Always');

    $template = $replicaSet->getTemplate();
    expect($template)->not->toBeNull();
    if ($template !== null) {
        expect($template['spec']['restartPolicy'])->toBe('Always');
    }
});

it('can get status fields', function (): void {
    $replicaSet = new ReplicaSet();
    expect($replicaSet->getCurrentReplicas())->toBe(0);
    expect($replicaSet->getReadyReplicas())->toBe(0);
    expect($replicaSet->getAvailableReplicas())->toBe(0);
    expect($replicaSet->getFullyLabeledReplicas())->toBe(0);
    expect($replicaSet->getObservedGeneration())->toBe(0);
});

it('can check readiness status', function (): void {
    $replicaSet = new ReplicaSet();
    $replicaSet->setReplicas(3);
    expect($replicaSet->isReady())->toBeFalse();
    expect($replicaSet->isAvailable())->toBeFalse();
});

it('returns empty arrays when no configuration is set', function (): void {
    $replicaSet = new ReplicaSet();
    expect($replicaSet->getConditions())->toBe([]);
    expect($replicaSet->getStatus())->toBe([]);
});

it('returns null when no configuration is set', function (): void {
    $replicaSet = new ReplicaSet();
    expect($replicaSet->getSelector())->toBeNull();
    expect($replicaSet->getTemplate())->toBeNull();
});

it('can chain setter methods', function (): void {
    $replicaSet = new ReplicaSet();
    $result = $replicaSet
        ->setName('test-replicaset')
        ->setNamespace('default')
        ->setReplicas(3)
        ->setSelectorMatchLabels(['app' => 'web'])
        ->setMinReadySeconds(5);

    expect($result)->toBe($replicaSet);
    expect($replicaSet->getName())->toBe('test-replicaset');
    expect($replicaSet->getNamespace())->toBe('default');
    expect($replicaSet->getReplicas())->toBe(3);
    expect($replicaSet->getMinReadySeconds())->toBe(5);
});
