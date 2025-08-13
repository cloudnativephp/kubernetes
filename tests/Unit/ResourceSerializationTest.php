<?php

declare(strict_types=1);

namespace Tests\Unit;

use Kubernetes\API\Core\V1\Pod;

/*
 * @property Pod $pod
 * @property array<string, mixed> $expectedData
 */
beforeEach(function (): void {
    $this->pod = new Pod();
    $this->pod
        ->setName('test-pod')
        ->setNamespace('default')
        ->setLabels(['app' => 'test'])
        ->setSpec([
            'containers' => [
                [
                    'name'  => 'nginx',
                    'image' => 'nginx:latest',
                ],
            ],
        ])
        ->setStatus([
            'phase' => 'Running',
        ]);

    $this->expectedData = [
        'apiVersion' => 'v1',
        'kind'       => 'Pod',
        'metadata'   => [
            'name'      => 'test-pod',
            'namespace' => 'default',
            'labels'    => ['app' => 'test'],
        ],
        'spec' => [
            'containers' => [
                [
                    'name'  => 'nginx',
                    'image' => 'nginx:latest',
                ],
            ],
        ],
        'status' => [
            'phase' => 'Running',
        ],
    ];
});

it('can convert resource to array', function (): void {
    $result = $this->pod->toArray();
    expect($result)->toBe($this->expectedData);
});

it('can convert resource to JSON', function (): void {
    $json = $this->pod->toJson(JSON_PRETTY_PRINT);

    expect($json)->toBeString();
    expect(json_decode($json, true))->toBe($this->expectedData);
});

it('can convert resource to YAML', function (): void {
    $yaml = $this->pod->toYaml();

    expect($yaml)->toBeString();
    expect($yaml)->toContain('apiVersion: v1');
    expect($yaml)->toContain('kind: Pod');
    expect($yaml)->toContain('name: test-pod');
});

it('can create resource from array', function (): void {
    $pod = Pod::fromArray($this->expectedData);

    expect($pod->getApiVersion())->toBe('v1');
    expect($pod->getKind())->toBe('Pod');
    expect($pod->getName())->toBe('test-pod');
    expect($pod->getNamespace())->toBe('default');
    expect($pod->getLabels())->toBe(['app' => 'test']);
    expect($pod->getSpec())->toBe($this->expectedData['spec']);
    expect($pod->getStatus())->toBe($this->expectedData['status']);
});

it('can create resource from JSON', function (): void {
    $json = json_encode($this->expectedData);
    $pod = Pod::fromJson($json);

    expect($pod->getName())->toBe('test-pod');
    expect($pod->getNamespace())->toBe('default');
    expect($pod->getSpec())->toBe($this->expectedData['spec']);
});

it('can create resource from YAML', function (): void {
    $yaml = <<<YAML
        apiVersion: v1
        kind: Pod
        metadata:
          name: test-pod
          namespace: default
          labels:
            app: test
        spec:
          containers:
            - name: nginx
              image: nginx:latest
        status:
          phase: Running
        YAML;

    $pod = Pod::fromYaml($yaml);

    expect($pod->getName())->toBe('test-pod');
    expect($pod->getNamespace())->toBe('default');
    expect($pod->getSpec())->toBe($this->expectedData['spec']);
    expect($pod->getStatus())->toBe($this->expectedData['status']);
});

it('throws exception for invalid JSON', function (): void {
    expect(fn () => Pod::fromJson('invalid json'))
        ->toThrow(JsonException::class);
});

it('throws exception for non-object JSON', function (): void {
    expect(fn () => Pod::fromJson('"string"'))
        ->toThrow(JsonException::class, 'Invalid JSON: expected object, got string');
});

it('throws exception for invalid YAML structure', function (): void {
    expect(fn () => Pod::fromYaml('"string"'))
        ->toThrow(InvalidArgumentException::class, 'Invalid YAML: expected object, got string');
});

it('can save and load resource from JSON file', function (): void {
    $tempFile = tempnam(sys_get_temp_dir(), 'test_pod') . '.json';

    try {
        // Save to file
        $result = $this->pod->toFile($tempFile);
        expect($result)->toBeTrue();
        expect(file_exists($tempFile))->toBeTrue();

        // Load from file
        $loadedPod = Pod::fromFile($tempFile);
        expect($loadedPod->getName())->toBe('test-pod');
        expect($loadedPod->getNamespace())->toBe('default');
        expect($loadedPod->getSpec())->toBe($this->expectedData['spec']);
    } finally {
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }
});

it('can save and load resource from YAML file', function (): void {
    $tempFile = tempnam(sys_get_temp_dir(), 'test_pod') . '.yaml';

    try {
        // Save to file
        $result = $this->pod->toFile($tempFile);
        expect($result)->toBeTrue();
        expect(file_exists($tempFile))->toBeTrue();

        // Load from file
        $loadedPod = Pod::fromFile($tempFile);
        expect($loadedPod->getName())->toBe('test-pod');
        expect($loadedPod->getNamespace())->toBe('default');
        expect($loadedPod->getSpec())->toBe($this->expectedData['spec']);
    } finally {
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }
});

it('throws exception when file does not exist', function (): void {
    expect(fn () => Pod::fromFile('/nonexistent/file.json'))
        ->toThrow(InvalidArgumentException::class, 'File not found: /nonexistent/file.json');
});

it('defaults to JSON format for unknown file extensions', function (): void {
    $tempFile = tempnam(sys_get_temp_dir(), 'test_pod') . '.txt';

    try {
        // Save to file (should use JSON format)
        $result = $this->pod->toFile($tempFile);
        expect($result)->toBeTrue();

        // Verify it's JSON content
        $content = file_get_contents($tempFile);
        $data = json_decode($content, true);
        expect($data)->not->toBeNull();
        expect($data['kind'])->toBe('Pod');

        // Load from file (should parse as JSON)
        $loadedPod = Pod::fromFile($tempFile);
        expect($loadedPod->getName())->toBe('test-pod');
    } finally {
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }
});

it('can handle resources without spec or status', function (): void {
    $pod = new Pod();
    $pod->setName('minimal-pod');

    $array = $pod->toArray();
    expect($array)->toHaveKey('apiVersion');
    expect($array)->toHaveKey('kind');
    expect($array)->toHaveKey('metadata');
    expect($array)->not->toHaveKey('spec');
    expect($array)->not->toHaveKey('status');

    $json = $pod->toJson();
    expect($json)->toBeString();

    $yaml = $pod->toYaml();
    expect($yaml)->toBeString();
});

it('preserves JSON encoding flags', function (): void {
    $jsonPretty = $this->pod->toJson(JSON_PRETTY_PRINT);
    $jsonCompact = $this->pod->toJson(0);

    expect($jsonPretty)->toContain("\n");
    expect($jsonCompact)->not->toContain("\n");
});

it('preserves YAML formatting options', function (): void {
    $yamlInline2 = $this->pod->toYaml(2);
    $yamlInline10 = $this->pod->toYaml(10);

    expect($yamlInline2)->toBeString();
    expect($yamlInline10)->toBeString();
    // Inline 2 should have more arrays on multiple lines
    // Inline 10 should have more compact formatting
});
