<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\RbacAuthorizationK8sIo\V1;

use Kubernetes\API\RbacAuthorizationK8sIo\V1\ClusterRole;

it('can create a cluster role', function (): void {
    $clusterRole = new ClusterRole();
    expect($clusterRole->getApiVersion())->toBe('rbac.authorization.k8s.io/v1');
    expect($clusterRole->getKind())->toBe('ClusterRole');
});

it('does not have namespace methods on cluster-scoped resources', function (): void {
    $clusterRole = new ClusterRole();
    expect(method_exists($clusterRole, 'setNamespace'))->toBeFalse();
    expect(method_exists($clusterRole, 'getNamespace'))->toBeFalse();
});

it('can set and get rules', function (): void {
    $clusterRole = new ClusterRole();
    $rules = [
        [
            'apiGroups' => [''],
            'resources' => ['nodes'],
            'verbs'     => ['get', 'list'],
        ],
    ];

    $clusterRole->setRules($rules);
    expect($clusterRole->getRules())->toBe($rules);
});

it('can add individual rules', function (): void {
    $clusterRole = new ClusterRole();
    $rule = [
        'apiGroups' => [''],
        'resources' => ['namespaces'],
        'verbs'     => ['create', 'delete'],
    ];

    $clusterRole->addRule($rule);
    expect($clusterRole->getRules())->toBe([$rule]);
});

it('can set and get aggregation rule', function (): void {
    $clusterRole = new ClusterRole();
    $aggregationRule = [
        'clusterRoleSelectors' => [
            [
                'matchLabels' => ['rbac.example.com/aggregate-to-admin' => 'true'],
            ],
        ],
    ];

    $clusterRole->setAggregationRule($aggregationRule);
    expect($clusterRole->getAggregationRule())->toBe($aggregationRule);
});

it('can add cluster admin rule', function (): void {
    $clusterRole = new ClusterRole();
    $clusterRole->addClusterAdminRule();

    $rules = $clusterRole->getRules();
    expect($rules[0]['apiGroups'])->toBe(['*']);
    expect($rules[0]['resources'])->toBe(['*']);
    expect($rules[0]['verbs'])->toBe(['*']);
});

it('can add node management rule', function (): void {
    $clusterRole = new ClusterRole();
    $clusterRole->addNodeManagementRule();

    $rules = $clusterRole->getRules();
    expect($rules[0]['resources'])->toBe(['nodes', 'nodes/status', 'nodes/proxy']);
    expect($rules[0]['verbs'])->toContain('get', 'list', 'watch', 'create', 'update', 'patch', 'delete');
});

it('can add namespace management rule', function (): void {
    $clusterRole = new ClusterRole();
    $clusterRole->addNamespaceManagementRule();

    $rules = $clusterRole->getRules();
    expect($rules[0]['resources'])->toBe(['namespaces', 'namespaces/status']);
});

it('can enable aggregation', function (): void {
    $clusterRole = new ClusterRole();
    $labels = ['rbac.example.com/aggregate-to-view' => 'true'];
    $clusterRole->enableAggregation($labels);

    $aggregationRule = $clusterRole->getAggregationRule();
    expect($aggregationRule)->not->toBeNull();
    if ($aggregationRule !== null) {
        expect($aggregationRule['clusterRoleSelectors'][0]['matchLabels'])->toBe($labels);
    }
});

it('can add resource rules with helper method', function (): void {
    $clusterRole = new ClusterRole();
    $clusterRole->addResourceRule(['apps'], ['deployments'], ['get', 'list']);

    $rules = $clusterRole->getRules();
    expect($rules[0]['apiGroups'])->toBe(['apps']);
    expect($rules[0]['resources'])->toBe(['deployments']);
    expect($rules[0]['verbs'])->toBe(['get', 'list']);
});

it('can add non-resource rules', function (): void {
    $clusterRole = new ClusterRole();
    $clusterRole->addNonResourceRule(['/api/*'], ['get']);

    $rules = $clusterRole->getRules();
    expect($rules[0]['nonResourceURLs'])->toBe(['/api/*']);
    expect($rules[0]['verbs'])->toBe(['get']);
});

it('can add read-only rules', function (): void {
    $clusterRole = new ClusterRole();
    $clusterRole->addReadOnlyRule([''], ['pods']);

    $rules = $clusterRole->getRules();
    expect($rules[0]['verbs'])->toBe(['get', 'list', 'watch']);
});

it('returns empty array when no rules are set', function (): void {
    $clusterRole = new ClusterRole();
    expect($clusterRole->getRules())->toBe([]);
});

it('returns null when no aggregation rule is set', function (): void {
    $clusterRole = new ClusterRole();
    expect($clusterRole->getAggregationRule())->toBeNull();
});

it('can chain setter methods', function (): void {
    $clusterRole = new ClusterRole();
    $result = $clusterRole
        ->setName('test-cluster-role')
        ->addNodeManagementRule()
        ->addReadOnlyRule(['apps'], ['deployments']);

    expect($result)->toBe($clusterRole);
    expect($clusterRole->getName())->toBe('test-cluster-role');
    expect($clusterRole->getRules())->toHaveCount(2);
});
