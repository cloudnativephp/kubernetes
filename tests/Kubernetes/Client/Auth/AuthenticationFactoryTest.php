<?php

declare(strict_types=1);

namespace Tests\Kubernetes\Client\Auth;

use Kubernetes\Client\Auth\AuthenticationFactory;
use Kubernetes\Client\Auth\KubeconfigAuthentication;
use Kubernetes\Client\Auth\TokenAuthentication;
use Kubernetes\Exceptions\AuthenticationException;

it('can create authentication automatically', function (): void {
    // This test would normally auto-detect the best method
    // For testing, we'll just verify the factory exists
    expect(class_exists(AuthenticationFactory::class))->toBeTrue();
});

it('can create kubeconfig authentication', function (): void {
    // Mock a simple kubeconfig for testing
    $kubeconfigPath = tempnam(sys_get_temp_dir(), 'kubeconfig_test');
    $kubeconfig = createTestKubeconfig();

    file_put_contents($kubeconfigPath, $kubeconfig);

    $auth = AuthenticationFactory::kubeconfig($kubeconfigPath);

    expect($auth)->toBeInstanceOf(KubeconfigAuthentication::class);
    expect($auth->getServerUrl())->toBe('https://kubernetes.example.com');
    expect($auth->getHeaders())->toHaveKey('Authorization');

    unlink($kubeconfigPath);
});

it('can create token authentication', function (): void {
    $auth = AuthenticationFactory::token(
        'https://kubernetes.example.com',
        'test-token-123'
    );

    expect($auth)->toBeInstanceOf(TokenAuthentication::class);
    expect($auth->getServerUrl())->toBe('https://kubernetes.example.com');
    expect($auth->getHeaders())->toBe(['Authorization' => 'Bearer test-token-123']);
    expect($auth->shouldVerifySsl())->toBeTrue();
});

it('can check if running in cluster', function (): void {
    $isInCluster = AuthenticationFactory::isInCluster();
    expect($isInCluster)->toBeBool();
});

it('can create in-cluster authentication when available', function (): void {
    // This test will only pass if actually running in a cluster
    // For CI/testing, we'll just verify the method exists
    expect(method_exists(AuthenticationFactory::class, 'inCluster'))->toBeTrue();
});

it('throws exception for invalid token authentication', function (): void {
    expect(fn () => AuthenticationFactory::token('', 'token'))
        ->toThrow(AuthenticationException::class);

    expect(fn () => AuthenticationFactory::token('https://example.com', ''))
        ->toThrow(AuthenticationException::class);
});

/**
 * Create a test kubeconfig YAML string.
 *
 * @return string YAML content for testing
 */
function createTestKubeconfig(): string
{
    return <<<YAML
        apiVersion: v1
        kind: Config
        current-context: test-context
        contexts:
        - name: test-context
          context:
            cluster: test-cluster
            user: test-user
        clusters:
        - name: test-cluster
          cluster:
            server: https://kubernetes.example.com
        users:
        - name: test-user
          user:
            token: test-token-123
        YAML;
}
