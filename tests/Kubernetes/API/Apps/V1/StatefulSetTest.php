<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\Apps\V1;

use Kubernetes\API\Apps\V1\StatefulSet;

it('can create a stateful set', function (): void {
    $statefulSet = new StatefulSet();
    expect($statefulSet->getApiVersion())->toBe('apps/v1');
    expect($statefulSet->getKind())->toBe('StatefulSet');
});

it('can set and get namespace', function (): void {
    $statefulSet = new StatefulSet();
    $statefulSet->setNamespace('test-namespace');
    expect($statefulSet->getNamespace())->toBe('test-namespace');
});

it('can set and get replicas', function (): void {
    $statefulSet = new StatefulSet();
    $statefulSet->setReplicas(3);
    expect($statefulSet->getReplicas())->toBe(3);
});

it('defaults to 1 replica when not set', function (): void {
    $statefulSet = new StatefulSet();
    expect($statefulSet->getReplicas())->toBe(1);
});

it('can set and get service name', function (): void {
    $statefulSet = new StatefulSet();
    $statefulSet->setServiceName('headless-service');
    expect($statefulSet->getServiceName())->toBe('headless-service');
});

it('can set and get selector', function (): void {
    $statefulSet = new StatefulSet();
    $selector = [
        'matchLabels' => [
            'app' => 'database',
        ],
    ];

    $statefulSet->setSelector($selector);
    expect($statefulSet->getSelector())->toBe($selector);
});

it('can set selector with match labels helper', function (): void {
    $statefulSet = new StatefulSet();
    $labels = ['app' => 'database', 'tier' => 'backend'];
    $statefulSet->setSelectorMatchLabels($labels);

    $selector = $statefulSet->getSelector();
    expect($selector)->not->toBeNull();
    if ($selector !== null) {
        expect($selector['matchLabels'])->toBe($labels);
    }
});

it('can set and get template', function (): void {
    $statefulSet = new StatefulSet();
    $template = [
        'metadata' => [
            'labels' => ['app' => 'database'],
        ],
        'spec' => [
            'containers' => [
                [
                    'name'  => 'db',
                    'image' => 'postgres:13',
                ],
            ],
        ],
    ];

    $statefulSet->setTemplate($template);
    expect($statefulSet->getTemplate())->toBe($template);
});

it('can set pod template with helper method', function (): void {
    $statefulSet = new StatefulSet();
    $labels = ['app' => 'database'];
    $containers = [
        [
            'name'  => 'db',
            'image' => 'postgres:13',
        ],
    ];

    $statefulSet->setPodTemplate($labels, $containers);

    $template = $statefulSet->getTemplate();
    expect($template)->not->toBeNull();
    if ($template !== null) {
        expect($template['metadata']['labels'])->toBe($labels);
        expect($template['spec']['containers'])->toBe($containers);
    }
});

it('can set and get volume claim templates', function (): void {
    $statefulSet = new StatefulSet();
    $templates = [
        [
            'metadata' => ['name' => 'data'],
            'spec'     => [
                'accessModes'      => ['ReadWriteOnce'],
                'storageClassName' => 'ssd',
                'resources'        => [
                    'requests' => ['storage' => '10Gi'],
                ],
            ],
        ],
    ];

    $statefulSet->setVolumeClaimTemplates($templates);
    expect($statefulSet->getVolumeClaimTemplates())->toBe($templates);
});

it('can add volume claim template', function (): void {
    $statefulSet = new StatefulSet();
    $template = [
        'metadata' => ['name' => 'data'],
        'spec'     => ['accessModes' => ['ReadWriteOnce']],
    ];

    $statefulSet->addVolumeClaimTemplate($template);
    expect($statefulSet->getVolumeClaimTemplates())->toBe([$template]);
});

it('can create PVC template with helper method', function (): void {
    $statefulSet = new StatefulSet();
    $template = $statefulSet->createPvcTemplate('data', 'ssd', '10Gi');

    expect($template['metadata']['name'])->toBe('data');
    expect($template['spec']['storageClassName'])->toBe('ssd');
    expect($template['spec']['resources']['requests']['storage'])->toBe('10Gi');
    expect($template['spec']['accessModes'])->toBe(['ReadWriteOnce']);
});

it('can add PVC template with helper method', function (): void {
    $statefulSet = new StatefulSet();
    $statefulSet->addPvcTemplate('data', 'ssd', '20Gi', ['ReadWriteMany']);

    $templates = $statefulSet->getVolumeClaimTemplates();
    expect($templates)->toHaveCount(1);
    expect($templates[0]['spec']['resources']['requests']['storage'])->toBe('20Gi');
    expect($templates[0]['spec']['accessModes'])->toBe(['ReadWriteMany']);
});

it('can set and get update strategy', function (): void {
    $statefulSet = new StatefulSet();
    $strategy = [
        'type'          => 'RollingUpdate',
        'rollingUpdate' => ['partition' => 3],
    ];

    $statefulSet->setUpdateStrategy($strategy);
    expect($statefulSet->getUpdateStrategy())->toBe($strategy);
});

it('can set rolling update strategy', function (): void {
    $statefulSet = new StatefulSet();
    $statefulSet->setRollingUpdateStrategy(2, 1);

    $strategy = $statefulSet->getUpdateStrategy();
    expect($strategy)->not->toBeNull();
    if ($strategy !== null) {
        expect($strategy['type'])->toBe('RollingUpdate');
        expect($strategy['rollingUpdate']['partition'])->toBe(2);
        expect($strategy['rollingUpdate']['maxUnavailable'])->toBe(1);
    }
});

it('can set on delete update strategy', function (): void {
    $statefulSet = new StatefulSet();
    $statefulSet->setOnDeleteUpdateStrategy();

    $strategy = $statefulSet->getUpdateStrategy();
    expect($strategy)->not->toBeNull();
    if ($strategy !== null) {
        expect($strategy['type'])->toBe('OnDelete');
    }
});

it('can set and get pod management policy', function (): void {
    $statefulSet = new StatefulSet();
    $statefulSet->setPodManagementPolicy('Parallel');
    expect($statefulSet->getPodManagementPolicy())->toBe('Parallel');
});

it('can set and get revision history limit', function (): void {
    $statefulSet = new StatefulSet();
    $statefulSet->setRevisionHistoryLimit(5);
    expect($statefulSet->getRevisionHistoryLimit())->toBe(5);
});

it('can set and get min ready seconds', function (): void {
    $statefulSet = new StatefulSet();
    $statefulSet->setMinReadySeconds(10);
    expect($statefulSet->getMinReadySeconds())->toBe(10);
});

it('defaults to 0 min ready seconds when not set', function (): void {
    $statefulSet = new StatefulSet();
    expect($statefulSet->getMinReadySeconds())->toBe(0);
});

it('can scale with helper method', function (): void {
    $statefulSet = new StatefulSet();
    $statefulSet->scale(5);
    expect($statefulSet->getReplicas())->toBe(5);
});

it('can get status fields', function (): void {
    $statefulSet = new StatefulSet();
    expect($statefulSet->getCurrentReplicas())->toBe(0);
    expect($statefulSet->getReadyReplicas())->toBe(0);
    expect($statefulSet->getUpdatedReplicas())->toBe(0);
});

it('returns empty arrays when no configuration is set', function (): void {
    $statefulSet = new StatefulSet();
    expect($statefulSet->getVolumeClaimTemplates())->toBe([]);
    expect($statefulSet->getStatus())->toBe([]);
});

it('returns null when no configuration is set', function (): void {
    $statefulSet = new StatefulSet();
    expect($statefulSet->getServiceName())->toBeNull();
    expect($statefulSet->getSelector())->toBeNull();
    expect($statefulSet->getTemplate())->toBeNull();
    expect($statefulSet->getUpdateStrategy())->toBeNull();
    expect($statefulSet->getPodManagementPolicy())->toBeNull();
    expect($statefulSet->getRevisionHistoryLimit())->toBeNull();
});

it('can chain setter methods', function (): void {
    $statefulSet = new StatefulSet();
    $result = $statefulSet
        ->setName('test-statefulset')
        ->setNamespace('default')
        ->setReplicas(3)
        ->setServiceName('headless-service')
        ->setSelectorMatchLabels(['app' => 'database']);

    expect($result)->toBe($statefulSet);
    expect($statefulSet->getName())->toBe('test-statefulset');
    expect($statefulSet->getNamespace())->toBe('default');
    expect($statefulSet->getReplicas())->toBe(3);
    expect($statefulSet->getServiceName())->toBe('headless-service');
});
