<?php

declare(strict_types=1);

use Kubernetes\Client\Http\GuzzleHttpClient;
use Kubernetes\Client\Http\HttpClientInterface;

it('can create a guzzle http client', function (): void {
    $client = new GuzzleHttpClient();
    expect($client)->toBeInstanceOf(HttpClientInterface::class);
});

it('can set base url', function (): void {
    $client = new GuzzleHttpClient();
    $result = $client->setBaseUrl('https://kubernetes.example.com');
    expect($result)->toBe($client);
});

it('can set default headers', function (): void {
    $client = new GuzzleHttpClient();
    $headers = ['Authorization' => 'Bearer token123'];
    $result = $client->setDefaultHeaders($headers);
    expect($result)->toBe($client);
});

it('can add default header', function (): void {
    $client = new GuzzleHttpClient();
    $result = $client->addDefaultHeader('X-Custom', 'value');
    expect($result)->toBe($client);
});

it('can set timeout', function (): void {
    $client = new GuzzleHttpClient();
    $result = $client->setTimeout(60);
    expect($result)->toBe($client);
});

it('can set ssl verification', function (): void {
    $client = new GuzzleHttpClient();
    $result = $client->setVerifySsl(true);
    expect($result)->toBe($client);
});

it('can chain configuration methods', function (): void {
    $client = new GuzzleHttpClient();
    $result = $client
        ->setBaseUrl('https://kubernetes.example.com')
        ->setTimeout(30)
        ->setVerifySsl(false)
        ->addDefaultHeader('Authorization', 'Bearer token');

    expect($result)->toBe($client);
});

// Note: These tests would require mocking Guzzle for actual HTTP calls
// In a real implementation, you'd mock the Guzzle client to test HTTP methods
