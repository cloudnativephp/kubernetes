<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\AuthenticationK8sIo\V1;

use Kubernetes\API\AuthenticationK8sIo\V1\TokenRequest;

it('can create a TokenRequest', function (): void {
    $tokenRequest = new TokenRequest();
    expect($tokenRequest->getApiVersion())->toBe('authentication.k8s.io/v1');
    expect($tokenRequest->getKind())->toBe('TokenRequest');
});

it('can set and get audiences', function (): void {
    $tokenRequest = new TokenRequest();
    $audiences = ['api', 'vault'];
    $tokenRequest->setAudiences($audiences);
    expect($tokenRequest->getAudiences())->toBe($audiences);
});

it('can add audience', function (): void {
    $tokenRequest = new TokenRequest();
    $tokenRequest->addAudience('api');
    $tokenRequest->addAudience('vault');
    expect($tokenRequest->getAudiences())->toBe(['api', 'vault']);
});

it('can set and get expiration seconds', function (): void {
    $tokenRequest = new TokenRequest();
    $tokenRequest->setExpirationSeconds(7200);
    expect($tokenRequest->getExpirationSeconds())->toBe(7200);
});

it('can set and get bound object reference', function (): void {
    $tokenRequest = new TokenRequest();
    $tokenRequest->setBoundObjectRef('Pod', 'my-pod', 'v1', 'uid-123');

    $boundRef = $tokenRequest->getBoundObjectRef();
    expect($boundRef)->not->toBeNull();
    if ($boundRef !== null) {
        expect($boundRef['kind'])->toBe('Pod');
        expect($boundRef['name'])->toBe('my-pod');
        expect($boundRef['apiVersion'])->toBe('v1');
        expect($boundRef['uid'])->toBe('uid-123');
    }
});

it('can set namespace', function (): void {
    $tokenRequest = new TokenRequest();
    $result = $tokenRequest->setNamespace('test-namespace');
    expect($result)->toBe($tokenRequest);
    expect($tokenRequest->getNamespace())->toBe('test-namespace');
});

it('can create simple token request', function (): void {
    $tokenRequest = new TokenRequest();
    $result = $tokenRequest->createSimpleTokenRequest(['api', 'vault'], 3600);

    expect($result)->toBe($tokenRequest);
    expect($tokenRequest->getAudiences())->toBe(['api', 'vault']);
    expect($tokenRequest->getExpirationSeconds())->toBe(3600);
});

it('can create pod-bound token request', function (): void {
    $tokenRequest = new TokenRequest();
    $result = $tokenRequest->createPodBoundTokenRequest('my-pod', ['api'], 1800);

    expect($result)->toBe($tokenRequest);
    expect($tokenRequest->getAudiences())->toBe(['api']);
    expect($tokenRequest->getExpirationSeconds())->toBe(1800);

    $boundRef = $tokenRequest->getBoundObjectRef();
    expect($boundRef)->not->toBeNull();
    if ($boundRef !== null) {
        expect($boundRef['kind'])->toBe('Pod');
        expect($boundRef['name'])->toBe('my-pod');
        expect($boundRef['apiVersion'])->toBe('v1');
    }
});

it('can chain setter methods', function (): void {
    $tokenRequest = new TokenRequest();
    $result = $tokenRequest
        ->setName('my-token-request')
        ->setNamespace('default')
        ->setAudiences(['api'])
        ->setExpirationSeconds(3600);

    expect($result)->toBe($tokenRequest);
});
