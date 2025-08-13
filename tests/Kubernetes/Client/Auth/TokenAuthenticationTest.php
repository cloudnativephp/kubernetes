<?php

declare(strict_types=1);

namespace Tests\Kubernetes\Client\Auth;

use Kubernetes\Client\Auth\TokenAuthentication;
use Kubernetes\Exceptions\AuthenticationException;

it('can create token authentication', function (): void {
    $auth = new TokenAuthentication(
        'https://kubernetes.example.com',
        'test-token-123'
    );

    expect($auth->getServerUrl())->toBe('https://kubernetes.example.com');
    expect($auth->getToken())->toBe('test-token-123');
    expect($auth->shouldVerifySsl())->toBeTrue();
    expect($auth->isValid())->toBeTrue();
});

it('can get authentication headers', function (): void {
    $auth = new TokenAuthentication(
        'https://kubernetes.example.com',
        'test-token-123'
    );

    $headers = $auth->getHeaders();
    expect($headers)->toBe(['Authorization' => 'Bearer test-token-123']);
});

it('can set new token', function (): void {
    $auth = new TokenAuthentication(
        'https://kubernetes.example.com',
        'old-token'
    );

    $result = $auth->setToken('new-token');
    expect($result)->toBe($auth);
    expect($auth->getToken())->toBe('new-token');
    expect($auth->getHeaders())->toBe(['Authorization' => 'Bearer new-token']);
});

it('can set CA certificate', function (): void {
    $auth = new TokenAuthentication(
        'https://kubernetes.example.com',
        'test-token'
    );

    $caCert = '-----BEGIN CERTIFICATE-----\ntest-cert\n-----END CERTIFICATE-----';
    $result = $auth->setCaCertificate($caCert);

    expect($result)->toBe($auth);
    expect($auth->getCaCertificate())->toBe($caCert);
});

it('can set SSL verification', function (): void {
    $auth = new TokenAuthentication(
        'https://kubernetes.example.com',
        'test-token'
    );

    $result = $auth->setVerifySsl(false);
    expect($result)->toBe($auth);
    expect($auth->shouldVerifySsl())->toBeFalse();
});

it('returns null for client certificates', function (): void {
    $auth = new TokenAuthentication(
        'https://kubernetes.example.com',
        'test-token'
    );

    expect($auth->getClientCertificate())->toBeNull();
    expect($auth->getClientKey())->toBeNull();
});

it('can refresh authentication', function (): void {
    $auth = new TokenAuthentication(
        'https://kubernetes.example.com',
        'test-token'
    );

    expect($auth->refresh())->toBeTrue();
});

it('throws exception for empty server URL', function (): void {
    expect(fn () => new TokenAuthentication('', 'token'))
        ->toThrow(AuthenticationException::class, 'Server URL cannot be empty');
});

it('throws exception for empty token', function (): void {
    expect(fn () => new TokenAuthentication('https://example.com', ''))
        ->toThrow(AuthenticationException::class, 'Token cannot be empty');

    $auth = new TokenAuthentication('https://example.com', 'token');
    expect(fn () => $auth->setToken(''))
        ->toThrow(AuthenticationException::class, 'Token cannot be empty');
});
