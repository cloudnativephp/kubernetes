<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\Apps\V1;

use Kubernetes\API\Apps\V1\Deployment;

it('can create a deployment with basic configuration', function (): void {
    $deployment = new Deployment();

    expect($deployment->getApiVersion())->toBe('apps/v1');
    expect($deployment->getKind())->toBe('Deployment');
    expect($deployment->getMetadata())->toBe([]);
    expect($deployment->getReplicas())->toBeNull();
});

it('can set and get deployment replicas', function (): void {
    $deployment = new Deployment();

    $deployment->setReplicas(3);

    expect($deployment->getReplicas())->toBe(3);
});

it('can set and get deployment selector', function (): void {
    $deployment = new Deployment();
    $selector = [
        'matchLabels' => ['app' => 'nginx'],
    ];

    $deployment->setSelector($selector);

    expect($deployment->getSelector())->toBe($selector);
});

it('can set and get pod template', function (): void {
    $deployment = new Deployment();
    $template = [
        'metadata' => [
            'labels' => ['app' => 'nginx'],
        ],
        'spec' => [
            'containers' => [
                [
                    'name'  => 'nginx',
                    'image' => 'nginx:latest',
                ],
            ],
        ],
    ];

    $deployment->setTemplate($template);

    expect($deployment->getTemplate())->toBe($template);
});

it('can convert deployment to array', function (): void {
    $deployment = new Deployment();
    $deployment->setName('nginx-deployment')
        ->setNamespace('default')
        ->setReplicas(3)
        ->setSelector(['matchLabels' => ['app' => 'nginx']])
        ->setTemplate([
            'metadata' => ['labels' => ['app' => 'nginx']],
            'spec'     => [
                'containers' => [
                    [
                        'name'  => 'nginx',
                        'image' => 'nginx:latest',
                    ],
                ],
            ],
        ]);

    $array = $deployment->toArray();

    expect($array)->toHaveKey('apiVersion', 'apps/v1');
    expect($array)->toHaveKey('kind', 'Deployment');
    expect($array)->toHaveKey('metadata.name', 'nginx-deployment');
    expect($array)->toHaveKey('spec.replicas', 3);
});
