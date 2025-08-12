# Architecture Guide

This guide explains the design patterns and architecture of the Kubernetes PHP client library.

## Core Concepts

### 1. Resource Hierarchy

The library follows a strict inheritance hierarchy:

```
Resource (Base Abstract Class)
├── AbstractResource (API Group Specific)
│   ├── Core\V1\AbstractResource
│   ├── Apps\V1\AbstractResource
│   ├── Batch\V1\AbstractResource
│   └── ...
└── Concrete Resources (Pod, Service, etc.)
```

#### Base Resource Class
```php
abstract class Resource implements ResourceInterface
{
    use HasMetadata;
    
    // Core functionality: serialization, client management, CRUD operations
    abstract public function getApiVersion(): string;
    abstract public function getKind(): string;
}
```

#### API Group AbstractResource
```php
// Core/V1/AbstractResource.php
abstract class AbstractResource extends Resource
{
    public function getApiVersion(): string 
    {
        return 'v1';
    }
    
    abstract public function getKind(): string;
}
```

#### Concrete Resource
```php
class Pod extends AbstractResource
{
    use IsNamespacedResource; // Only for namespaced resources
    
    public function getKind(): string 
    {
        return 'Pod';
    }
    
    // Resource-specific methods
}
```

### 2. Namespace Mapping

Kubernetes API groups are mapped to PHP namespaces using a consistent pattern:

| Kubernetes API Group | PHP Namespace |
|---------------------|---------------|
| `core/v1` | `Kubernetes\API\Core\V1` |
| `apps/v1` | `Kubernetes\API\Apps\V1` |
| `batch/v1` | `Kubernetes\API\Batch\V1` |
| `networking.k8s.io/v1` | `Kubernetes\API\NetworkingK8sIo\V1` |
| `rbac.authorization.k8s.io/v1` | `Kubernetes\API\RbacAuthorizationK8sIo\V1` |

### 3. Trait System

The library uses traits to provide consistent functionality across resources:

#### HasMetadata Trait
Used by ALL Kubernetes resources:
```php
trait HasMetadata
{
    protected array $metadata = [];
    
    public function getName(): ?string;
    public function setName(string $name): self;
    public function getLabels(): array;
    public function addLabel(string $key, string $value): self;
    public function getAnnotations(): array;
    public function addAnnotation(string $key, string $value): self;
    // ... more metadata methods
}
```

#### IsNamespacedResource Trait
Used ONLY by namespaced resources:
```php
trait IsNamespacedResource
{
    public function getNamespace(): ?string;
    public function setNamespace(string $namespace): self;
    
    public static function isNamespacedResourceClass(): bool 
    {
        return true;
    }
}
```

## Design Patterns

### 1. Factory Pattern

Resources can be created from arrays (API responses) or files:

```php
// From API response
$pod = Pod::fromArray($apiData);

// From YAML file
$deployment = Deployment::fromYaml($yamlContent);

// From JSON file
$service = Service::fromJson($jsonContent);
```

### 2. Fluent Interface

All setter methods return `self` for method chaining:

```php
$pod = new Pod();
$pod->setName('my-app')
    ->setNamespace('production')
    ->addLabel('app', 'web-server')
    ->addLabel('version', 'v1.2.3')
    ->addContainer('nginx', 'nginx:1.21', [80, 443]);
```

### 3. Repository Pattern

The client acts as a repository for resource operations:

```php
interface ClientInterface
{
    public function create(ResourceInterface $resource): ResourceInterface;
    public function get(ResourceInterface $resource): ResourceInterface;
    public function update(ResourceInterface $resource): ResourceInterface;
    public function delete(ResourceInterface $resource): bool;
    public function list(ResourceInterface $template, array $options = []): array;
}
```

### 4. Active Record Pattern

Resources can perform operations on themselves when a default client is set:

```php
Resource::setDefaultClient($client);

$pod = new Pod();
$pod->setName('my-app')->setNamespace('default');
$pod->save(); // Creates the resource

$pod->addLabel('updated', 'true');
$pod->save(); // Updates the resource

$pod->delete(); // Deletes the resource
```

## Resource Structure

### Standard Resource Properties

Every Kubernetes resource has these standard fields:

```php
class SomeResource extends AbstractResource
{
    protected array $metadata = [];  // name, labels, annotations, etc.
    protected array $spec = [];      // desired state
    protected array $status = [];    // current state (read-only)
}
```

### Spec Management

The `spec` array contains the desired state configuration:

```php
public function setReplicas(int $replicas): self
{
    $this->spec['replicas'] = $replicas;
    return $this;
}

public function getReplicas(): int
{
    return $this->spec['replicas'] ?? 1;
}
```

### Status Access

Status fields are read-only and reflect the current state:

```php
public function getReadyReplicas(): int
{
    return $this->status['readyReplicas'] ?? 0;
}

public function getConditions(): array
{
    return $this->status['conditions'] ?? [];
}
```

## Advanced Patterns

### 1. Template Management

Complex resources like Deployments manage Pod templates:

```php
class Deployment extends AbstractResource
{
    public function getTemplate(): array
    {
        return $this->spec['template'] ?? [];
    }
    
    public function setTemplate(array $template): self
    {
        $this->spec['template'] = $template;
        return $this;
    }
    
    public function addContainer(string $name, string $image, ?array $ports = null): self
    {
        $container = ['name' => $name, 'image' => $image];
        
        if ($ports !== null) {
            $container['ports'] = array_map(fn($port) => ['containerPort' => $port], $ports);
        }
        
        $this->spec['template']['spec']['containers'][] = $container;
        return $this;
    }
}
```

### 2. Resource References

Resources can reference other resources:

```php
class Service extends AbstractResource
{
    public function setSelectorMatchLabels(array $labels): self
    {
        $this->spec['selector'] = $labels;
        return $this;
    }
    
    public function matchesPod(Pod $pod): bool
    {
        $selector = $this->spec['selector'] ?? [];
        $podLabels = $pod->getLabels();
        
        foreach ($selector as $key => $value) {
            if (!isset($podLabels[$key]) || $podLabels[$key] !== $value) {
                return false;
            }
        }
        
        return true;
    }
}
```

### 3. Helper Method Patterns

Complex resources provide helper methods for common operations:

```php
class StatefulSet extends AbstractResource
{
    // Scaling helpers
    public function scale(int $replicas): self
    {
        return $this->setReplicas($replicas);
    }
    
    public function scaleUp(int $amount = 1): self
    {
        return $this->setReplicas($this->getReplicas() + $amount);
    }
    
    public function scaleDown(int $amount = 1): self
    {
        $newReplicas = max(0, $this->getReplicas() - $amount);
        return $this->setReplicas($newReplicas);
    }
    
    // PVC template helpers
    public function addPvcTemplate(string $name, string $storageClass, string $size): self
    {
        $pvcTemplate = [
            'metadata' => ['name' => $name],
            'spec' => [
                'accessModes' => ['ReadWriteOnce'],
                'storageClassName' => $storageClass,
                'resources' => ['requests' => ['storage' => $size]]
            ]
        ];
        
        $this->spec['volumeClaimTemplates'][] = $pvcTemplate;
        return $this;
    }
}
```

## Error Handling Architecture

### Exception Hierarchy

```
KubernetesException (Base)
├── ApiException (HTTP/API errors)
├── AuthenticationException (Auth failures)
└── ResourceNotFoundException (404 errors)
```

### Error Context

Exceptions include relevant context:

```php
class ApiException extends KubernetesException
{
    public function __construct(
        string $message,
        int $code,
        public readonly ?array $response = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
    
    public function getApiResponse(): ?array
    {
        return $this->response;
    }
}
```

## Performance Considerations

### 1. Lazy Loading

Resources are only populated with data when needed:

```php
// This doesn't make an API call
$pod = new Pod();
$pod->setName('my-app');

// This makes the API call
$pod->save();
```

### 2. Efficient Listing

List operations support filtering to reduce data transfer:

```php
// Only get pods with specific labels
$pods = Pod::all('default', [
    'labelSelector' => 'app=web,tier=frontend',
    'fieldSelector' => 'status.phase=Running'
]);
```

### 3. Batch Operations

Multiple resources can be managed efficiently:

```php
$resources = [
    $deployment,
    $service,
    $configMap
];

foreach ($resources as $resource) {
    $client->create($resource);
}
```

## Testing Architecture

### Test Structure

Tests mirror the source structure:

```
tests/
├── Unit/
│   ├── Core/V1/
│   │   ├── PodTest.php
│   │   └── ServiceTest.php
│   └── Apps/V1/
│       └── DeploymentTest.php
└── Integration/
    └── ClientTest.php
```

### Test Patterns

```php
it('can create a pod with fluent interface', function (): void {
    $pod = new Pod();
    $result = $pod->setName('test')
                  ->setNamespace('default')
                  ->addLabel('app', 'test');
    
    expect($result)->toBe($pod); // Method chaining
    expect($pod->getName())->toBe('test');
    expect($pod->getLabels())->toHaveKey('app', 'test');
});
```

This architecture provides a robust, type-safe, and intuitive interface for working with Kubernetes resources in PHP applications.
