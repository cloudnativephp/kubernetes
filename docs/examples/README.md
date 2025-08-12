# Usage Examples

This directory contains real-world usage examples and patterns for the Kubernetes PHP client library.

## Example Categories

### Basic Operations
- **[Pod Management](pod-management.md)** - Creating, updating, and managing Pods
- **[Service Configuration](service-configuration.md)** - Setting up Services and load balancing
- **[ConfigMap & Secret Management](config-secrets.md)** - Managing configuration and sensitive data

### Workload Management
- **[Deployment Patterns](deployment-patterns.md)** - Application deployments and rolling updates
- **[StatefulSet Examples](statefulset-examples.md)** - Database and stateful application patterns
- **[DaemonSet Usage](daemonset-usage.md)** - System services and node-level applications
- **[Job & CronJob Patterns](job-patterns.md)** - Batch processing and scheduled tasks

### Advanced Scenarios
- **[Multi-Resource Applications](multi-resource-apps.md)** - Complete application stacks
- **[RBAC Configuration](rbac-examples.md)** - Security and access control
- **[Networking Setup](networking-examples.md)** - Ingress, NetworkPolicy, and connectivity
- **[Storage Management](storage-examples.md)** - Persistent volumes and storage classes

### Production Patterns
- **[CI/CD Integration](cicd-integration.md)** - Deployment automation
- **[Monitoring & Observability](monitoring-setup.md)** - Setting up monitoring resources
- **[Scaling Strategies](scaling-strategies.md)** - Auto-scaling and resource management
- **[Backup & Disaster Recovery](backup-patterns.md)** - Data protection strategies

### Framework Integration
- **[Laravel Integration](laravel-integration.md)** - Using with Laravel applications
- **[Symfony Integration](symfony-integration.md)** - Integration with Symfony projects
- **[Standalone Applications](standalone-apps.md)** - Pure PHP application examples

## Common Patterns

### Resource Factory Pattern
```php
class KubernetesResourceFactory
{
    public static function createWebApplication(
        string $name, 
        string $namespace, 
        string $image, 
        int $replicas = 3
    ): array {
        $deployment = new Deployment();
        $deployment->setName($name)
                  ->setNamespace($namespace)
                  ->setReplicas($replicas)
                  ->addContainer('app', $image, [8080]);

        $service = new Service();
        $service->setName($name)
               ->setNamespace($namespace)
               ->setType('ClusterIP')
               ->addPort('http', 80, 8080);

        return [$deployment, $service];
    }
}
```

### Configuration Builder Pattern
```php
class ApplicationBuilder
{
    private array $resources = [];
    
    public function addDeployment(string $name, string $image): self
    {
        $this->resources[] = (new Deployment())
            ->setName($name)
            ->addContainer('app', $image);
        return $this;
    }
    
    public function addService(string $name, int $port): self
    {
        $this->resources[] = (new Service())
            ->setName($name)
            ->addPort('http', $port);
        return $this;
    }
    
    public function deploy(ClientInterface $client): array
    {
        $deployed = [];
        foreach ($this->resources as $resource) {
            $deployed[] = $client->create($resource);
        }
        return $deployed;
    }
}
```

### Error Handling Pattern
```php
class KubernetesDeployer
{
    public function deployWithRetry(ResourceInterface $resource, int $maxRetries = 3): ResourceInterface
    {
        $lastException = null;
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                return $this->client->create($resource);
            } catch (ApiException $e) {
                $lastException = $e;
                
                if ($e->getCode() === 409) { // Conflict
                    // Resource already exists, try update
                    try {
                        return $this->client->update($resource);
                    } catch (ApiException $updateException) {
                        $lastException = $updateException;
                    }
                }
                
                if ($attempt < $maxRetries) {
                    sleep(pow(2, $attempt)); // Exponential backoff
                }
            }
        }
        
        throw $lastException;
    }
}
```

## Quick Reference

### Resource Creation Shortcuts
```php
// Pod with common configuration
$pod = Pod::create('my-app', 'default')
    ->addContainer('app', 'nginx:latest', [80])
    ->addLabel('app', 'web-server')
    ->setRestartPolicy('Always');

// Service with selector
$service = Service::create('my-service', 'default')
    ->setType('LoadBalancer')
    ->setSelectorMatchLabels(['app' => 'web-server'])
    ->addPort('http', 80, 8080);

// Deployment with scaling
$deployment = Deployment::create('my-deployment', 'default')
    ->setReplicas(5)
    ->addContainer('app', 'my-app:v1.0.0')
    ->setRollingUpdateStrategy(2, 1);
```

### Batch Operations
```php
// Deploy multiple resources
$resources = [
    $configMap,
    $secret,
    $deployment,
    $service,
    $ingress
];

$deployer = new BatchDeployer($client);
$results = $deployer->deployAll($resources);

// Cleanup resources
$cleanup = new ResourceCleanup($client);
$cleanup->deleteByLabels('default', ['app' => 'my-app']);
```
