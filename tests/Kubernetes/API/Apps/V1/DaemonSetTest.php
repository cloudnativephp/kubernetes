<?php

declare(strict_types=1);

use Kubernetes\API\Apps\V1\DaemonSet;

it('can create a daemon set', function (): void {
    $daemonSet = new DaemonSet();
    expect($daemonSet->getApiVersion())->toBe('apps/v1');
    expect($daemonSet->getKind())->toBe('DaemonSet');
});

it('can set and get namespace', function (): void {
    $daemonSet = new DaemonSet();
    $daemonSet->setNamespace('kube-system');
    expect($daemonSet->getNamespace())->toBe('kube-system');
});

it('can set and get selector', function (): void {
    $daemonSet = new DaemonSet();
    $selector = [
        'matchLabels' => [
            'app' => 'log-collector',
        ],
    ];

    $daemonSet->setSelector($selector);
    expect($daemonSet->getSelector())->toBe($selector);
});

it('can set selector with match labels helper', function (): void {
    $daemonSet = new DaemonSet();
    $labels = ['app' => 'monitoring', 'component' => 'agent'];
    $daemonSet->setSelectorMatchLabels($labels);

    $selector = $daemonSet->getSelector();
    expect($selector)->not->toBeNull();
    if ($selector !== null) {
        expect($selector['matchLabels'])->toBe($labels);
    }
});

it('can set and get template', function (): void {
    $daemonSet = new DaemonSet();
    $template = [
        'metadata' => [
            'labels' => ['app' => 'monitoring'],
        ],
        'spec' => [
            'containers' => [
                [
                    'name'  => 'agent',
                    'image' => 'monitoring-agent:latest',
                ],
            ],
        ],
    ];

    $daemonSet->setTemplate($template);
    expect($daemonSet->getTemplate())->toBe($template);
});

it('can set pod template with helper method', function (): void {
    $daemonSet = new DaemonSet();
    $labels = ['app' => 'log-collector'];
    $containers = [
        [
            'name'  => 'fluentd',
            'image' => 'fluentd:v1.14',
        ],
    ];

    $daemonSet->setPodTemplate($labels, $containers);

    $template = $daemonSet->getTemplate();
    expect($template)->not->toBeNull();
    if ($template !== null) {
        expect($template['metadata']['labels'])->toBe($labels);
        expect($template['spec']['containers'])->toBe($containers);
    }
});

it('can set and get update strategy', function (): void {
    $daemonSet = new DaemonSet();
    $strategy = [
        'type'          => 'RollingUpdate',
        'rollingUpdate' => ['maxUnavailable' => 1],
    ];

    $daemonSet->setUpdateStrategy($strategy);
    expect($daemonSet->getUpdateStrategy())->toBe($strategy);
});

it('can set rolling update strategy', function (): void {
    $daemonSet = new DaemonSet();
    $daemonSet->setRollingUpdateStrategy(2, 1);

    $strategy = $daemonSet->getUpdateStrategy();
    expect($strategy)->not->toBeNull();
    if ($strategy !== null) {
        expect($strategy['type'])->toBe('RollingUpdate');
        expect($strategy['rollingUpdate']['maxUnavailable'])->toBe(2);
        expect($strategy['rollingUpdate']['maxSurge'])->toBe(1);
    }
});

it('can set on delete update strategy', function (): void {
    $daemonSet = new DaemonSet();
    $daemonSet->setOnDeleteUpdateStrategy();

    $strategy = $daemonSet->getUpdateStrategy();
    expect($strategy)->not->toBeNull();
    if ($strategy !== null) {
        expect($strategy['type'])->toBe('OnDelete');
    }
});

it('can set and get min ready seconds', function (): void {
    $daemonSet = new DaemonSet();
    $daemonSet->setMinReadySeconds(30);
    expect($daemonSet->getMinReadySeconds())->toBe(30);
});

it('defaults to 0 min ready seconds when not set', function (): void {
    $daemonSet = new DaemonSet();
    expect($daemonSet->getMinReadySeconds())->toBe(0);
});

it('can set and get revision history limit', function (): void {
    $daemonSet = new DaemonSet();
    $daemonSet->setRevisionHistoryLimit(10);
    expect($daemonSet->getRevisionHistoryLimit())->toBe(10);
});

it('can add node selector', function (): void {
    $daemonSet = new DaemonSet();
    $nodeSelector = ['disktype' => 'ssd'];
    $daemonSet->addNodeSelector($nodeSelector);

    $template = $daemonSet->getTemplate();
    expect($template)->not->toBeNull();
    if ($template !== null) {
        expect($template['spec']['nodeSelector'])->toBe($nodeSelector);
    }
});

it('can add toleration', function (): void {
    $daemonSet = new DaemonSet();
    $toleration = [
        'key'      => 'node-role.kubernetes.io/master',
        'operator' => 'Exists',
        'effect'   => 'NoSchedule',
    ];

    $daemonSet->addToleration($toleration);

    $template = $daemonSet->getTemplate();
    expect($template)->not->toBeNull();
    if ($template !== null) {
        expect($template['spec']['tolerations'])->toBe([$toleration]);
    }
});

it('can create toleration with helper method', function (): void {
    $daemonSet = new DaemonSet();
    $toleration = $daemonSet->createToleration(
        'custom-taint',
        'Equal',
        'special-value',
        'NoExecute',
        300
    );

    expect($toleration['key'])->toBe('custom-taint');
    expect($toleration['operator'])->toBe('Equal');
    expect($toleration['value'])->toBe('special-value');
    expect($toleration['effect'])->toBe('NoExecute');
    expect($toleration['tolerationSeconds'])->toBe(300);
});

it('can configure to run on all nodes', function (): void {
    $daemonSet = new DaemonSet();
    $daemonSet->runOnAllNodes();

    $template = $daemonSet->getTemplate();
    expect($template)->not->toBeNull();
    if ($template !== null) {
        expect($template['spec']['tolerations'])->toHaveCount(2);
    }
});

it('can configure to run on worker nodes only', function (): void {
    $daemonSet = new DaemonSet();
    $daemonSet->runOnWorkerNodesOnly();

    $template = $daemonSet->getTemplate();
    expect($template)->not->toBeNull();
    if ($template !== null) {
        expect($template['spec']['nodeSelector']['node-role.kubernetes.io/worker'])->toBe('true');
    }
});

it('can set privileged security context', function (): void {
    $daemonSet = new DaemonSet();
    $daemonSet->setPodTemplate(['app' => 'test'], [['name' => 'test', 'image' => 'test']]);
    $daemonSet->setPrivileged(true);

    $template = $daemonSet->getTemplate();
    expect($template)->not->toBeNull();
    if ($template !== null) {
        expect($template['spec']['containers'][0]['securityContext']['privileged'])->toBeTrue();
    }
});

it('can set host network', function (): void {
    $daemonSet = new DaemonSet();
    $daemonSet->setHostNetwork(true);

    $template = $daemonSet->getTemplate();
    expect($template)->not->toBeNull();
    if ($template !== null) {
        expect($template['spec']['hostNetwork'])->toBeTrue();
    }
});

it('can set host PID', function (): void {
    $daemonSet = new DaemonSet();
    $daemonSet->setHostPID(true);

    $template = $daemonSet->getTemplate();
    expect($template)->not->toBeNull();
    if ($template !== null) {
        expect($template['spec']['hostPID'])->toBeTrue();
    }
});

it('can get status fields', function (): void {
    $daemonSet = new DaemonSet();
    expect($daemonSet->getCurrentNumberScheduled())->toBe(0);
    expect($daemonSet->getDesiredNumberScheduled())->toBe(0);
    expect($daemonSet->getNumberReady())->toBe(0);
    expect($daemonSet->getUpdatedNumberScheduled())->toBe(0);
    expect($daemonSet->getNumberAvailable())->toBe(0);
    expect($daemonSet->getNumberUnavailable())->toBe(0);
});

it('returns empty arrays when no configuration is set', function (): void {
    $daemonSet = new DaemonSet();
    expect($daemonSet->getStatus())->toBe([]);
});

it('returns null when no configuration is set', function (): void {
    $daemonSet = new DaemonSet();
    expect($daemonSet->getSelector())->toBeNull();
    expect($daemonSet->getTemplate())->toBeNull();
    expect($daemonSet->getUpdateStrategy())->toBeNull();
    expect($daemonSet->getRevisionHistoryLimit())->toBeNull();
});

it('can chain setter methods', function (): void {
    $daemonSet = new DaemonSet();
    $result = $daemonSet
        ->setName('test-daemonset')
        ->setNamespace('kube-system')
        ->setSelectorMatchLabels(['app' => 'monitoring'])
        ->setMinReadySeconds(10)
        ->runOnAllNodes();

    expect($result)->toBe($daemonSet);
    expect($daemonSet->getName())->toBe('test-daemonset');
    expect($daemonSet->getNamespace())->toBe('kube-system');
    expect($daemonSet->getMinReadySeconds())->toBe(10);
});
