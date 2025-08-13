<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\EventsK8sIo\V1;

use Kubernetes\API\EventsK8sIo\V1\Event;

it('can create an Event', function (): void {
    $event = new Event();
    expect($event->getApiVersion())->toBe('events.k8s.io/v1');
    expect($event->getKind())->toBe('Event');
});

it('can set namespace', function (): void {
    $event = new Event();
    $result = $event->setNamespace('kube-system');
    expect($result)->toBe($event);
    expect($event->getNamespace())->toBe('kube-system');
});

it('can set and get event time', function (): void {
    $event = new Event();
    $time = '2025-08-12T12:00:00Z';
    $event->setEventTime($time);
    expect($event->getEventTime())->toBe($time);
});

it('can set and get series', function (): void {
    $event = new Event();
    $event->setSeries(5, '2025-08-12T12:05:00Z');

    $series = $event->getSeries();
    expect($series)->not->toBeNull();
    if ($series !== null) {
        expect($series['count'])->toBe(5);
        expect($series['lastObservedTime'])->toBe('2025-08-12T12:05:00Z');
    }
});

it('can set and get reporting controller', function (): void {
    $event = new Event();
    $event->setReportingController('deployment-controller', 'controller-instance-1');

    expect($event->getReportingController())->toBe('deployment-controller');
    expect($event->getReportingInstance())->toBe('controller-instance-1');
});

it('can set and get action', function (): void {
    $event = new Event();
    $event->setAction('ScalingReplicaSet');
    expect($event->getAction())->toBe('ScalingReplicaSet');
});

it('can set and get reason', function (): void {
    $event = new Event();
    $event->setReason('SuccessfulCreate');
    expect($event->getReason())->toBe('SuccessfulCreate');
});

it('can set and get regarding object', function (): void {
    $event = new Event();
    $event->setRegarding('apps/v1', 'Deployment', 'web-app', 'production', 'uid-123');

    $regarding = $event->getRegarding();
    expect($regarding)->not->toBeNull();
    if ($regarding !== null) {
        expect($regarding['apiVersion'])->toBe('apps/v1');
        expect($regarding['kind'])->toBe('Deployment');
        expect($regarding['name'])->toBe('web-app');
        expect($regarding['namespace'])->toBe('production');
        expect($regarding['uid'])->toBe('uid-123');
    }
});

it('can set and get related object', function (): void {
    $event = new Event();
    $event->setRelated('apps/v1', 'ReplicaSet', 'web-app-abc123', 'production', 'uid-456');

    $related = $event->getRelated();
    expect($related)->not->toBeNull();
    if ($related !== null) {
        expect($related['apiVersion'])->toBe('apps/v1');
        expect($related['kind'])->toBe('ReplicaSet');
        expect($related['name'])->toBe('web-app-abc123');
        expect($related['namespace'])->toBe('production');
        expect($related['uid'])->toBe('uid-456');
    }
});

it('can set and get note', function (): void {
    $event = new Event();
    $note = 'Scaled up replica set web-app-abc123 to 3';
    $event->setNote($note);
    expect($event->getNote())->toBe($note);
});

it('can set and get type', function (): void {
    $event = new Event();
    $event->setType('Normal');
    expect($event->getType())->toBe('Normal');

    $event->setType('Warning');
    expect($event->getType())->toBe('Warning');
});

it('can create normal event', function (): void {
    $event = new Event();
    $result = $event->createNormalEvent(
        'SuccessfulCreate',
        'Created pod: web-app-pod-1',
        'Create',
        'deployment-controller'
    );

    expect($result)->toBe($event);
    expect($event->getType())->toBe('Normal');
    expect($event->getReason())->toBe('SuccessfulCreate');
    expect($event->getNote())->toBe('Created pod: web-app-pod-1');
    expect($event->getAction())->toBe('Create');
    expect($event->getReportingController())->toBe('deployment-controller');
    expect($event->getEventTime())->not->toBeNull();
});

it('can create warning event', function (): void {
    $event = new Event();
    $result = $event->createWarningEvent(
        'FailedScheduling',
        'Pod cannot be scheduled: insufficient CPU',
        'Schedule',
        'default-scheduler'
    );

    expect($result)->toBe($event);
    expect($event->getType())->toBe('Warning');
    expect($event->getReason())->toBe('FailedScheduling');
    expect($event->getNote())->toBe('Pod cannot be scheduled: insufficient CPU');
    expect($event->getAction())->toBe('Schedule');
    expect($event->getReportingController())->toBe('default-scheduler');
    expect($event->getEventTime())->not->toBeNull();
});

it('can create pod event', function (): void {
    $event = new Event();
    $result = $event->createPodEvent(
        'web-app-pod-1',
        'pod-uid-123',
        'production',
        'Started',
        'Container started successfully',
        'Normal'
    );

    expect($result)->toBe($event);
    expect($event->getNamespace())->toBe('production');
    expect($event->getType())->toBe('Normal');
    expect($event->getReason())->toBe('Started');
    expect($event->getNote())->toBe('Container started successfully');

    $regarding = $event->getRegarding();
    expect($regarding)->not->toBeNull();
    if ($regarding !== null) {
        expect($regarding['apiVersion'])->toBe('v1');
        expect($regarding['kind'])->toBe('Pod');
        expect($regarding['name'])->toBe('web-app-pod-1');
        expect($regarding['uid'])->toBe('pod-uid-123');
    }
});

it('can create deployment event', function (): void {
    $event = new Event();
    $result = $event->createDeploymentEvent(
        'web-app',
        'deployment-uid-456',
        'production',
        'ScalingReplicaSet',
        'Scaled up replica set to 5 replicas',
        'Normal'
    );

    expect($result)->toBe($event);
    expect($event->getNamespace())->toBe('production');
    expect($event->getType())->toBe('Normal');
    expect($event->getReason())->toBe('ScalingReplicaSet');
    expect($event->getAction())->toBe('Update');
    expect($event->getReportingController())->toBe('deployment-controller');

    $regarding = $event->getRegarding();
    expect($regarding)->not->toBeNull();
    if ($regarding !== null) {
        expect($regarding['apiVersion'])->toBe('apps/v1');
        expect($regarding['kind'])->toBe('Deployment');
        expect($regarding['name'])->toBe('web-app');
        expect($regarding['uid'])->toBe('deployment-uid-456');
    }
});

it('can check if event is warning', function (): void {
    $event = new Event();
    expect($event->isWarning())->toBe(false);

    $event->setType('Warning');
    expect($event->isWarning())->toBe(true);
    expect($event->isNormal())->toBe(false);
});

it('can check if event is normal', function (): void {
    $event = new Event();
    expect($event->isNormal())->toBe(false);

    $event->setType('Normal');
    expect($event->isNormal())->toBe(true);
    expect($event->isWarning())->toBe(false);
});

it('can set deprecation warning', function (): void {
    $event = new Event();
    $result = $event->setDeprecatedSourceComponent('extensions/v1beta1/Deployment', 'API deprecated');
    expect($result)->toBe($event);
});

it('can chain setter methods', function (): void {
    $event = new Event();
    $result = $event
        ->setName('test-event')
        ->setNamespace('default')
        ->setType('Normal')
        ->setReason('Test')
        ->setNote('Test event')
        ->setAction('Test');

    expect($result)->toBe($event);
});
