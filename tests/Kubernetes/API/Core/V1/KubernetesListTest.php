<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\Core\V1;

use Kubernetes\API\Core\V1\KubernetesList;
use Kubernetes\API\Core\V1\Pod;
use Kubernetes\API\Core\V1\Service;

it('can create a kubernetes list', function (): void {
    $list = new KubernetesList();
    expect($list->getApiVersion())->toBe('v1');
    expect($list->getKind())->toBe('List');
});

it('can manage items in the list', function (): void {
    $list = new KubernetesList();
    $pod = new Pod();
    $pod->setName('test-pod');

    $list->addItem($pod);

    expect($list->count())->toBe(1);
    expect($list->isEmpty())->toBeFalse();
    expect($list->getItems())->toHaveCount(1);
    expect($list->getItems()[0])->toBe($pod);
});

it('can chain setter methods', function (): void {
    $list = new KubernetesList();
    $pod = new Pod();

    $result = $list
        ->setName('test-list')
        ->addItem($pod);

    expect($result)->toBe($list);
});

it('can get first and last items', function (): void {
    $list = new KubernetesList();

    // Empty list
    expect($list->first())->toBeNull();
    expect($list->last())->toBeNull();

    // Add items
    $pod1 = new Pod();
    $pod1->setName('pod-1');
    $pod2 = new Pod();
    $pod2->setName('pod-2');

    $list->addItem($pod1)->addItem($pod2);

    expect($list->first())->toBe($pod1);
    expect($list->last())->toBe($pod2);
});

it('can filter items by kind', function (): void {
    $list = new KubernetesList();

    $pod = new Pod();
    $pod->setName('test-pod');

    $service = new Service();
    $service->setName('test-service');

    $list->addItem($pod)->addItem($service);

    $pods = $list->filterByKind('Pod');
    $services = $list->filterByKind('Service');

    expect($pods)->toHaveCount(1);
    expect($pods[0])->toBe($pod);
    expect($services)->toHaveCount(1);
    expect($services[0])->toBe($service);
});

it('can filter items by namespace', function (): void {
    $list = new KubernetesList();

    $pod1 = new Pod();
    $pod1->setName('pod-1')->setNamespace('default');

    $pod2 = new Pod();
    $pod2->setName('pod-2')->setNamespace('kube-system');

    $list->addItem($pod1)->addItem($pod2);

    $defaultPods = $list->filterByNamespace('default');
    $systemPods = $list->filterByNamespace('kube-system');

    expect($defaultPods)->toHaveCount(1);
    expect($defaultPods[0])->toBe($pod1);
    expect($systemPods)->toHaveCount(1);
    expect($systemPods[0])->toBe($pod2);
});

it('can convert to array', function (): void {
    $list = new KubernetesList();
    $list->setName('test-list');

    $pod = new Pod();
    $pod->setName('test-pod');
    $list->addItem($pod);

    $array = $list->toArray();

    expect($array)->toHaveKeys(['apiVersion', 'kind', 'metadata', 'items']);
    expect($array['apiVersion'])->toBe('v1');
    expect($array['kind'])->toBe('List');
    expect($array['metadata']['name'])->toBe('test-list');
    expect($array['items'])->toHaveCount(1);
    expect($array['items'][0]['kind'])->toBe('Pod');
});

it('can create from array', function (): void {
    $data = [
        'apiVersion' => 'v1',
        'kind'       => 'List',
        'metadata'   => ['name' => 'test-list'],
        'items'      => [], // Note: Full implementation would need ResourceFactory
    ];

    $list = KubernetesList::fromArray($data);

    expect($list->getApiVersion())->toBe('v1');
    expect($list->getKind())->toBe('List');
    expect($list->getName())->toBe('test-list');
    expect($list->isEmpty())->toBeTrue();
});

it('can set items with array', function (): void {
    $list = new KubernetesList();
    $pod1 = new Pod();
    $pod2 = new Pod();

    $items = [$pod1, $pod2];
    $list->setItems($items);

    expect($list->getItems())->toBe($items);
    expect($list->count())->toBe(2);
});

it('handles empty list operations', function (): void {
    $list = new KubernetesList();

    expect($list->isEmpty())->toBeTrue();
    expect($list->count())->toBe(0);
    expect($list->getItems())->toBeEmpty();
    expect($list->filterByKind('Pod'))->toBeEmpty();
    expect($list->filterByNamespace('default'))->toBeEmpty();
});
