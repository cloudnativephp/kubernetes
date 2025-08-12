# Pod API Reference

The Pod class represents the smallest deployable unit in Kubernetes, encapsulating one or more containers.

## Class Definition

```php
namespace Kubernetes\API\Core\V1;

use Kubernetes\Traits\IsNamespacedResource;

class Pod extends AbstractResource
{
    use IsNamespacedResource;
    
    public function getKind(): string;
    public function getApiVersion(): string; // Inherited: 'v1'
}
```

## Properties

| Property | Type | Description |
|----------|------|-------------|
| `$metadata` | `array` | Pod metadata (name, labels, annotations, etc.) |
| `$spec` | `array` | Pod specification (containers, volumes, etc.) |
| `$status` | `array` | Pod status (phase, conditions, etc.) - read-only |

## Core Methods

### Container Management

#### addContainer()
```php
public function addContainer(
    string $name, 
    string $image, 
    ?array $ports = null, 
    ?array $env = null
): self
```

Adds a container to the Pod specification.

**Parameters:**
- `$name` - Container name (must be unique within the Pod)
- `$image` - Container image (e.g., 'nginx:latest')
- `$ports` - Array of port numbers to expose (optional)
- `$env` - Environment variables as key-value pairs (optional)

**Returns:** `self` for method chaining

**Example:**
```php
$pod = new Pod();
$pod->addContainer('web', 'nginx:1.21', [80, 443], [
    'NGINX_PORT' => '80',
    'ENVIRONMENT' => 'production'
]);
```

#### getContainers()
```php
public function getContainers(): array
```

Returns array of container specifications.

**Returns:** Array of container definitions

#### setContainerImage()
```php
public function setContainerImage(string $containerName, string $image): self
```

Updates the image of a specific container.

#### setContainerResources()
```php
public function setContainerResources(string $containerName, array $resources): self
```

Sets resource requests and limits for a container.

**Example:**
```php
$pod->setContainerResources('web', [
    'requests' => ['cpu' => '100m', 'memory' => '128Mi'],
    'limits' => ['cpu' => '500m', 'memory' => '512Mi']
]);
```

### Volume Management

#### addVolume()
```php
public function addVolume(string $name, array $volumeSource): self
```

Adds a volume to the Pod.

**Example:**
```php
// Empty directory volume
$pod->addVolume('cache', ['emptyDir' => []]);

// ConfigMap volume
$pod->addVolume('config', [
    'configMap' => ['name' => 'app-config']
]);

// Secret volume
$pod->addVolume('secrets', [
    'secret' => ['secretName' => 'app-secrets']
]);
```

#### addVolumeMount()
```php
public function addVolumeMount(
    string $containerName, 
    string $volumeName, 
    string $mountPath, 
    bool $readOnly = false
): self
```

Mounts a volume in a container.

#### addConfigMapVolume()
```php
public function addConfigMapVolume(
    string $volumeName, 
    string $configMapName, 
    string $mountPath, 
    ?string $containerName = null
): self
```

Convenience method to add and mount a ConfigMap volume.

#### addSecretVolume()
```php
public function addSecretVolume(
    string $volumeName, 
    string $secretName, 
    string $mountPath, 
    ?string $containerName = null
): self
```

Convenience method to add and mount a Secret volume.

### Health Checks

#### setReadinessProbe()
```php
public function setReadinessProbe(string $containerName, array $probe): self
```

Sets readiness probe for traffic routing decisions.

**Example:**
```php
$pod->setReadinessProbe('web', [
    'httpGet' => [
        'path' => '/health/ready',
        'port' => 8080
    ],
    'initialDelaySeconds' => 10,
    'periodSeconds' => 5,
    'timeoutSeconds' => 3,
    'failureThreshold' => 3
]);
```

#### setLivenessProbe()
```php
public function setLivenessProbe(string $containerName, array $probe): self
```

Sets liveness probe for container restart decisions.

#### setStartupProbe()
```php
public function setStartupProbe(string $containerName, array $probe): self
```

Sets startup probe for slow-starting containers.

### Security Context

#### setSecurityContext()
```php
public function setSecurityContext(array $securityContext): self
```

Sets Pod-level security context.

**Example:**
```php
$pod->setSecurityContext([
    'runAsNonRoot' => true,
    'runAsUser' => 1000,
    'runAsGroup' => 1000,
    'fsGroup' => 1000
]);
```

#### setContainerSecurityContext()
```php
public function setContainerSecurityContext(string $containerName, array $securityContext): self
```

Sets container-level security context.

### Configuration

#### setRestartPolicy()
```php
public function setRestartPolicy(string $policy): self
```

Sets Pod restart policy.

**Parameters:**
- `$policy` - One of: 'Always', 'OnFailure', 'Never'

#### setNodeSelector()
```php
public function setNodeSelector(array $nodeSelector): self
```

Sets node selection constraints.

#### addToleration()
```php
public function addToleration(array $toleration): self
```

Adds a toleration for node taints.

## Status Methods (Read-Only)

### getPhase()
```php
public function getPhase(): ?string
```

Returns Pod phase: 'Pending', 'Running', 'Succeeded', 'Failed', 'Unknown'

### getPodIP()
```php
public function getPodIP(): ?string
```

Returns the Pod's IP address.

### getNodeName()
```php
public function getNodeName(): ?string
```

Returns the name of the node where the Pod is scheduled.

### getContainerStatuses()
```php
public function getContainerStatuses(): array
```

Returns status information for all containers.

**Example response:**
```php
[
    [
        'name' => 'web',
        'ready' => true,
        'restartCount' => 0,
        'image' => 'nginx:1.21',
        'state' => [
            'running' => [
                'startedAt' => '2023-08-12T10:30:00Z'
            ]
        ]
    ]
]
```

### getConditions()
```php
public function getConditions(): array
```

Returns Pod conditions (PodScheduled, ContainersReady, Initialized, Ready).

## Factory Methods

### fromArray()
```php
public static function fromArray(array $data): static
```

Creates Pod instance from Kubernetes API response.

### fromYaml()
```php
public static function fromYaml(string $yaml): static
```

Creates Pod instance from YAML string.

### fromJson()
```php
public static function fromJson(string $json): static
```

Creates Pod instance from JSON string.

### fromFile()
```php
public static function fromFile(string $filePath): static
```

Creates Pod instance from YAML or JSON file.

## Serialization Methods

### toArray()
```php
public function toArray(): array
```

Converts Pod to Kubernetes API format.

### toYaml()
```php
public function toYaml(int $inline = 4, int $indent = 2): string
```

Exports Pod as YAML string.

### toJson()
```php
public function toJson(int $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT): string
```

Exports Pod as JSON string.

### toFile()
```php
public function toFile(string $filePath, int $jsonFlags = 0, int $yamlInline = 4, int $yamlIndent = 2): bool
```

Saves Pod to file (format determined by extension).

## Static Query Methods

### all()
```php
public static function all(?string $namespace = null, array $options = [], ?ClientInterface $client = null): array
```

Lists Pods in namespace or cluster-wide.

**Example:**
```php
// All Pods in default namespace
$pods = Pod::all('default');

// Pods with label selector
$pods = Pod::all('production', [
    'labelSelector' => 'app=web-server,tier=frontend'
]);

// Running Pods only
$pods = Pod::all('default', [
    'fieldSelector' => 'status.phase=Running'
]);
```

### find()
```php
public static function find(string $name, ?string $namespace = null, ?ClientInterface $client = null): static
```

Finds a specific Pod by name.

## Usage Examples

### Basic Pod Creation
```php
$pod = new Pod();
$pod->setName('my-app')
    ->setNamespace('production')
    ->addContainer('app', 'my-app:v1.0.0', [8080])
    ->addLabel('app', 'my-application')
    ->addLabel('version', 'v1.0.0');

// Save to cluster
$pod->save(); // Requires default client
// or
$client->create($pod);
```

### Multi-Container Pod
```php
$pod = new Pod();
$pod->setName('multi-container-app')
    ->setNamespace('default')
    ->addContainer('web', 'nginx:latest', [80])
    ->addContainer('api', 'my-api:latest', [8080])
    ->addContainer('worker', 'my-worker:latest');

// Add shared volume
$pod->addVolume('shared-data', ['emptyDir' => []])
    ->addVolumeMount('web', 'shared-data', '/usr/share/nginx/html')
    ->addVolumeMount('api', 'shared-data', '/app/public');
```

### Pod with Health Checks
```php
$pod = new Pod();
$pod->setName('health-checked-app')
    ->addContainer('app', 'my-app:latest', [8080])
    ->setReadinessProbe('app', [
        'httpGet' => ['path' => '/ready', 'port' => 8080],
        'initialDelaySeconds' => 10,
        'periodSeconds' => 5
    ])
    ->setLivenessProbe('app', [
        'httpGet' => ['path' => '/health', 'port' => 8080],
        'initialDelaySeconds' => 30,
        'periodSeconds' => 10
    ]);
```

## See Also

- [Deployment](../apps/v1/deployment.md) - For managing Pod replicas
- [Service](service.md) - For exposing Pods
- [ConfigMap](configmap.md) - For Pod configuration
- [Secret](secret.md) - For sensitive data
- [HasMetadata Trait](../traits/has-metadata.md) - For metadata operations
- [IsNamespacedResource Trait](../traits/is-namespaced-resource.md) - For namespace operations
