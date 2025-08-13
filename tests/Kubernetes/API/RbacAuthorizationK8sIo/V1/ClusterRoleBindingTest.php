<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\RbacAuthorizationK8sIo\V1;

use Kubernetes\API\RbacAuthorizationK8sIo\V1\ClusterRoleBinding;

it('can create a cluster role binding', function (): void {
    $clusterRoleBinding = new ClusterRoleBinding();
    expect($clusterRoleBinding->getApiVersion())->toBe('rbac.authorization.k8s.io/v1');
    expect($clusterRoleBinding->getKind())->toBe('ClusterRoleBinding');
});

it('does not have namespace methods on cluster-scoped resources', function (): void {
    $clusterRoleBinding = new ClusterRoleBinding();
    expect(method_exists($clusterRoleBinding, 'setNamespace'))->toBeFalse();
    expect(method_exists($clusterRoleBinding, 'getNamespace'))->toBeFalse();
});

it('can set and get subjects', function (): void {
    $clusterRoleBinding = new ClusterRoleBinding();
    $subjects = [
        [
            'kind' => 'User',
            'name' => 'cluster-admin',
        ],
    ];

    $clusterRoleBinding->setSubjects($subjects);
    expect($clusterRoleBinding->getSubjects())->toBe($subjects);
});

it('can add individual subjects', function (): void {
    $clusterRoleBinding = new ClusterRoleBinding();
    $subject = [
        'kind' => 'Group',
        'name' => 'system:masters',
    ];

    $clusterRoleBinding->addSubject($subject);
    expect($clusterRoleBinding->getSubjects())->toBe([$subject]);
});

it('can set and get role reference', function (): void {
    $clusterRoleBinding = new ClusterRoleBinding();
    $roleRef = [
        'kind'     => 'ClusterRole',
        'name'     => 'cluster-admin',
        'apiGroup' => 'rbac.authorization.k8s.io',
    ];

    $clusterRoleBinding->setRoleRef($roleRef);
    expect($clusterRoleBinding->getRoleRef())->toBe($roleRef);
});

it('can add user subjects', function (): void {
    $clusterRoleBinding = new ClusterRoleBinding();
    $clusterRoleBinding->addUser('admin-user');

    $subjects = $clusterRoleBinding->getSubjects();
    expect($subjects)->toHaveCount(1);
    expect($subjects[0]['kind'])->toBe('User');
    expect($subjects[0]['name'])->toBe('admin-user');
});

it('can add group subjects', function (): void {
    $clusterRoleBinding = new ClusterRoleBinding();
    $clusterRoleBinding->addGroup('system:masters');

    $subjects = $clusterRoleBinding->getSubjects();
    expect($subjects[0]['kind'])->toBe('Group');
    expect($subjects[0]['name'])->toBe('system:masters');
});

it('can add service account subjects', function (): void {
    $clusterRoleBinding = new ClusterRoleBinding();
    $clusterRoleBinding->addServiceAccount('system-sa', 'kube-system');

    $subjects = $clusterRoleBinding->getSubjects();
    expect($subjects[0]['kind'])->toBe('ServiceAccount');
    expect($subjects[0]['name'])->toBe('system-sa');
    expect($subjects[0]['namespace'])->toBe('kube-system');
});

it('can bind to cluster role', function (): void {
    $clusterRoleBinding = new ClusterRoleBinding();
    $clusterRoleBinding->bindToClusterRole('cluster-admin');

    $roleRef = $clusterRoleBinding->getRoleRef();
    expect($roleRef)->not->toBeNull();
    if ($roleRef !== null) {
        expect($roleRef['kind'])->toBe('ClusterRole');
        expect($roleRef['name'])->toBe('cluster-admin');
        expect($roleRef['apiGroup'])->toBe('rbac.authorization.k8s.io');
    }
});

it('can create cluster admin binding', function (): void {
    $clusterRoleBinding = new ClusterRoleBinding();
    $clusterRoleBinding->createClusterAdminBinding('admin-user');

    $roleRef = $clusterRoleBinding->getRoleRef();
    expect($roleRef)->not->toBeNull();
    if ($roleRef !== null) {
        expect($roleRef['name'])->toBe('cluster-admin');
    }

    $subjects = $clusterRoleBinding->getSubjects();
    expect($subjects)->toHaveCount(1);
    expect($subjects[0]['name'])->toBe('admin-user');
});

it('can create cluster reader binding', function (): void {
    $clusterRoleBinding = new ClusterRoleBinding();
    $clusterRoleBinding->createClusterReaderBinding('reader-user');

    $roleRef = $clusterRoleBinding->getRoleRef();
    expect($roleRef)->not->toBeNull();
    if ($roleRef !== null) {
        expect($roleRef['name'])->toBe('view');
    }

    $subjects = $clusterRoleBinding->getSubjects();
    expect($subjects[0]['name'])->toBe('reader-user');
});

it('can create system binding', function (): void {
    $clusterRoleBinding = new ClusterRoleBinding();
    $clusterRoleBinding->createSystemBinding('system:node', 'node-sa', 'kube-system');

    $roleRef = $clusterRoleBinding->getRoleRef();
    expect($roleRef)->not->toBeNull();
    if ($roleRef !== null) {
        expect($roleRef['name'])->toBe('system:node');
    }

    $subjects = $clusterRoleBinding->getSubjects();
    expect($subjects[0]['kind'])->toBe('ServiceAccount');
    expect($subjects[0]['name'])->toBe('node-sa');
    expect($subjects[0]['namespace'])->toBe('kube-system');
});

it('returns empty array when no subjects are set', function (): void {
    $clusterRoleBinding = new ClusterRoleBinding();
    expect($clusterRoleBinding->getSubjects())->toBe([]);
});

it('returns null when no role ref is set', function (): void {
    $clusterRoleBinding = new ClusterRoleBinding();
    expect($clusterRoleBinding->getRoleRef())->toBeNull();
});

it('can chain setter methods', function (): void {
    $clusterRoleBinding = new ClusterRoleBinding();
    $result = $clusterRoleBinding
        ->setName('test-cluster-binding')
        ->bindToClusterRole('view')
        ->addUser('reader');

    expect($result)->toBe($clusterRoleBinding);
    expect($clusterRoleBinding->getName())->toBe('test-cluster-binding');
    expect($clusterRoleBinding->getSubjects())->toHaveCount(1);
});
