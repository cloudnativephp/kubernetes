<?php

declare(strict_types=1);

namespace Tests\Kubernetes\Traits;

use Kubernetes\API\Core\V1\KubernetesNamespace;
use Kubernetes\API\Core\V1\Node;
use Kubernetes\API\Core\V1\PersistentVolume;
use Kubernetes\API\Core\V1\Secret;
use Kubernetes\API\Core\V1\Service;

it('can set and get namespace on namespaced resources', function (): void {
    $service = new Service();
    $secret = new Secret();

    // Test Service namespace methods
    expect($service->getNamespace())->toBeNull();
    $service->setNamespace('default');
    expect($service->getNamespace())->toBe('default');

    // Test Secret namespace methods
    expect($secret->getNamespace())->toBeNull();
    $secret->setNamespace('kube-system');
    expect($secret->getNamespace())->toBe('kube-system');
});

it('does not have namespace methods on non-namespaced resources', function (): void {
    $node = new Node();
    $pv = new PersistentVolume();
    $namespace = new KubernetesNamespace();

    // These resources should NOT have namespace methods
    expect(method_exists($node, 'getNamespace'))->toBeFalse();
    expect(method_exists($node, 'setNamespace'))->toBeFalse();

    expect(method_exists($pv, 'getNamespace'))->toBeFalse();
    expect(method_exists($pv, 'setNamespace'))->toBeFalse();

    expect(method_exists($namespace, 'getNamespace'))->toBeFalse();
    expect(method_exists($namespace, 'setNamespace'))->toBeFalse();
});

it('can chain namespace setter methods', function (): void {
    $service = new Service();

    $result = $service
        ->setName('my-service')
        ->setNamespace('production');

    expect($result)->toBe($service);
    expect($service->getName())->toBe('my-service');
    expect($service->getNamespace())->toBe('production');
});
