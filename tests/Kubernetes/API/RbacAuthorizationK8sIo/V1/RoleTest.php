<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\RbacAuthorizationK8sIo\V1;

use Kubernetes\API\RbacAuthorizationK8sIo\V1\Role;

it('can create a role', function (): void {
    $role = new Role();
    expect($role->getApiVersion())->toBe('rbac.authorization.k8s.io/v1');
    expect($role->getKind())->toBe('Role');
});

it('can set and get namespace', function (): void {
    $role = new Role();
    $role->setNamespace('test-namespace');
    expect($role->getNamespace())->toBe('test-namespace');
});

it('can set and get rules', function (): void {
    $role = new Role();
    $rules = [
        [
            'apiGroups' => [''],
            'resources' => ['pods'],
            'verbs'     => ['get', 'list'],
        ],
    ];

    $role->setRules($rules);
    expect($role->getRules())->toBe($rules);
});

it('can add individual rules', function (): void {
    $role = new Role();
    $rule = [
        'apiGroups' => ['apps'],
        'resources' => ['deployments'],
        'verbs'     => ['create', 'update'],
    ];

    $role->addRule($rule);
    expect($role->getRules())->toBe([$rule]);
});

it('can add resource rules with helper method', function (): void {
    $role = new Role();
    $role->addResourceRule([''], ['pods'], ['get', 'list']);

    $rules = $role->getRules();
    expect($rules)->toHaveCount(1);
    expect($rules[0]['apiGroups'])->toBe(['']);
    expect($rules[0]['resources'])->toBe(['pods']);
    expect($rules[0]['verbs'])->toBe(['get', 'list']);
});

it('can add resource rules with resource names', function (): void {
    $role = new Role();
    $role->addResourceRule([''], ['secrets'], ['get'], ['my-secret']);

    $rules = $role->getRules();
    expect($rules[0]['resourceNames'])->toBe(['my-secret']);
});

it('can add non-resource rules', function (): void {
    $role = new Role();
    $role->addNonResourceRule(['/healthz'], ['get']);

    $rules = $role->getRules();
    expect($rules[0]['nonResourceURLs'])->toBe(['/healthz']);
    expect($rules[0]['verbs'])->toBe(['get']);
});

it('can add core resource rules', function (): void {
    $role = new Role();
    $role->addCoreResourceRule(['pods', 'services']);

    $rules = $role->getRules();
    expect($rules[0]['apiGroups'])->toBe(['']);
    expect($rules[0]['resources'])->toBe(['pods', 'services']);
    expect($rules[0]['verbs'])->toBe(['get', 'list', 'watch', 'create', 'update', 'patch', 'delete']);
});

it('can add read-only rules', function (): void {
    $role = new Role();
    $role->addReadOnlyRule(['apps'], ['deployments']);

    $rules = $role->getRules();
    expect($rules[0]['verbs'])->toBe(['get', 'list', 'watch']);
});

it('returns empty array when no rules are set', function (): void {
    $role = new Role();
    expect($role->getRules())->toBe([]);
});

it('can chain setter methods', function (): void {
    $role = new Role();
    $result = $role
        ->setName('test-role')
        ->setNamespace('default')
        ->addCoreResourceRule(['pods']);

    expect($result)->toBe($role);
    expect($role->getName())->toBe('test-role');
    expect($role->getNamespace())->toBe('default');
    expect($role->getRules())->toHaveCount(1);
});
