<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\AuthorizationK8sIo\V1;

use Kubernetes\API\AuthorizationK8sIo\V1\SubjectAccessReview;

it('can create a SubjectAccessReview', function (): void {
    $review = new SubjectAccessReview();
    expect($review->getApiVersion())->toBe('authorization.k8s.io/v1');
    expect($review->getKind())->toBe('SubjectAccessReview');
});

it('can set and get resource attributes', function (): void {
    $review = new SubjectAccessReview();
    $review->setResourceAttributes('get', 'pods', 'v1', 'v1', 'default', 'test-pod', 'status');

    $attributes = $review->getResourceAttributes();
    expect($attributes)->not->toBeNull();
    if ($attributes !== null) {
        expect($attributes['verb'])->toBe('get');
        expect($attributes['resource'])->toBe('pods');
        expect($attributes['group'])->toBe('v1');
        expect($attributes['version'])->toBe('v1');
        expect($attributes['namespace'])->toBe('default');
        expect($attributes['name'])->toBe('test-pod');
        expect($attributes['subresource'])->toBe('status');
    }
});

it('can set and get non-resource attributes', function (): void {
    $review = new SubjectAccessReview();
    $review->setNonResourceAttributes('/api/v1', 'get');

    $attributes = $review->getNonResourceAttributes();
    expect($attributes)->not->toBeNull();
    if ($attributes !== null) {
        expect($attributes['path'])->toBe('/api/v1');
        expect($attributes['verb'])->toBe('get');
    }
});

it('can set and get user', function (): void {
    $review = new SubjectAccessReview();
    $review->setUser('test-user', 'uid-123', ['admin', 'developer'], ['role' => ['admin']]);

    $user = $review->getUser();
    expect($user)->not->toBeNull();
    if ($user !== null) {
        expect($user['username'])->toBe('test-user');
        expect($user['uid'])->toBe('uid-123');
        expect($user['groups'])->toBe(['admin', 'developer']);
        expect($user['extra'])->toBe(['role' => ['admin']]);
    }
});

it('can check resource access', function (): void {
    $review = new SubjectAccessReview();
    $result = $review->checkResourceAccess('test-user', 'create', 'pods', 'default', 'test-pod');

    expect($result)->toBe($review);

    $user = $review->getUser();
    expect($user)->not->toBeNull();
    if ($user !== null) {
        expect($user['username'])->toBe('test-user');
    }

    $attributes = $review->getResourceAttributes();
    expect($attributes)->not->toBeNull();
    if ($attributes !== null) {
        expect($attributes['verb'])->toBe('create');
        expect($attributes['resource'])->toBe('pods');
        expect($attributes['namespace'])->toBe('default');
        expect($attributes['name'])->toBe('test-pod');
    }
});

it('can check cluster access', function (): void {
    $review = new SubjectAccessReview();
    $result = $review->checkClusterAccess('test-user', 'list', 'nodes');

    expect($result)->toBe($review);

    $attributes = $review->getResourceAttributes();
    expect($attributes)->not->toBeNull();
    if ($attributes !== null) {
        expect($attributes['verb'])->toBe('list');
        expect($attributes['resource'])->toBe('nodes');
    }
});

it('can check non-resource access', function (): void {
    $review = new SubjectAccessReview();
    $result = $review->checkNonResourceAccess('test-user', '/healthz', 'get');

    expect($result)->toBe($review);

    $attributes = $review->getNonResourceAttributes();
    expect($attributes)->not->toBeNull();
    if ($attributes !== null) {
        expect($attributes['path'])->toBe('/healthz');
        expect($attributes['verb'])->toBe('get');
    }
});

it('can check authorization status', function (): void {
    $review = new SubjectAccessReview();
    expect($review->isAllowed())->toBe(false);
    expect($review->isDenied())->toBe(false);
    expect($review->getReason())->toBeNull();
    expect($review->getEvaluationError())->toBeNull();
});

it('can chain setter methods', function (): void {
    $review = new SubjectAccessReview();
    $result = $review
        ->setName('test-review')
        ->setUser('test-user')
        ->setResourceAttributes('get', 'pods');

    expect($result)->toBe($review);
});
