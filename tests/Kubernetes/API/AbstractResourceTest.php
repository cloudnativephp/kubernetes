<?php

declare(strict_types=1);

namespace Tests\Kubernetes\API;

use Exception;
use InvalidArgumentException;
use JsonException;
use Kubernetes\API\AbstractResource;
use Kubernetes\Contracts\ClientInterface;
use Kubernetes\Exceptions\ResourceNotFoundException;
use Kubernetes\Tests\TestCase;
use Kubernetes\Traits\IsNamespacedResource;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use ReflectionMethod;

/**
 * Test implementation of AbstractResource for testing purposes.
 */
class TestResource extends AbstractResource
{
    public function getApiVersion(): string
    {
        return 'v1';
    }

    public function getKind(): string
    {
        return 'TestResource';
    }
}

/**
 * Test implementation of AbstractResource with IsNamespacedResource trait for testing purposes.
 */
class TestNamespacedResource extends AbstractResource
{
    use IsNamespacedResource;

    public function getApiVersion(): string
    {
        return 'v1';
    }

    public function getKind(): string
    {
        return 'TestNamespacedResource';
    }
}

class AbstractResourceTest extends TestCase
{
    private ClientInterface&MockObject $mockClient;
    private TestResource $resource;
    private TestNamespacedResource $namespacedResource;

    public function testCanSetAndGetDefaultClient(): void
    {
        $client = $this->createMock(ClientInterface::class);

        AbstractResource::setDefaultClient($client);

        $this->assertSame($client, AbstractResource::getDefaultClient());
    }

    public function testReturnsNullWhenNoDefaultClientIsSet(): void
    {
        // Clear any existing default client
        $reflection = new ReflectionClass(AbstractResource::class);
        $property = $reflection->getProperty('defaultClient');
        $property->setAccessible(true);
        $property->setValue(null, null);

        $this->assertNull(AbstractResource::getDefaultClient());
    }

    // Test static client management

    public function testCanConvertToArrayWithMinimalMetadata(): void
    {
        $this->resource->setName('test-resource');

        $array = $this->resource->toArray();

        $this->assertArrayHasKey('apiVersion', $array);
        $this->assertArrayHasKey('kind', $array);
        $this->assertArrayHasKey('metadata', $array);
        $this->assertEquals('v1', $array['apiVersion']);
        $this->assertEquals('TestResource', $array['kind']);
        $this->assertEquals('test-resource', $array['metadata']['name']);
    }

    public function testCanConvertToArrayWithSpecAndStatus(): void
    {
        $spec = ['replicas' => 3, 'selector' => ['app' => 'test']];
        $status = ['readyReplicas' => 2];

        $this->resource
            ->setName('test-resource')
            ->setSpec($spec)
            ->setStatus($status);

        $array = $this->resource->toArray();

        $this->assertArrayHasKey('spec', $array);
        $this->assertArrayHasKey('status', $array);
        $this->assertEquals($spec, $array['spec']);
        $this->assertEquals($status, $array['status']);
    }

    // Test array conversion

    public function testExcludesEmptySpecAndStatusFromArray(): void
    {
        $this->resource->setName('test-resource');

        $array = $this->resource->toArray();

        $this->assertArrayNotHasKey('spec', $array);
        $this->assertArrayNotHasKey('status', $array);
    }

    public function testCanConvertToJson(): void
    {
        $this->resource->setName('test-resource');

        $json = $this->resource->toJson();
        $decoded = json_decode($json, true);

        $this->assertIsArray($decoded);
        $this->assertEquals('v1', $decoded['apiVersion']);
        $this->assertEquals('TestResource', $decoded['kind']);
        $this->assertEquals('test-resource', $decoded['metadata']['name']);
    }

    public function testCanConvertToJsonWithCustomFlags(): void
    {
        $this->resource->setName('test-resource');

        $json = $this->resource->toJson(JSON_PRETTY_PRINT);

        $this->assertStringContainsString("\n", $json);
        $this->assertStringContainsString('    ', $json);
    }

    // Test JSON conversion

    public function testCanConvertToYaml(): void
    {
        $this->resource->setName('test-resource');

        $yaml = $this->resource->toYaml();

        $this->assertIsString($yaml);
        $this->assertStringContainsString('apiVersion: v1', $yaml);
        $this->assertStringContainsString('kind: TestResource', $yaml);
        $this->assertStringContainsString('name: test-resource', $yaml);
    }

    public function testCanConvertToYamlWithCustomFormatting(): void
    {
        $this->resource->setName('test-resource')
            ->setSpec(['nested' => ['data' => 'value']]);

        $yaml = $this->resource->toYaml(2, 4);

        $this->assertIsString($yaml);
        $this->assertStringContainsString('    ', $yaml); // Should have 4-space indentation
    }

    // Test YAML conversion

    public function testCanCreateFromArrayWithMinimalData(): void
    {
        $data = [
            'apiVersion' => 'v1',
            'kind'       => 'TestResource',
            'metadata'   => ['name' => 'test-resource'],
        ];

        $resource = TestResource::fromArray($data);

        $this->assertInstanceOf(TestResource::class, $resource);
        $this->assertEquals('test-resource', $resource->getName());
        $this->assertEquals([], $resource->getSpec());
        $this->assertEquals([], $resource->getStatus());
    }

    public function testCanCreateFromArrayWithSpecAndStatus(): void
    {
        $data = [
            'apiVersion' => 'v1',
            'kind'       => 'TestResource',
            'metadata'   => ['name' => 'test-resource'],
            'spec'       => ['replicas' => 3],
            'status'     => ['readyReplicas' => 2],
        ];

        $resource = TestResource::fromArray($data);

        $this->assertEquals('test-resource', $resource->getName());
        $this->assertEquals(['replicas' => 3], $resource->getSpec());
        $this->assertEquals(['readyReplicas' => 2], $resource->getStatus());
    }

    // Test creation from array

    public function testCanCreateFromArrayWithEmptyMetadata(): void
    {
        $data = [
            'apiVersion' => 'v1',
            'kind'       => 'TestResource',
        ];

        $resource = TestResource::fromArray($data);

        $this->assertInstanceOf(TestResource::class, $resource);
        $this->assertEquals([], $resource->getMetadata());
    }

    public function testCanCreateFromJsonString(): void
    {
        $json = '{"apiVersion":"v1","kind":"TestResource","metadata":{"name":"test-resource"}}';

        $resource = TestResource::fromJson($json);

        $this->assertInstanceOf(TestResource::class, $resource);
        $this->assertEquals('test-resource', $resource->getName());
    }

    public function testCanCreateFromYamlString(): void
    {
        $yaml = "apiVersion: v1\nkind: TestResource\nmetadata:\n  name: test-resource";

        $resource = TestResource::fromYaml($yaml);

        $this->assertInstanceOf(TestResource::class, $resource);
        $this->assertEquals('test-resource', $resource->getName());
    }

    // Test creation from JSON

    public function testCanCreateFromJsonFile(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_resource') . '.json';
        $data = [
            'apiVersion' => 'v1',
            'kind'       => 'TestResource',
            'metadata'   => ['name' => 'test-resource'],
        ];
        file_put_contents($tempFile, json_encode($data));

        try {
            $resource = TestResource::fromFile($tempFile);

            $this->assertInstanceOf(TestResource::class, $resource);
            $this->assertEquals('test-resource', $resource->getName());
        } finally {
            unlink($tempFile);
        }
    }

    // Test creation from YAML

    public function testCanCreateFromYamlFile(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_resource') . '.yaml';
        $yaml = "apiVersion: v1\nkind: TestResource\nmetadata:\n  name: test-resource";
        file_put_contents($tempFile, $yaml);

        try {
            $resource = TestResource::fromFile($tempFile);

            $this->assertInstanceOf(TestResource::class, $resource);
            $this->assertEquals('test-resource', $resource->getName());
        } finally {
            unlink($tempFile);
        }
    }

    // Test file operations

    public function testCanCreateFromYmlFile(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_resource') . '.yml';
        $yaml = "apiVersion: v1\nkind: TestResource\nmetadata:\n  name: test-resource";
        file_put_contents($tempFile, $yaml);

        try {
            $resource = TestResource::fromFile($tempFile);

            $this->assertInstanceOf(TestResource::class, $resource);
            $this->assertEquals('test-resource', $resource->getName());
        } finally {
            unlink($tempFile);
        }
    }

    public function testDefaultsToJsonForUnknownFileExtension(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_resource') . '.txt';
        $data = [
            'apiVersion' => 'v1',
            'kind'       => 'TestResource',
            'metadata'   => ['name' => 'test-resource'],
        ];
        file_put_contents($tempFile, json_encode($data));

        try {
            $resource = TestResource::fromFile($tempFile);

            $this->assertInstanceOf(TestResource::class, $resource);
            $this->assertEquals('test-resource', $resource->getName());
        } finally {
            unlink($tempFile);
        }
    }

    public function testThrowsExceptionForNonExistentFile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('File not found: /non/existent/file.json');

        TestResource::fromFile('/non/existent/file.json');
    }

    public function testCanSaveToJsonFile(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_resource') . '.json';
        $this->resource->setName('test-resource');

        try {
            $result = $this->resource->toFile($tempFile);

            $this->assertTrue($result);
            $this->assertFileExists($tempFile);

            $content = file_get_contents($tempFile);
            $decoded = json_decode($content, true);
            $this->assertEquals('test-resource', $decoded['metadata']['name']);
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function testCanSaveToYamlFile(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_resource') . '.yaml';
        $this->resource->setName('test-resource');

        try {
            $result = $this->resource->toFile($tempFile);

            $this->assertTrue($result);
            $this->assertFileExists($tempFile);

            $content = file_get_contents($tempFile);
            $this->assertStringContainsString('name: test-resource', $content);
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    // Test saving to file

    public function testCanSaveWithCustomJsonFlags(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_resource') . '.json';
        $this->resource->setName('test-resource');

        try {
            $result = $this->resource->toFile($tempFile, JSON_PRETTY_PRINT);

            $this->assertTrue($result);

            $content = file_get_contents($tempFile);
            $this->assertStringContainsString("\n", $content); // Pretty print should include newlines
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function testCanGetAndSetSpec(): void
    {
        $spec = ['replicas' => 3, 'selector' => ['app' => 'test']];

        $result = $this->resource->setSpec($spec);

        $this->assertSame($this->resource, $result); // Method chaining
        $this->assertEquals($spec, $this->resource->getSpec());
    }

    public function testCanGetAndSetStatus(): void
    {
        $status = ['readyReplicas' => 2, 'phase' => 'Running'];

        $result = $this->resource->setStatus($status);

        $this->assertSame($this->resource, $result); // Method chaining
        $this->assertEquals($status, $this->resource->getStatus());
    }

    // Test spec and status management

    public function testReturnsEmptyArrayForUnsetSpec(): void
    {
        $this->assertEquals([], $this->resource->getSpec());
    }

    public function testReturnsEmptyArrayForUnsetStatus(): void
    {
        $this->assertEquals([], $this->resource->getStatus());
    }

    public function testIdentifiesNewResourceWithoutResourceVersion(): void
    {
        $this->resource->setName('test-resource');

        $this->assertTrue($this->resource->isNewResource());
    }

    public function testIdentifiesExistingResourceWithResourceVersion(): void
    {
        $this->resource->setName('test-resource')
            ->setMetadata(['name' => 'test-resource', 'resourceVersion' => '12345']);

        $this->assertFalse($this->resource->isNewResource());
    }

    // Test new resource detection

    public function testIdentifiesNewResourceWithEmptyResourceVersion(): void
    {
        $this->resource->setName('test-resource')
            ->setMetadata(['name' => 'test-resource', 'resourceVersion' => '']);

        $this->assertTrue($this->resource->isNewResource());
    }

    public function testCanSaveNewResourceUsingCreate(): void
    {
        $this->resource->setName('test-resource');

        $createdResource = new TestResource();
        $createdResource->setName('test-resource')
            ->setMetadata(['name' => 'test-resource', 'resourceVersion' => '12345'])
            ->setStatus(['phase' => 'Active']);

        $this->mockClient->expects($this->once())
            ->method('create')
            ->with($this->resource)
            ->willReturn($createdResource);

        $result = $this->resource->save();

        $this->assertSame($this->resource, $result);
        $this->assertEquals('12345', $this->resource->getMetadata()['resourceVersion']);
        $this->assertEquals('Active', $this->resource->getStatus()['phase']);
    }

    public function testCanSaveExistingResourceUsingUpdate(): void
    {
        $this->resource->setName('test-resource')
            ->setMetadata(['name' => 'test-resource', 'resourceVersion' => '12345']);

        $updatedResource = new TestResource();
        $updatedResource->setName('test-resource')
            ->setMetadata(['name' => 'test-resource', 'resourceVersion' => '12346'])
            ->setStatus(['phase' => 'Updated']);

        $this->mockClient->expects($this->once())
            ->method('update')
            ->with($this->resource)
            ->willReturn($updatedResource);

        $result = $this->resource->save();

        $this->assertSame($this->resource, $result);
        $this->assertEquals('12346', $this->resource->getMetadata()['resourceVersion']);
        $this->assertEquals('Updated', $this->resource->getStatus()['phase']);
    }

    // Test Kubernetes cluster operations - save

    public function testFallsBackToUpdateWhenCreateFailsWithConflict(): void
    {
        $this->resource->setName('test-resource');

        $updatedResource = new TestResource();
        $updatedResource->setName('test-resource')
            ->setMetadata(['name' => 'test-resource', 'resourceVersion' => '12345']);

        $this->mockClient->expects($this->once())
            ->method('create')
            ->with($this->resource)
            ->willThrowException(new Exception('Resource already exists'));

        $this->mockClient->expects($this->once())
            ->method('update')
            ->with($this->resource)
            ->willReturn($updatedResource);

        $result = $this->resource->save();

        $this->assertSame($this->resource, $result);
        $this->assertEquals('12345', $this->resource->getMetadata()['resourceVersion']);
    }

    public function testThrowsExceptionWhenSaveFailsWithNoClient(): void
    {
        // Clear the default client
        $reflection = new ReflectionClass(AbstractResource::class);
        $property = $reflection->getProperty('defaultClient');
        $property->setAccessible(true);
        $property->setValue(null, null);

        $this->resource->setName('test-resource');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No Kubernetes client available');

        $this->resource->save();
    }

    public function testThrowsExceptionWhenSaveFailsWithNoName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Resource name must be set before saving');

        $this->resource->save();
    }

    public function testCanRefreshResourceFromCluster(): void
    {
        $this->resource->setName('test-resource');

        $refreshedResource = new TestResource();
        $refreshedResource->setName('test-resource')
            ->setMetadata(['name' => 'test-resource', 'resourceVersion' => '12346'])
            ->setSpec(['replicas' => 5])
            ->setStatus(['readyReplicas' => 5]);

        $this->mockClient->expects($this->once())
            ->method('read')
            ->willReturn($refreshedResource);

        $result = $this->resource->refresh();

        $this->assertSame($this->resource, $result);
        $this->assertEquals('12346', $this->resource->getMetadata()['resourceVersion']);
        $this->assertEquals(5, $this->resource->getSpec()['replicas']);
        $this->assertEquals(5, $this->resource->getStatus()['readyReplicas']);
    }

    public function testThrowsExceptionWhenRefreshFailsWithNoName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Resource name must be set before refreshing');

        $this->resource->refresh();
    }

    // Test refresh operation

    public function testCanDeleteResourceFromCluster(): void
    {
        $this->resource->setName('test-resource');

        $this->mockClient->expects($this->once())
            ->method('delete')
            ->with($this->resource)
            ->willReturn(true);

        $result = $this->resource->delete();

        $this->assertTrue($result);
    }

    public function testThrowsExceptionWhenDeleteFailsWithNoName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Resource name must be set before deleting');

        $this->resource->delete();
    }

    // Test delete operation

    public function testReturnsTrueWhenResourceExistsInCluster(): void
    {
        $this->resource->setName('test-resource');

        $existingResource = new TestResource();
        $existingResource->setName('test-resource');

        $this->mockClient->expects($this->once())
            ->method('read')
            ->willReturn($existingResource);

        $result = $this->resource->exists();

        $this->assertTrue($result);
    }

    public function testReturnsFalseWhenResourceDoesNotExistInCluster(): void
    {
        $this->resource->setName('test-resource');

        $this->mockClient->expects($this->once())
            ->method('read')
            ->willThrowException(new ResourceNotFoundException('TestResource', 'test-resource'));

        $result = $this->resource->exists();

        $this->assertFalse($result);
    }

    // Test exists operation

    public function testCanGetResourceByName(): void
    {
        $resource = new TestResource();
        $resource->setName('test-resource');

        $this->mockClient->expects($this->once())
            ->method('read')
            ->willReturn($resource);

        $result = TestResource::get('test-resource');

        $this->assertInstanceOf(TestResource::class, $result);
        $this->assertEquals('test-resource', $result->getName());
    }

    public function testCanGetNamespacedResourceByNameAndNamespace(): void
    {
        $resource = new TestNamespacedResource();
        $resource->setName('test-resource')->setNamespace('test-namespace');

        $this->mockClient->expects($this->once())
            ->method('read')
            ->willReturn($resource);

        $result = TestNamespacedResource::get('test-resource', 'test-namespace');

        $this->assertInstanceOf(TestNamespacedResource::class, $result);
        $this->assertEquals('test-resource', $result->getName());
    }

    // Test static get operation

    public function testThrowsExceptionWhenGetFailsWithEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Resource name cannot be empty');

        TestResource::get('');
    }

    public function testCanGetMultipleResourcesByNames(): void
    {
        $resource1 = new TestResource();
        $resource1->setName('test-resource-1');
        $resource2 = new TestResource();
        $resource2->setName('test-resource-2');

        $this->mockClient->expects($this->exactly(2))
            ->method('read')
            ->willReturnOnConsecutiveCalls($resource1, $resource2);

        $result = TestResource::getMany(['test-resource-1', 'test-resource-2']);

        $this->assertCount(2, $result);
        $this->assertInstanceOf(TestResource::class, $result['test-resource-1']);
        $this->assertInstanceOf(TestResource::class, $result['test-resource-2']);
    }

    public function testSkipsNonExistentResourcesInGetMany(): void
    {
        $resource1 = new TestResource();
        $resource1->setName('test-resource-1');

        $this->mockClient->expects($this->exactly(2))
            ->method('read')
            ->willReturnCallback(function ($template) use ($resource1) {
                if ($template->getName() === 'test-resource-1') {
                    return $resource1;
                } else {
                    throw new ResourceNotFoundException('TestResource', 'non-existent');
                }
            });

        $result = TestResource::getMany(['test-resource-1', 'non-existent']);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(TestResource::class, $result['test-resource-1']);
        $this->assertArrayNotHasKey('non-existent', $result);
    }

    // Test static getMany operation

    public function testThrowsExceptionWhenGetManyFailsWithEmptyNamesArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Names array cannot be empty');

        TestResource::getMany([]);
    }

    public function testCanGetAllResources(): void
    {
        $resource1 = new TestResource();
        $resource1->setName('test-resource-1');
        $resource2 = new TestResource();
        $resource2->setName('test-resource-2');

        $this->mockClient->expects($this->once())
            ->method('list')
            ->willReturn([$resource1, $resource2]);

        $result = TestResource::all();

        $this->assertCount(2, $result);
        $this->assertInstanceOf(TestResource::class, $result[0]);
        $this->assertInstanceOf(TestResource::class, $result[1]);
    }

    public function testCanGetAllResourcesWithNamespaceFilter(): void
    {
        $resource1 = new TestNamespacedResource();
        $resource1->setName('test-resource-1')->setNamespace('test-namespace');

        $this->mockClient->expects($this->once())
            ->method('list')
            ->willReturn([$resource1]);

        $result = TestNamespacedResource::all('test-namespace');

        $this->assertCount(1, $result);
        $this->assertInstanceOf(TestNamespacedResource::class, $result[0]);
    }

    // Test static all operation

    public function testCanGetAllResourcesWithOptions(): void
    {
        $resource1 = new TestResource();
        $resource1->setName('test-resource-1');

        $this->mockClient->expects($this->once())
            ->method('list')
            ->with($this->anything(), ['labelSelector' => 'app=test'])
            ->willReturn([$resource1]);

        $result = TestResource::all(null, ['labelSelector' => 'app=test']);

        $this->assertCount(1, $result);
    }

    public function testCanFindResourcesByLabels(): void
    {
        $resource1 = new TestResource();
        $resource1->setName('test-resource-1');

        $this->mockClient->expects($this->once())
            ->method('list')
            ->with($this->anything(), ['labelSelector' => 'app=test,env=prod'])
            ->willReturn([$resource1]);

        $result = TestResource::findByLabels(['app' => 'test', 'env' => 'prod']);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(TestResource::class, $result[0]);
    }

    public function testCanFindOneResourceByLabels(): void
    {
        $resource1 = new TestResource();
        $resource1->setName('test-resource-1');
        $resource2 = new TestResource();
        $resource2->setName('test-resource-2');

        $this->mockClient->expects($this->once())
            ->method('list')
            ->with($this->anything(), ['labelSelector' => 'app=test'])
            ->willReturn([$resource1, $resource2]);

        $result = TestResource::findOneByLabels(['app' => 'test']);

        $this->assertInstanceOf(TestResource::class, $result);
        $this->assertEquals('test-resource-1', $result->getName()); // Should return first match
    }

    // Test findByLabels operation

    public function testReturnsNullWhenNoResourcesFoundByLabels(): void
    {
        $this->mockClient->expects($this->once())
            ->method('list')
            ->with($this->anything(), ['labelSelector' => 'app=nonexistent'])
            ->willReturn([]);

        $result = TestResource::findOneByLabels(['app' => 'nonexistent']);

        $this->assertNull($result);
    }

    // Test findOneByLabels operation

    public function testCorrectlyIdentifiesNamespacedResourceClass(): void
    {
        $reflection = new ReflectionMethod(TestNamespacedResource::class, 'isNamespacedResourceClass');
        $reflection->setAccessible(true);

        $result = $reflection->invoke(null);

        $this->assertTrue($result);
    }

    public function testCorrectlyIdentifiesNonNamespacedResourceClass(): void
    {
        $reflection = new ReflectionMethod(TestResource::class, 'isNamespacedResourceClass');
        $reflection->setAccessible(true);

        $result = $reflection->invoke(null);

        $this->assertFalse($result);
    }

    // Test isNamespacedResourceClass method

    public function testSupportsMethodChainingForSetters(): void
    {
        $result = $this->resource
            ->setName('test-resource')
            ->setSpec(['replicas' => 3])
            ->setStatus(['phase' => 'Running']);

        $this->assertSame($this->resource, $result);
        $this->assertEquals('test-resource', $this->resource->getName());
        $this->assertEquals(3, $this->resource->getSpec()['replicas']);
        $this->assertEquals('Running', $this->resource->getStatus()['phase']);
    }

    public function testHandlesComplexNestedDataStructures(): void
    {
        $complexSpec = [
            'template' => [
                'spec' => [
                    'containers' => [
                        [
                            'name'  => 'app',
                            'image' => 'nginx:latest',
                            'ports' => [['containerPort' => 80]],
                            'env'   => [
                                ['name' => 'ENV_VAR', 'value' => 'test-value'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->resource->setSpec($complexSpec);
        $array = $this->resource->toArray();

        $this->assertEquals($complexSpec, $array['spec']);

        $json = $this->resource->toJson();
        $decoded = json_decode($json, true);
        $this->assertEquals($complexSpec, $decoded['spec']);
    }

    // Test method chaining

    public function testPreservesMetadataWhenConvertingBetweenFormats(): void
    {
        $metadata = [
            'name'        => 'test-resource',
            'namespace'   => 'test-namespace',
            'labels'      => ['app' => 'test', 'version' => 'v1.0.0'],
            'annotations' => ['description' => 'Test resource'],
        ];

        $this->resource->setMetadata($metadata);

        $json = $this->resource->toJson();
        $resourceFromJson = TestResource::fromJson($json);

        $this->assertEquals($metadata, $resourceFromJson->getMetadata());

        $yaml = $this->resource->toYaml();
        $resourceFromYaml = TestResource::fromYaml($yaml);

        $this->assertEquals($metadata, $resourceFromYaml->getMetadata());
    }

    // Test edge cases and error conditions

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockClient = $this->createMock(ClientInterface::class);
        $this->resource = new TestResource();
        $this->namespacedResource = new TestNamespacedResource();

        // Set default client for testing
        AbstractResource::setDefaultClient($this->mockClient);
    }

    protected function tearDown(): void
    {
        // Clear the static default client after each test
        $reflection = new ReflectionClass(AbstractResource::class);
        $property = $reflection->getProperty('defaultClient');
        $property->setAccessible(true);
        $property->setValue(null, null);

        parent::tearDown();
    }
}
