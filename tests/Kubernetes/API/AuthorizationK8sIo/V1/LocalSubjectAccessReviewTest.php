<?php

declare(strict_types=1);

use Kubernetes\API\AuthorizationK8sIo\V1\LocalSubjectAccessReview;

it('can create a LocalSubjectAccessReview', function (): void {
    $review = new LocalSubjectAccessReview();
    expect($review->getApiVersion())->toBe('authorization.k8s.io/v1');
    expect($review->getKind())->toBe('LocalSubjectAccessReview');
});

it('can set namespace', function (): void {
    $review = new LocalSubjectAccessReview();
    $result = $review->setNamespace('test-namespace');
    expect($result)->toBe($review);
    expect($review->getNamespace())->toBe('test-namespace');
});

it('can set and get resource attributes', function (): void {
    $review = new LocalSubjectAccessReview();
    $review->setResourceAttributes('update', 'configmaps', 'v1', 'v1', 'my-config', 'data');

    $attributes = $review->getResourceAttributes();
    expect($attributes)->not->toBeNull();
    if ($attributes !== null) {
        expect($attributes['verb'])->toBe('update');
        expect($attributes['resource'])->toBe('configmaps');
        expect($attributes['group'])->toBe('v1');
        expect($attributes['version'])->toBe('v1');
        expect($attributes['name'])->toBe('my-config');
        expect($attributes['subresource'])->toBe('data');
    }
});

it('can set and get non-resource attributes', function (): void {
    $review = new LocalSubjectAccessReview();
    $review->setNonResourceAttributes('/api/v1/namespaces/default', 'post');

    $attributes = $review->getNonResourceAttributes();
    expect($attributes)->not->toBeNull();
    if ($attributes !== null) {
        expect($attributes['path'])->toBe('/api/v1/namespaces/default');
        expect($attributes['verb'])->toBe('post');
    }
});

it('can set and get user', function (): void {
    $review = new LocalSubjectAccessReview();
    $review->setUser('namespace-user', 'uid-456', ['developers'], ['team' => ['backend']]);

    $user = $review->getUser();
    expect($user)->not->toBeNull();
    if ($user !== null) {
        expect($user['username'])->toBe('namespace-user');
        expect($user['uid'])->toBe('uid-456');
        expect($user['groups'])->toBe(['developers']);
        expect($user['extra'])->toBe(['team' => ['backend']]);
    }
});

it('can check resource access within namespace', function (): void {
    $review = new LocalSubjectAccessReview();
    $result = $review->checkResourceAccess('dev-user', 'create', 'secrets', 'my-secret');

    expect($result)->toBe($review);

    $user = $review->getUser();
    expect($user)->not->toBeNull();
    if ($user !== null) {
        expect($user['username'])->toBe('dev-user');
    }

    $attributes = $review->getResourceAttributes();
    expect($attributes)->not->toBeNull();
    if ($attributes !== null) {
        expect($attributes['verb'])->toBe('create');
        expect($attributes['resource'])->toBe('secrets');
        expect($attributes['name'])->toBe('my-secret');
    }
});

it('has convenience methods for common resource checks', function (): void {
    $review = new LocalSubjectAccessReview();

    $review->checkPodAccess('dev-user', 'get');
    $attributes = $review->getResourceAttributes();
    expect($attributes)->not->toBeNull();
    if ($attributes !== null) {
        expect($attributes['verb'])->toBe('get');
        expect($attributes['resource'])->toBe('pods');
    }

    $review->checkSecretAccess('admin-user', 'create');
    $attributes = $review->getResourceAttributes();
    expect($attributes)->not->toBeNull();
    if ($attributes !== null) {
        expect($attributes['verb'])->toBe('create');
        expect($attributes['resource'])->toBe('secrets');
    }

    $review->checkServiceAccess('ops-user', 'delete');
    $attributes = $review->getResourceAttributes();
    expect($attributes)->not->toBeNull();
    if ($attributes !== null) {
        expect($attributes['verb'])->toBe('delete');
        expect($attributes['resource'])->toBe('services');
    }
});

it('can check authorization status', function (): void {
    $review = new LocalSubjectAccessReview();
    expect($review->isAllowed())->toBe(false);
    expect($review->isDenied())->toBe(false);
    expect($review->getReason())->toBeNull();
    expect($review->getEvaluationError())->toBeNull();
});

it('can chain setter methods', function (): void {
    $review = new LocalSubjectAccessReview();
    $result = $review
        ->setName('local-check')
        ->setNamespace('development')
        ->setUser('test-user')
        ->setResourceAttributes('list', 'pods');

    expect($result)->toBe($review);
});
