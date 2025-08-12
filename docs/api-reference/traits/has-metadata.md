# HasMetadata Trait API Reference

The HasMetadata trait provides common metadata operations for all Kubernetes resources, including name, labels, annotations, and other metadata fields.

## Trait Definition

```php
namespace Kubernetes\Traits;

trait HasMetadata
{
    protected array $metadata = [];
    
    // Name operations
    public function getName(): ?string;
    public function setName(string $name): self;
    
    // Label operations
    public function getLabels(): array;
    public function addLabel(string $key, string $value): self;
    public function setLabels(array $labels): self;
    
    // Annotation operations
    public function getAnnotations(): array;
    public function addAnnotation(string $key, string $value): self;
    public function setAnnotations(array $annotations): self;
    
    // Additional metadata operations
    public function getCreationTimestamp(): ?string;
    public function getUid(): ?string;
    public function getResourceVersion(): ?string;
    public function getGeneration(): ?int;
}
```

## Name Operations

### setName()
```php
public function setName(string $name): self
```

Sets the resource name.

**Parameters:**
- `$name` - Resource name (must follow Kubernetes naming conventions)

**Validation:**
- Must be lowercase alphanumeric characters or '-'
- Must start and end with alphanumeric character
- Maximum 253 characters

### getName()
```php
public function getName(): ?string
```

Returns the resource name or null if not set.

## Label Operations

### addLabel()
```php
public function addLabel(string $key, string $value): self
```

Adds or updates a single label.

**Parameters:**
- `$key` - Label key (must follow Kubernetes label key format)
- `$value` - Label value

**Example:**
```php
$resource->addLabel('app', 'web-server')
         ->addLabel('tier', 'frontend')
         ->addLabel('version', 'v1.2.3');
```

### setLabels()
```php
public function setLabels(array $labels): self
```

Sets all labels at once, replacing existing labels.

### getLabels()
```php
public function getLabels(): array
```

Returns all labels as an associative array.

### hasLabel()
```php
public function hasLabel(string $key): bool
```

Checks if a label exists.

### getLabel()
```php
public function getLabel(string $key): ?string
```

Gets a specific label value.

### removeLabel()
```php
public function removeLabel(string $key): self
```

Removes a label.

## Annotation Operations

### addAnnotation()
```php
public function addAnnotation(string $key, string $value): self
```

Adds or updates a single annotation.

**Example:**
```php
$resource->addAnnotation('kubernetes.io/managed-by', 'my-controller')
         ->addAnnotation('deployment.kubernetes.io/revision', '5')
         ->addAnnotation('description', 'Production web server');
```

### setAnnotations()
```php
public function setAnnotations(array $annotations): self
```

Sets all annotations at once.

### getAnnotations()
```php
public function getAnnotations(): array
```

Returns all annotations.

### hasAnnotation()
```php
public function hasAnnotation(string $key): bool
```

Checks if an annotation exists.

### getAnnotation()
```php
public function getAnnotation(string $key): ?string
```

Gets a specific annotation value.

### removeAnnotation()
```php
public function removeAnnotation(string $key): self
```

Removes an annotation.

## Standard Label Helpers

### addStandardLabels()
```php
public function addStandardLabels(string $app, ?string $version = null, ?string $component = null): self
```

Adds recommended Kubernetes labels.

**Standard labels added:**
- `app.kubernetes.io/name` - Application name
- `app.kubernetes.io/version` - Application version (if provided)
- `app.kubernetes.io/component` - Application component (if provided)
- `app.kubernetes.io/managed-by` - Set to 'kubernetes-php-client'

### addAppLabel()
```php
public function addAppLabel(string $app): self
```

Convenience method for setting the app label.

### addVersionLabel()
```php
public function addVersionLabel(string $version): self
```

Convenience method for setting the version label.

## Metadata Access (Read-Only)

### getCreationTimestamp()
```php
public function getCreationTimestamp(): ?string
```

Returns creation timestamp in RFC3339 format.

### getUid()
```php
public function getUid(): ?string
```

Returns unique identifier assigned by Kubernetes.

### getResourceVersion()
```php
public function getResourceVersion(): ?string
```

Returns resource version for optimistic concurrency control.

### getGeneration()
```php
public function getGeneration(): ?int
```

Returns generation number for spec changes.

### getDeletionTimestamp()
```php
public function getDeletionTimestamp(): ?string
```

Returns deletion timestamp if resource is being deleted.

### getFinalizers()
```php
public function getFinalizers(): array
```

Returns finalizers preventing deletion.

## Owner Reference Operations

### addOwnerReference()
```php
public function addOwnerReference(ResourceInterface $owner, bool $controller = false, bool $blockOwnerDeletion = true): self
```

Adds an owner reference for garbage collection.

### getOwnerReferences()
```php
public function getOwnerReferences(): array
```

Returns owner references.

### isOwnedBy()
```php
public function isOwnedBy(ResourceInterface $resource): bool
```

Checks if resource is owned by another resource.

## Usage Examples

### Basic Metadata Setup
```php
$pod = new Pod();
$pod->setName('web-server-pod')
    ->addLabel('app', 'web-server')
    ->addLabel('tier', 'frontend')
    ->addLabel('version', 'v1.0.0')
    ->addAnnotation('description', 'Main web server pod')
    ->addAnnotation('maintainer', 'platform-team@company.com');
```

### Standard Labels Pattern
```php
$deployment = new Deployment();
$deployment->setName('api-server')
          ->addStandardLabels('api-server', 'v2.1.0', 'backend')
          ->addLabel('environment', 'production');

// Results in labels:
// app.kubernetes.io/name: api-server
// app.kubernetes.io/version: v2.1.0
// app.kubernetes.io/component: backend
// app.kubernetes.io/managed-by: kubernetes-php-client
// environment: production
```

### Prometheus Integration
```php
$service = new Service();
$service->setName('metrics-service')
        ->addAnnotation('prometheus.io/scrape', 'true')
        ->addAnnotation('prometheus.io/port', '9090')
        ->addAnnotation('prometheus.io/path', '/metrics')
        ->addAnnotation('prometheus.io/interval', '30s');
```

### Service Mesh Annotations
```php
$deployment = new Deployment();
$deployment->setName('microservice')
          ->addAnnotation('sidecar.istio.io/inject', 'true')
          ->addAnnotation('traffic.sidecar.istio.io/excludeOutboundPorts', '8080,9090')
          ->addAnnotation('sidecar.istio.io/proxyCPU', '100m')
          ->addAnnotation('sidecar.istio.io/proxyMemory', '128Mi');
```

### Owner Reference for Garbage Collection
```php
$configMap = new ConfigMap();
$configMap->setName('app-config');

$deployment = new Deployment();
$deployment->setName('app')
          ->addOwnerReference($configMap, true); // ConfigMap owns Deployment

// When ConfigMap is deleted, Deployment will be automatically deleted
```

### Label Selectors and Filtering
```php
// Setting up labels for selection
$pod1 = new Pod();
$pod1->setName('web-1')
     ->addLabel('app', 'web-server')
     ->addLabel('tier', 'frontend')
     ->addLabel('environment', 'production');

$pod2 = new Pod();
$pod2->setName('api-1')
     ->addLabel('app', 'api-server')
     ->addLabel('tier', 'backend')
     ->addLabel('environment', 'production');

// Service selecting frontend pods
$service = new Service();
$service->setName('frontend-service')
        ->setSelectorMatchLabels([
            'app' => 'web-server',
            'tier' => 'frontend'
        ]);
```

### Dynamic Label Management
```php
$resource = Pod::find('my-pod', 'default');

// Check and update labels
if (!$resource->hasLabel('updated')) {
    $resource->addLabel('updated', date('Y-m-d'))
             ->addLabel('updated-by', 'automation-script');
    
    $client->update($resource);
}

// Remove deprecated labels
if ($resource->hasLabel('deprecated-label')) {
    $resource->removeLabel('deprecated-label');
    $client->update($resource);
}
```

### Metadata Inspection
```php
$deployment = Deployment::find('my-app', 'production');

echo "Created: " . $deployment->getCreationTimestamp() . "\n";
echo "UID: " . $deployment->getUid() . "\n";
echo "Generation: " . $deployment->getGeneration() . "\n";
echo "Resource Version: " . $deployment->getResourceVersion() . "\n";

// Check if being deleted
if ($deployment->getDeletionTimestamp()) {
    echo "Resource is being deleted\n";
    $finalizers = $deployment->getFinalizers();
    echo "Finalizers: " . implode(', ', $finalizers) . "\n";
}
```

## Best Practices

### Label Naming Conventions
```php
// ✅ Good label keys
$resource->addLabel('app.kubernetes.io/name', 'my-app')
         ->addLabel('app.kubernetes.io/version', 'v1.0.0')
         ->addLabel('company.com/team', 'platform')
         ->addLabel('environment', 'production');

// ❌ Avoid these patterns
// $resource->addLabel('APP_NAME', 'my-app');           // Use lowercase
// $resource->addLabel('app-name', 'My App');           // Avoid spaces in values
// $resource->addLabel('verylongkeyname...', 'value');  // Keep keys reasonable
```

### Annotation Usage Guidelines
```php
// Configuration and tooling annotations
$resource->addAnnotation('kubectl.kubernetes.io/last-applied-configuration', $json)
         ->addAnnotation('deployment.kubernetes.io/revision', '3')
         ->addAnnotation('ingress.kubernetes.io/ssl-redirect', 'true');

// Custom application annotations
$resource->addAnnotation('mycompany.com/contact', 'team@company.com')
         ->addAnnotation('mycompany.com/cost-center', 'engineering')
         ->addAnnotation('mycompany.com/backup-policy', 'daily');
```

## See Also

- [IsNamespacedResource Trait](is-namespaced-resource.md) - Namespace operations
- [Resource](../resource.md) - Base resource class
- [Labels and Selectors Guide](../../examples/labels-and-selectors.md) - Advanced selection patterns
