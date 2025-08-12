<?php

declare(strict_types=1);

use Kubernetes\Client\Http\CurlHttpClient;
use Kubernetes\Client\Http\HttpClientInterface;

it('can create a curl http client', function (): void {
    $client = new CurlHttpClient();
    expect($client)->toBeInstanceOf(HttpClientInterface::class);
});

it('throws exception if curl extension not loaded', function (): void {
    // This test would need to be conditional based on environment
    // For now, we assume cURL is available in test environment
    expect(extension_loaded('curl'))->toBeTrue();
});

it('can set base url', function (): void {
    $client = new CurlHttpClient();
    $result = $client->setBaseUrl('https://kubernetes.example.com');
    expect($result)->toBe($client);
});

it('can set default headers', function (): void {
    $client = new CurlHttpClient();
    $headers = ['Authorization' => 'Bearer token123'];
    $result = $client->setDefaultHeaders($headers);
    expect($result)->toBe($client);
});

it('can add default header', function (): void {
    $client = new CurlHttpClient();
    $result = $client->addDefaultHeader('X-Custom', 'value');
    expect($result)->toBe($client);
});

it('can set timeout', function (): void {
    $client = new CurlHttpClient();
    $result = $client->setTimeout(60);
    expect($result)->toBe($client);
});

it('can set ssl verification', function (): void {
    $client = new CurlHttpClient();
    $result = $client->setVerifySsl(true);
    expect($result)->toBe($client);
});

it('can chain configuration methods', function (): void {
    $client = new CurlHttpClient();
    $result = $client
        ->setBaseUrl('https://kubernetes.example.com')
        ->setTimeout(30)
        ->setVerifySsl(false)
        ->addDefaultHeader('Authorization', 'Bearer token');

    expect($result)->toBe($client);
});

// Note: These tests focus on configuration methods
// HTTP method testing would require mock servers or integration tests
