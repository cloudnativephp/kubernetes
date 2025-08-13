<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\NetworkingK8sIo\V1;

use Kubernetes\API\NetworkingK8sIo\V1\IngressClass;

it('can create an ingress class', function (): void {
    $ingressClass = new IngressClass();
    expect($ingressClass->getApiVersion())->toBe('networking.k8s.io/v1');
    expect($ingressClass->getKind())->toBe('IngressClass');
});

it('does not have namespace methods on cluster-scoped resources', function (): void {
    $ingressClass = new IngressClass();
    expect(method_exists($ingressClass, 'setNamespace'))->toBeFalse();
    expect(method_exists($ingressClass, 'getNamespace'))->toBeFalse();
});

it('can set and get controller', function (): void {
    $ingressClass = new IngressClass();
    $ingressClass->setController('k8s.io/ingress-nginx');
    expect($ingressClass->getController())->toBe('k8s.io/ingress-nginx');
});

it('can set and get parameters', function (): void {
    $ingressClass = new IngressClass();
    $parameters = [
        'apiGroup' => '',
        'kind'     => 'ConfigMap',
        'name'     => 'nginx-config',
    ];

    $ingressClass->setParameters($parameters);
    expect($ingressClass->getParameters())->toBe($parameters);
});

it('can check if marked as default', function (): void {
    $ingressClass = new IngressClass();
    expect($ingressClass->isDefault())->toBeFalse();

    $annotations = $ingressClass->getAnnotations();
    $annotations['ingressclass.kubernetes.io/is-default-class'] = 'true';
    $ingressClass->setAnnotations($annotations);
    expect($ingressClass->isDefault())->toBeTrue();
});

it('can set as default ingress class', function (): void {
    $ingressClass = new IngressClass();
    $ingressClass->setAsDefault(true);

    expect($ingressClass->isDefault())->toBeTrue();
    $annotations = $ingressClass->getAnnotations();
    expect($annotations['ingressclass.kubernetes.io/is-default-class'])->toBe('true');
});

it('can unset as default ingress class', function (): void {
    $ingressClass = new IngressClass();
    $ingressClass->setAsDefault(true);
    $ingressClass->setAsDefault(false);

    expect($ingressClass->isDefault())->toBeFalse();
    $annotations = $ingressClass->getAnnotations();
    expect($annotations['ingressclass.kubernetes.io/is-default-class'] ?? null)->toBeNull();
});

it('can set ConfigMap parameters', function (): void {
    $ingressClass = new IngressClass();
    $ingressClass->setConfigMapParameters('nginx-config', 'kube-system', 'Cluster');

    $parameters = $ingressClass->getParameters();
    expect($parameters)->not->toBeNull();
    if ($parameters !== null) {
        expect($parameters['kind'])->toBe('ConfigMap');
        expect($parameters['name'])->toBe('nginx-config');
        expect($parameters['namespace'])->toBe('kube-system');
        expect($parameters['scope'])->toBe('Cluster');
    }
});

it('can set Secret parameters', function (): void {
    $ingressClass = new IngressClass();
    $ingressClass->setSecretParameters('nginx-secret', 'kube-system');

    $parameters = $ingressClass->getParameters();
    expect($parameters)->not->toBeNull();
    if ($parameters !== null) {
        expect($parameters['kind'])->toBe('Secret');
        expect($parameters['name'])->toBe('nginx-secret');
        expect($parameters['namespace'])->toBe('kube-system');
    }
});

it('can create NGINX ingress class', function (): void {
    $ingressClass = new IngressClass();
    $ingressClass->createNginxIngressClass(true);

    expect($ingressClass->getController())->toBe('k8s.io/ingress-nginx');
    expect($ingressClass->isDefault())->toBeTrue();
});

it('can create Traefik ingress class', function (): void {
    $ingressClass = new IngressClass();
    $ingressClass->createTraefikIngressClass();

    expect($ingressClass->getController())->toBe('traefik.io/ingress-controller');
    expect($ingressClass->isDefault())->toBeFalse();
});

it('can create HAProxy ingress class', function (): void {
    $ingressClass = new IngressClass();
    $ingressClass->createHaproxyIngressClass();

    expect($ingressClass->getController())->toBe('haproxy.org/ingress-controller');
});

it('can create AWS ALB ingress class', function (): void {
    $ingressClass = new IngressClass();
    $ingressClass->createAwsAlbIngressClass();

    expect($ingressClass->getController())->toBe('ingress.k8s.aws/alb');
});

it('can create GCE ingress class', function (): void {
    $ingressClass = new IngressClass();
    $ingressClass->createGceIngressClass();

    expect($ingressClass->getController())->toBe('k8s.io/ingress-gce');
});

it('can identify controller type', function (): void {
    $ingressClass = new IngressClass();

    $ingressClass->setController('k8s.io/ingress-nginx');
    expect($ingressClass->getControllerType())->toBe('nginx');

    $ingressClass->setController('traefik.io/ingress-controller');
    expect($ingressClass->getControllerType())->toBe('traefik');

    $ingressClass->setController('haproxy.org/ingress-controller');
    expect($ingressClass->getControllerType())->toBe('haproxy');

    $ingressClass->setController('ingress.k8s.aws/alb');
    expect($ingressClass->getControllerType())->toBe('aws-alb');

    $ingressClass->setController('k8s.io/ingress-gce');
    expect($ingressClass->getControllerType())->toBe('gce');

    $ingressClass->setController('custom.controller/unknown');
    expect($ingressClass->getControllerType())->toBe('unknown');
});

it('returns null when no configuration is set', function (): void {
    $ingressClass = new IngressClass();
    expect($ingressClass->getController())->toBeNull();
    expect($ingressClass->getParameters())->toBeNull();
});

it('can chain setter methods', function (): void {
    $ingressClass = new IngressClass();
    $result = $ingressClass
        ->setName('nginx-ingress')
        ->setController('k8s.io/ingress-nginx')
        ->setAsDefault(true);

    expect($result)->toBe($ingressClass);
    expect($ingressClass->getName())->toBe('nginx-ingress');
    expect($ingressClass->getController())->toBe('k8s.io/ingress-nginx');
    expect($ingressClass->isDefault())->toBeTrue();
});
