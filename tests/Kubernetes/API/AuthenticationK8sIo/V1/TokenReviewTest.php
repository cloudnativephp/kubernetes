<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\AuthenticationK8sIo\V1;

use Kubernetes\API\AuthenticationK8sIo\V1\TokenReview;

it('can create a TokenReview', function (): void {
    $tokenReview = new TokenReview();
    expect($tokenReview->getApiVersion())->toBe('authentication.k8s.io/v1');
    expect($tokenReview->getKind())->toBe('TokenReview');
});

it('can set and get token', function (): void {
    $tokenReview = new TokenReview();
    $token = 'eyJhbGciOiJSUzI1NiIs...';
    $tokenReview->setToken($token);
    expect($tokenReview->getToken())->toBe($token);
});

it('can set and get audiences', function (): void {
    $tokenReview = new TokenReview();
    $audiences = ['api', 'vault'];
    $tokenReview->setAudiences($audiences);
    expect($tokenReview->getAudiences())->toBe($audiences);
});

it('can add audience', function (): void {
    $tokenReview = new TokenReview();
    $tokenReview->addAudience('api');
    $tokenReview->addAudience('vault');
    expect($tokenReview->getAudiences())->toBe(['api', 'vault']);
});

it('can check if authenticated', function (): void {
    $tokenReview = new TokenReview();
    expect($tokenReview->isAuthenticated())->toBe(false);
});

it('can get user information', function (): void {
    $tokenReview = new TokenReview();
    expect($tokenReview->getUser())->toBeNull();
    expect($tokenReview->getUsername())->toBeNull();
    expect($tokenReview->getUserUid())->toBeNull();
    expect($tokenReview->getUserGroups())->toBe([]);
    expect($tokenReview->getUserExtra())->toBe([]);
});

it('can get error message', function (): void {
    $tokenReview = new TokenReview();
    expect($tokenReview->getError())->toBeNull();
});

it('can get validated audiences', function (): void {
    $tokenReview = new TokenReview();
    expect($tokenReview->getValidatedAudiences())->toBe([]);
});

it('can create token review', function (): void {
    $tokenReview = new TokenReview();
    $token = 'eyJhbGciOiJSUzI1NiIs...';
    $audiences = ['api', 'vault'];

    $result = $tokenReview->createTokenReview($token, $audiences);

    expect($result)->toBe($tokenReview);
    expect($tokenReview->getToken())->toBe($token);
    expect($tokenReview->getAudiences())->toBe($audiences);
});

it('can check user group membership', function (): void {
    $tokenReview = new TokenReview();
    expect($tokenReview->userHasGroup('admin'))->toBe(false);
    expect($tokenReview->userHasAnyGroup(['admin', 'developer']))->toBe(false);
});

it('can chain setter methods', function (): void {
    $tokenReview = new TokenReview();
    $result = $tokenReview
        ->setName('my-token-review')
        ->setToken('eyJhbGciOiJSUzI1NiIs...')
        ->setAudiences(['api']);

    expect($result)->toBe($tokenReview);
});
