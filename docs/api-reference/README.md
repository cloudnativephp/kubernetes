# API Reference

Complete API documentation for the Kubernetes PHP client library.

## Core Components

### Base Classes
- **[Resource](resource.md)** - Base abstract class for all Kubernetes resources
- **[Client](client.md)** - Main client for Kubernetes API interactions
- **[ClientInterface](client-interface.md)** - Contract for client implementations

### Traits
- **[HasMetadata](traits/has-metadata.md)** - Common metadata operations (labels, annotations, name)
- **[IsNamespacedResource](traits/is-namespaced-resource.md)** - Namespace operations for namespaced resources

### Exceptions
- **[KubernetesException](exceptions/kubernetes-exception.md)** - Base exception class
- **[ApiException](exceptions/api-exception.md)** - HTTP/API related errors
- **[AuthenticationException](exceptions/authentication-exception.md)** - Authentication failures
- **[ResourceNotFoundException](exceptions/resource-not-found-exception.md)** - Resource not found errors

## API Groups

### Core/v1 Resources
- **[Pod](core/v1/pod.md)** - Basic compute unit
- **[Service](core/v1/service.md)** - Service discovery and load balancing
- **[ConfigMap](core/v1/configmap.md)** - Configuration data storage
- **[Secret](core/v1/secret.md)** - Sensitive data storage
- **[PersistentVolume](core/v1/persistent-volume.md)** - Cluster storage
- **[PersistentVolumeClaim](core/v1/persistent-volume-claim.md)** - Storage requests
- **[ServiceAccount](core/v1/service-account.md)** - Pod identity
- **[Namespace](core/v1/namespace.md)** - Resource isolation
- **[Node](core/v1/node.md)** - Cluster nodes
- **[Event](core/v1/event.md)** - Cluster events
- **[Endpoints](core/v1/endpoints.md)** - Service endpoints
- **[LimitRange](core/v1/limit-range.md)** - Resource constraints
- **[ResourceQuota](core/v1/resource-quota.md)** - Resource limits
- **[ReplicationController](core/v1/replication-controller.md)** - Pod replication (legacy)

### Apps/v1 Resources
- **[Deployment](apps/v1/deployment.md)** - Declarative application updates
- **[ReplicaSet](apps/v1/replica-set.md)** - Pod replication management
- **[StatefulSet](apps/v1/stateful-set.md)** - Stateful application management
- **[DaemonSet](apps/v1/daemon-set.md)** - Node-level service management

### Batch/v1 Resources
- **[Job](batch/v1/job.md)** - One-time task execution
- **[CronJob](batch/v1/cron-job.md)** - Scheduled task execution

### Networking Resources
- **[Ingress](networking/v1/ingress.md)** - HTTP/HTTPS routing
- **[IngressClass](networking/v1/ingress-class.md)** - Ingress controller configuration
- **[NetworkPolicy](networking/v1/network-policy.md)** - Network traffic rules

### RBAC Resources
- **[Role](rbac/v1/role.md)** - Namespaced permissions
- **[RoleBinding](rbac/v1/role-binding.md)** - Namespaced permission assignments
- **[ClusterRole](rbac/v1/cluster-role.md)** - Cluster-wide permissions
- **[ClusterRoleBinding](rbac/v1/cluster-role-binding.md)** - Cluster-wide permission assignments

## Common Patterns

### Resource Creation
```php
// Direct instantiation
$pod = new Pod();
$pod->setName('my-pod')
    ->setNamespace('default')
    ->addContainer('app', 'nginx:latest');

// Factory methods
$pod = Pod::fromArray($apiData);
$pod = Pod::fromYaml($yamlContent);
$pod = Pod::fromJson($jsonContent);
```

### Resource Operations
```php
// CRUD operations
$resource = $client->create($pod);
$resource = $client->get($pod);
$resource = $client->update($pod);
$success = $client->delete($pod);

// List operations
$pods = $client->list(new Pod(), ['labelSelector' => 'app=web']);
$pods = Pod::all('default', ['labelSelector' => 'app=web']);
```

### Method Chaining
```php
$deployment = new Deployment();
$deployment->setName('web-app')
          ->setNamespace('production')
          ->setReplicas(3)
          ->addContainer('web', 'nginx:latest', [80])
          ->addLabel('app', 'web-server');
```

### Error Handling
```php
try {
    $pod = Pod::find('my-pod', 'default');
} catch (ResourceNotFoundException $e) {
    // Handle not found
} catch (ApiException $e) {
    // Handle API errors
    $statusCode = $e->getCode();
    $response = $e->getApiResponse();
}
```

## Type System

### Generic Types
- `array<string, mixed>` - Generic associative array
- `array<int, ResourceInterface>` - Array of resources
- `array<string, string>` - String-to-string mapping

### Resource-Specific Types
```php
// Container definition
array{
    name: string,
    image: string,
    ports?: array<int, array{containerPort: int, protocol?: string}>,
    env?: array<int, array{name: string, value: string}>,
    resources?: array{
        requests?: array<string, string>,
        limits?: array<string, string>
    }
}

// Label/annotation mapping
array<string, string>

// Resource status conditions
array<int, array{
    type: string,
    status: string,
    reason?: string,
    message?: string,
    lastTransitionTime?: string
}>
```

## Configuration Options

### Client Configuration
```php
$client = new Client(
    string $server,           // Kubernetes API server URL
    string $token,            // Authentication token
    ?string $caCert = null,   // CA certificate path
    array $options = []       // HTTP client options
);
```

### List Options
```php
$options = [
    'labelSelector' => 'app=web,tier=frontend',
    'fieldSelector' => 'status.phase=Running',
    'limit' => 100,
    'continue' => $continueToken,
    'watch' => false,
    'resourceVersion' => '12345'
];
```

### Serialization Options
```php
// JSON options
$jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;

// YAML options
$yamlInline = 4;  // Inline depth
$yamlIndent = 2;  // Indentation spaces
```

## Extension Points

### Custom Resources
```php
abstract class CustomResource extends Resource
{
    public function getApiVersion(): string 
    {
        return 'mycompany.com/v1';
    }
    
    abstract public function getKind(): string;
}

class MyCustomResource extends CustomResource
{
    public function getKind(): string 
    {
        return 'MyResource';
    }
}
```

### Custom Clients
```php
class CustomClient implements ClientInterface
{
    public function create(ResourceInterface $resource): ResourceInterface
    {
        // Custom implementation
    }
    
    // ... implement other methods
}
```

This API reference provides complete documentation for all classes, methods, and patterns in the Kubernetes PHP client library.
