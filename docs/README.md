# Kubernetes PHP Client Library Documentation

A comprehensive, type-safe PHP client library for interacting with Kubernetes resources, designed with patterns similar to Laravel's Eloquent ORM.

## Documentation Structure

- **[Getting Started](getting-started.md)** - Installation, setup, and basic usage
- **[Architecture Guide](architecture.md)** - Understanding the library structure and patterns
- **[API Reference](api-reference/README.md)** - Complete API documentation
- **[Usage Examples](examples/README.md)** - Real-world usage patterns and recipes
- **[Best Practices](best-practices.md)** - Production deployment guidelines
- **[Contributing](contributing.md)** - Development guidelines and coding standards

## Quick Start

```php
use Kubernetes\API\Core\V1\Pod;
use Kubernetes\API\Apps\V1\Deployment;
use Kubernetes\Client\Client;

// Create a client
$client = new Client('https://kubernetes.default.svc', $token);

// Create a Pod
$pod = new Pod();
$pod->setName('my-app')
    ->setNamespace('default')
    ->addContainer('app', 'nginx:latest', [80]);

// Deploy the Pod
$client->create($pod);

// Create a Deployment
$deployment = new Deployment();
$deployment->setName('my-deployment')
          ->setNamespace('default')
          ->setReplicas(3)
          ->setSelectorMatchLabels(['app' => 'my-app'])
          ->addContainer('app', 'nginx:latest', [80]);

// Deploy the Deployment
$client->create($deployment);
```

## Key Features

- **Type Safety**: Full PHP 8.4+ type hints and static analysis support
- **Fluent API**: Method chaining for intuitive resource configuration
- **Resource Management**: Create, read, update, delete, and list operations
- **Namespace Support**: Automatic namespace handling for namespaced resources
- **Format Support**: JSON and YAML serialization/deserialization
- **Test Coverage**: Comprehensive test suite with Pest framework
- **Production Ready**: Used in production environments

## Supported Resources

### Core/v1
- Pod, Service, Secret, ConfigMap
- PersistentVolume, PersistentVolumeClaim
- ServiceAccount, Namespace, Node
- Event, Endpoints, LimitRange, ResourceQuota
- ReplicationController

### Apps/v1
- Deployment, ReplicaSet, StatefulSet, DaemonSet

### Batch/v1
- Job, CronJob

### Networking/v1 & networking.k8s.io/v1
- Ingress, IngressClass, NetworkPolicy

### RBAC (rbac.authorization.k8s.io/v1)
- Role, RoleBinding, ClusterRole, ClusterRoleBinding

And many more...

## Requirements

- PHP 8.4 or higher
- Composer for dependency management
- Kubernetes cluster access

## License

This project is open source. Please see the [license file](../LICENSE) for more information.
