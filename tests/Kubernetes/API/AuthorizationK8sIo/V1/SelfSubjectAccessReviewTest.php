<?php

declare(strict_types=1);

use Kubernetes\API\AuthorizationK8sIo\V1\SelfSubjectAccessReview;

it('can create a SelfSubjectAccessReview', function (): void {
    $review = new SelfSubjectAccessReview();
    expect($review->getApiVersion())->toBe('authorization.k8s.io/v1');
    expect($review->getKind())->toBe('SelfSubjectAccessReview');
});

it('can set and get resource attributes', function (): void {
    $review = new SelfSubjectAccessReview();
    $review->setResourceAttributes('create', 'pods', 'v1', 'v1', 'default', 'test-pod', 'logs');

    $attributes = $review->getResourceAttributes();
    expect($attributes)->not->toBeNull();
    if ($attributes !== null) {
        expect($attributes['verb'])->toBe('create');
        expect($attributes['resource'])->toBe('pods');
        expect($attributes['group'])->toBe('v1');
        expect($attributes['version'])->toBe('v1');
        expect($attributes['namespace'])->toBe('default');
        expect($attributes['name'])->toBe('test-pod');
        expect($attributes['subresource'])->toBe('logs');
    }
});

it('can set and get non-resource attributes', function (): void {
    $review = new SelfSubjectAccessReview();
    $review->setNonResourceAttributes('/metrics', 'get');

    $attributes = $review->getNonResourceAttributes();
    expect($attributes)->not->toBeNull();
    if ($attributes !== null) {
        expect($attributes['path'])->toBe('/metrics');
        expect($attributes['verb'])->toBe('get');
    }
});

it('can check resource access', function (): void {
    $review = new SelfSubjectAccessReview();
    $result = $review->checkResourceAccess('delete', 'services', 'kube-system', 'dns');

    expect($result)->toBe($review);

    $attributes = $review->getResourceAttributes();
    expect($attributes)->not->toBeNull();
    if ($attributes !== null) {
        expect($attributes['verb'])->toBe('delete');
        expect($attributes['resource'])->toBe('services');
        expect($attributes['namespace'])->toBe('kube-system');
        expect($attributes['name'])->toBe('dns');
    }
});

it('can check cluster access', function (): void {
    $review = new SelfSubjectAccessReview();
    $result = $review->checkClusterAccess('get', 'persistentvolumes');

    expect($result)->toBe($review);

    $attributes = $review->getResourceAttributes();
    expect($attributes)->not->toBeNull();
    if ($attributes !== null) {
        expect($attributes['verb'])->toBe('get');
        expect($attributes['resource'])->toBe('persistentvolumes');
    }
});

it('can check non-resource access', function (): void {
    $review = new SelfSubjectAccessReview();
    $result = $review->checkNonResourceAccess('/version', 'get');

    expect($result)->toBe($review);

    $attributes = $review->getNonResourceAttributes();
    expect($attributes)->not->toBeNull();
    if ($attributes !== null) {
        expect($attributes['path'])->toBe('/version');
        expect($attributes['verb'])->toBe('get');
    }
});

it('has convenience methods for common checks', function (): void {
    $review = new SelfSubjectAccessReview();

    $review->canCreatePods('default');
    $attributes = $review->getResourceAttributes();
    expect($attributes)->not->toBeNull();
    if ($attributes !== null) {
        expect($attributes['verb'])->toBe('create');
        expect($attributes['resource'])->toBe('pods');
        expect($attributes['namespace'])->toBe('default');
    }

    $review->canListSecrets('kube-system');
    $attributes = $review->getResourceAttributes();
    expect($attributes)->not->toBeNull();
    if ($attributes !== null) {
        expect($attributes['verb'])->toBe('list');
        expect($attributes['resource'])->toBe('secrets');
        expect($attributes['namespace'])->toBe('kube-system');
    }

    $review->canDeleteDeployments('production');
    $attributes = $review->getResourceAttributes();
    expect($attributes)->not->toBeNull();
    if ($attributes !== null) {
        expect($attributes['verb'])->toBe('delete');
        expect($attributes['resource'])->toBe('deployments');
        expect($attributes['namespace'])->toBe('production');
    }

    $review->canAccessNodes('list');
    $attributes = $review->getResourceAttributes();
    expect($attributes)->not->toBeNull();
    if ($attributes !== null) {
        expect($attributes['verb'])->toBe('list');
        expect($attributes['resource'])->toBe('nodes');
    }
});

it('can check authorization status', function (): void {
    $review = new SelfSubjectAccessReview();
    expect($review->isAllowed())->toBe(false);
    expect($review->isDenied())->toBe(false);
    expect($review->getReason())->toBeNull();
    expect($review->getEvaluationError())->toBeNull();
});

it('can chain setter methods', function (): void {
    $review = new SelfSubjectAccessReview();
    $result = $review
        ->setName('self-check')
        ->setResourceAttributes('get', 'pods')
        ->setNonResourceAttributes('/api');

    expect($result)->toBe($review);
});
