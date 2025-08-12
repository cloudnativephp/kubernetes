<?php

declare(strict_types=1);

use Kubernetes\API\ApiregistrationK8sIo\V1\APIService;

it('can create an APIService', function (): void {
    $apiService = new APIService();
    expect($apiService->getApiVersion())->toBe('apiregistration.k8s.io/v1');
    expect($apiService->getKind())->toBe('APIService');
});

it('can set and get group', function (): void {
    $apiService = new APIService();
    $apiService->setGroup('custom.example.com');
    expect($apiService->getGroup())->toBe('custom.example.com');
});

it('can set and get version', function (): void {
    $apiService = new APIService();
    $apiService->setVersion('v1alpha1');
    expect($apiService->getVersion())->toBe('v1alpha1');
});

it('can set and get group priority minimum', function (): void {
    $apiService = new APIService();
    $apiService->setGroupPriorityMinimum(1000);
    expect($apiService->getGroupPriorityMinimum())->toBe(1000);
});

it('can set and get version priority', function (): void {
    $apiService = new APIService();
    $apiService->setVersionPriority(15);
    expect($apiService->getVersionPriority())->toBe(15);
});

it('can set and get service reference', function (): void {
    $apiService = new APIService();
    $apiService->setService('custom-api', 'custom-system', 8443);

    $service = $apiService->getService();
    expect($service)->not->toBeNull();
    if ($service !== null) {
        expect($service['name'])->toBe('custom-api');
        expect($service['namespace'])->toBe('custom-system');
        expect($service['port'])->toBe(8443);
    }
});

it('can set and get CA bundle', function (): void {
    $apiService = new APIService();
    $caBundle = 'LS0tLS1CRUdJTi=='; // Base64 encoded data
    $apiService->setCaBundle($caBundle);
    expect($apiService->getCaBundle())->toBe($caBundle);
});

it('can set and get insecure skip TLS verify', function (): void {
    $apiService = new APIService();
    $apiService->setInsecureSkipTLSVerify(true);
    expect($apiService->getInsecureSkipTLSVerify())->toBe(true);

    $apiService->setInsecureSkipTLSVerify(false);
    expect($apiService->getInsecureSkipTLSVerify())->toBe(false);
});

it('can get status', function (): void {
    $apiService = new APIService();
    expect($apiService->getStatus())->toBeArray();
});

it('can get conditions', function (): void {
    $apiService = new APIService();
    expect($apiService->getConditions())->toBeArray();
});

it('can configure extension API', function (): void {
    $apiService = new APIService();
    $result = $apiService->configureExtensionAPI(
        'metrics.k8s.io',
        'v1beta1',
        'metrics-server',
        'kube-system',
        443
    );

    expect($result)->toBe($apiService);
    expect($apiService->getGroup())->toBe('metrics.k8s.io');
    expect($apiService->getVersion())->toBe('v1beta1');
    expect($apiService->getGroupPriorityMinimum())->toBe(1000);
    expect($apiService->getVersionPriority())->toBe(15);

    $service = $apiService->getService();
    expect($service)->not->toBeNull();
    if ($service !== null) {
        expect($service['name'])->toBe('metrics-server');
        expect($service['namespace'])->toBe('kube-system');
        expect($service['port'])->toBe(443);
    }
});

it('can chain setter methods', function (): void {
    $apiService = new APIService();
    $result = $apiService
        ->setName('custom-api-service')
        ->setGroup('custom.example.com')
        ->setVersion('v1')
        ->setGroupPriorityMinimum(1000)
        ->setVersionPriority(15);

    expect($result)->toBe($apiService);
});
