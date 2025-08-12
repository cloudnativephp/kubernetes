<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API\Core\V1;

use Kubernetes\API\Core\V1\ConfigMap;

it('can create a configmap with basic configuration', function (): void {
    $configMap = new ConfigMap();

    expect($configMap->getApiVersion())->toBe('v1');
    expect($configMap->getKind())->toBe('ConfigMap');
    expect($configMap->getMetadata())->toBe([]);
    expect($configMap->getData())->toBe([]);
});

it('can set and get configmap data', function (): void {
    $configMap = new ConfigMap();
    $data = [
        'database.host' => 'localhost',
        'database.port' => '5432',
    ];

    $configMap->setData($data);

    expect($configMap->getData())->toBe($data);
});

it('can add individual data entries', function (): void {
    $configMap = new ConfigMap();

    $configMap->addData('app.name', 'my-app')
        ->addData('app.version', '1.0.0');

    expect($configMap->getData())->toBe([
        'app.name'    => 'my-app',
        'app.version' => '1.0.0',
    ]);
});

it('can set and get binary data', function (): void {
    $configMap = new ConfigMap();
    $binaryData = [
        'binary.data' => base64_encode('binary content'),
    ];

    $configMap->setBinaryData($binaryData);

    expect($configMap->getBinaryData())->toBe($binaryData);
});

it('can set immutable flag', function (): void {
    $configMap = new ConfigMap();

    expect($configMap->isImmutable())->toBeFalse();

    $configMap->setImmutable(true);

    expect($configMap->isImmutable())->toBeTrue();
});

it('can convert configmap to array', function (): void {
    $configMap = new ConfigMap();
    $configMap->setName('app-config')
        ->setNamespace('default')
        ->setData(['key' => 'value'])
        ->setImmutable(true);

    $array = $configMap->toArray();

    expect($array)->toHaveKey('apiVersion', 'v1');
    expect($array)->toHaveKey('kind', 'ConfigMap');
    expect($array)->toHaveKey('metadata.name', 'app-config');
    expect($array)->toHaveKey('spec.data.key', 'value');
    expect($array)->toHaveKey('spec.immutable', true);
});
