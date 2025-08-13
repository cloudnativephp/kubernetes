<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\RbacAuthorizationK8sIo\V1;

use Kubernetes\API\RbacAuthorizationK8sIo\V1\RoleBinding;

it('can create a role binding', function (): void {
    $roleBinding = new RoleBinding();
    expect($roleBinding->getApiVersion())->toBe('rbac.authorization.k8s.io/v1');
    expect($roleBinding->getKind())->toBe('RoleBinding');
});

it('can set and get namespace', function (): void {
    $roleBinding = new RoleBinding();
    $roleBinding->setNamespace('test-namespace');
    expect($roleBinding->getNamespace())->toBe('test-namespace');
});

it('can set and get subjects', function (): void {
    $roleBinding = new RoleBinding();
    $subjects = [
        [
            'kind' => 'User',
            'name' => 'alice',
        ],
    ];

    $roleBinding->setSubjects($subjects);
    expect($roleBinding->getSubjects())->toBe($subjects);
});

it('can add individual subjects', function (): void {
    $roleBinding = new RoleBinding();
    $subject = [
        'kind'      => 'ServiceAccount',
        'name'      => 'my-sa',
        'namespace' => 'default',
    ];

    $roleBinding->addSubject($subject);
    expect($roleBinding->getSubjects())->toBe([$subject]);
});

it('can set and get role reference', function (): void {
    $roleBinding = new RoleBinding();
    $roleRef = [
        'kind'     => 'Role',
        'name'     => 'my-role',
        'apiGroup' => 'rbac.authorization.k8s.io',
    ];

    $roleBinding->setRoleRef($roleRef);
    expect($roleBinding->getRoleRef())->toBe($roleRef);
});

it('can add user subjects', function (): void {
    $roleBinding = new RoleBinding();
    $roleBinding->addUser('alice');

    $subjects = $roleBinding->getSubjects();
    expect($subjects)->toHaveCount(1);
    expect($subjects[0]['kind'])->toBe('User');
    expect($subjects[0]['name'])->toBe('alice');
});

it('can add user subjects with namespace', function (): void {
    $roleBinding = new RoleBinding();
    $roleBinding->addUser('alice', 'custom-ns');

    $subjects = $roleBinding->getSubjects();
    expect($subjects[0]['namespace'])->toBe('custom-ns');
});

it('can add group subjects', function (): void {
    $roleBinding = new RoleBinding();
    $roleBinding->addGroup('developers');

    $subjects = $roleBinding->getSubjects();
    expect($subjects[0]['kind'])->toBe('Group');
    expect($subjects[0]['name'])->toBe('developers');
});

it('can add service account subjects', function (): void {
    $roleBinding = new RoleBinding();
    $roleBinding->addServiceAccount('my-sa', 'default');

    $subjects = $roleBinding->getSubjects();
    expect($subjects[0]['kind'])->toBe('ServiceAccount');
    expect($subjects[0]['name'])->toBe('my-sa');
    expect($subjects[0]['namespace'])->toBe('default');
});

it('can bind to role', function (): void {
    $roleBinding = new RoleBinding();
    $roleBinding->bindToRole('my-role');

    $roleRef = $roleBinding->getRoleRef();
    expect($roleRef)->not->toBeNull();
    if ($roleRef !== null) {
        expect($roleRef['kind'])->toBe('Role');
        expect($roleRef['name'])->toBe('my-role');
        expect($roleRef['apiGroup'])->toBe('rbac.authorization.k8s.io');
    }
});

it('can bind to cluster role', function (): void {
    $roleBinding = new RoleBinding();
    $roleBinding->bindToClusterRole('cluster-admin');

    $roleRef = $roleBinding->getRoleRef();
    expect($roleRef)->not->toBeNull();
    if ($roleRef !== null) {
        expect($roleRef['kind'])->toBe('ClusterRole');
        expect($roleRef['name'])->toBe('cluster-admin');
    }
});

it('returns empty array when no subjects are set', function (): void {
    $roleBinding = new RoleBinding();
    expect($roleBinding->getSubjects())->toBe([]);
});

it('returns null when no role ref is set', function (): void {
    $roleBinding = new RoleBinding();
    expect($roleBinding->getRoleRef())->toBeNull();
});

it('can chain setter methods', function (): void {
    $roleBinding = new RoleBinding();
    $result = $roleBinding
        ->setName('test-binding')
        ->setNamespace('default')
        ->bindToRole('my-role')
        ->addUser('alice');

    expect($result)->toBe($roleBinding);
    expect($roleBinding->getName())->toBe('test-binding');
    expect($roleBinding->getNamespace())->toBe('default');
    expect($roleBinding->getSubjects())->toHaveCount(1);
});
