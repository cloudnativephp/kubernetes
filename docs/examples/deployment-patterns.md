# Deployment Patterns

Complete examples for managing application deployments with rolling updates, scaling strategies, and production patterns.

## Basic Deployment Creation

### Simple Web Application Deployment
```php
use Kubernetes\API\Apps\V1\Deployment;
use Kubernetes\API\Core\V1\Service;

$deployment = new Deployment();
$deployment->setName('web-app')
          ->setNamespace('production')
          ->setReplicas(3)
          ->setSelectorMatchLabels(['app' => 'web-app'])
          ->addContainer('web', 'nginx:1.21', [80])
          ->addLabel('app', 'web-app')
          ->addLabel('tier', 'frontend');

// Create accompanying service
$service = new Service();
$service->setName('web-app-service')
       ->setNamespace('production')
       ->setType('LoadBalancer')
       ->setSelectorMatchLabels(['app' => 'web-app'])
       ->addPort('http', 80, 80);

// Deploy both resources
$client->create($deployment);
$client->create($service);
```

### Deployment with Environment Configuration
```php
$deployment = new Deployment();
$deployment->setName('api-server')
          ->setNamespace('production')
          ->setReplicas(5)
          ->setSelectorMatchLabels(['app' => 'api-server'])
          ->addContainer('api', 'my-api:v2.1.0', [8080], [
              'DATABASE_URL' => 'postgresql://db.production.svc.cluster.local:5432/myapp',
              'REDIS_URL' => 'redis://redis.production.svc.cluster.local:6379',
              'LOG_LEVEL' => 'warn',
              'ENVIRONMENT' => 'production'
          ])
          ->setContainerResources('api', [
              'requests' => ['cpu' => '200m', 'memory' => '256Mi'],
              'limits' => ['cpu' => '1', 'memory' => '512Mi']
          ]);
```

## Rolling Update Strategies

### Safe Rolling Updates
```php
$deployment = new Deployment();
$deployment->setName('critical-app')
          ->setNamespace('production')
          ->setReplicas(10)
          ->setSelectorMatchLabels(['app' => 'critical-app'])
          ->addContainer('app', 'critical-app:v1.5.0')
          ->setRollingUpdateStrategy(2, 1); // maxUnavailable=1, maxSurge=2

// Health checks for safe rollouts
$deployment->setReadinessProbe('app', [
    'httpGet' => ['path' => '/health', 'port' => 8080],
    'initialDelaySeconds' => 10,
    'periodSeconds' => 5,
    'failureThreshold' => 3
]);

$deployment->setLivenessProbe('app', [
    'httpGet' => ['path' => '/health', 'port' => 8080],
    'initialDelaySeconds' => 30,
    'periodSeconds' => 10,
    'failureThreshold' => 3
]);
```

### Blue-Green Deployment Pattern
```php
class BlueGreenDeployer
{
    private ClientInterface $client;
    
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }
    
    public function deployNewVersion(string $appName, string $namespace, string $newImage): void
    {
        // Get current deployment
        $currentDeployment = Deployment::find($appName, $namespace);
        $currentVersion = $currentDeployment->getLabel('version') ?? 'v1';
        
        // Determine new version
        $newVersion = $this->getNextVersion($currentVersion);
        
        // Create new deployment with different selector
        $newDeployment = clone $currentDeployment;
        $newDeployment->setName($appName . '-' . $newVersion)
                     ->addLabel('version', $newVersion)
                     ->setSelectorMatchLabels([
                         'app' => $appName,
                         'version' => $newVersion
                     ])
                     ->setContainerImage('app', $newImage);
        
        // Deploy new version
        $this->client->create($newDeployment);
        
        // Wait for new deployment to be ready
        $this->waitForDeploymentReady($newDeployment);
        
        // Switch service to new version
        $service = Service::find($appName . '-service', $namespace);
        $service->setSelectorMatchLabels([
            'app' => $appName,
            'version' => $newVersion
        ]);
        $this->client->update($service);
        
        // Clean up old deployment after verification
        sleep(300); // Wait 5 minutes for verification
        $this->client->delete($currentDeployment);
    }
    
    private function waitForDeploymentReady(Deployment $deployment): void
    {
        $timeout = 600; // 10 minutes
        $start = time();
        
        while (time() - $start < $timeout) {
            $current = Deployment::find($deployment->getName(), $deployment->getNamespace());
            
            if ($current->getReadyReplicas() === $current->getReplicas()) {
                return; // Deployment is ready
            }
            
            sleep(10);
        }
        
        throw new RuntimeException('Deployment failed to become ready within timeout');
    }
}
```

### Canary Deployment Pattern
```php
class CanaryDeployer
{
    private ClientInterface $client;
    
    public function deployCanary(
        string $appName, 
        string $namespace, 
        string $newImage, 
        int $canaryPercentage = 10
    ): void {
        $currentDeployment = Deployment::find($appName, $namespace);
        $totalReplicas = $currentDeployment->getReplicas();
        
        // Calculate canary replicas
        $canaryReplicas = max(1, intval($totalReplicas * $canaryPercentage / 100));
        $stableReplicas = $totalReplicas - $canaryReplicas;
        
        // Scale down current deployment
        $currentDeployment->setReplicas($stableReplicas);
        $this->client->update($currentDeployment);
        
        // Create canary deployment
        $canaryDeployment = new Deployment();
        $canaryDeployment->setName($appName . '-canary')
                        ->setNamespace($namespace)
                        ->setReplicas($canaryReplicas)
                        ->setSelectorMatchLabels([
                            'app' => $appName,
                            'version' => 'canary'
                        ])
                        ->addContainer('app', $newImage)
                        ->addLabel('app', $appName)
                        ->addLabel('version', 'canary');
        
        $this->client->create($canaryDeployment);
        
        // Update service to include canary pods
        $service = Service::find($appName . '-service', $namespace);
        $service->setSelectorMatchLabels(['app' => $appName]); // Remove version selector
        $this->client->update($service);
    }
    
    public function promoteCanary(string $appName, string $namespace): void
    {
        $canaryDeployment = Deployment::find($appName . '-canary', $namespace);
        $currentDeployment = Deployment::find($appName, $namespace);
        
        // Get canary image
        $canaryImage = $canaryDeployment->getContainerImage('app');
        
        // Update current deployment with canary image
        $currentDeployment->setContainerImage('app', $canaryImage)
                         ->setReplicas($currentDeployment->getReplicas() + $canaryDeployment->getReplicas());
        
        $this->client->update($currentDeployment);
        
        // Wait for rollout
        $this->waitForDeploymentReady($currentDeployment);
        
        // Delete canary deployment
        $this->client->delete($canaryDeployment);
        
        // Restore service selector
        $service = Service::find($appName . '-service', $namespace);
        $service->setSelectorMatchLabels(['app' => $appName, 'version' => 'stable']);
        $this->client->update($service);
    }
}
```

## Scaling Strategies

### Horizontal Pod Autoscaler Integration
```php
use Kubernetes\API\Autoscaling\V2\HorizontalPodAutoscaler;

$deployment = new Deployment();
$deployment->setName('scalable-app')
          ->setNamespace('production')
          ->setReplicas(3) // Initial replicas
          ->setSelectorMatchLabels(['app' => 'scalable-app'])
          ->addContainer('app', 'my-app:latest', [8080])
          ->setContainerResources('app', [
              'requests' => ['cpu' => '100m', 'memory' => '128Mi'],
              'limits' => ['cpu' => '500m', 'memory' => '512Mi']
          ]);

// Create HPA
$hpa = new HorizontalPodAutoscaler();
$hpa->setName('scalable-app-hpa')
   ->setNamespace('production')
   ->setTargetRef('Deployment', 'scalable-app')
   ->setMinReplicas(3)
   ->setMaxReplicas(20)
   ->addCpuMetric(70) // Target 70% CPU utilization
   ->addMemoryMetric(80); // Target 80% memory utilization

$client->create($deployment);
$client->create($hpa);
```

### Manual Scaling Operations
```php
// Scale up for high traffic
$deployment = Deployment::find('web-app', 'production');
$deployment->scale(10);
$client->update($deployment);

// Scale down during low traffic
$deployment->scale(3);
$client->update($deployment);

// Gradual scaling with monitoring
class GradualScaler
{
    public function scaleGradually(
        Deployment $deployment, 
        int $targetReplicas, 
        int $stepSize = 2, 
        int $waitSeconds = 60
    ): void {
        $currentReplicas = $deployment->getReplicas();
        
        if ($targetReplicas > $currentReplicas) {
            // Scale up
            while ($currentReplicas < $targetReplicas) {
                $currentReplicas = min($targetReplicas, $currentReplicas + $stepSize);
                $deployment->setReplicas($currentReplicas);
                $this->client->update($deployment);
                
                $this->waitForStability($deployment, $waitSeconds);
            }
        } else {
            // Scale down
            while ($currentReplicas > $targetReplicas) {
                $currentReplicas = max($targetReplicas, $currentReplicas - $stepSize);
                $deployment->setReplicas($currentReplicas);
                $this->client->update($deployment);
                
                $this->waitForStability($deployment, $waitSeconds);
            }
        }
    }
}
```

## Advanced Configuration Patterns

### Multi-Container Application
```php
$deployment = new Deployment();
$deployment->setName('microservices-app')
          ->setNamespace('production')
          ->setReplicas(3)
          ->setSelectorMatchLabels(['app' => 'microservices-app']);

// Main application container
$deployment->addContainer('api', 'my-api:v2.0.0', [8080], [
    'DATABASE_URL' => 'postgresql://db:5432/myapp'
]);

// Sidecar logging container
$deployment->addContainer('logger', 'fluentd:v1.14', [], [
    'FLUENTD_CONF' => 'fluent.conf'
]);

// Monitoring sidecar
$deployment->addContainer('metrics', 'prometheus-exporter:latest', [9090]);

// Shared volumes
$deployment->addVolume('logs', ['emptyDir' => []])
          ->addVolumeMount('api', 'logs', '/var/log/app')
          ->addVolumeMount('logger', 'logs', '/var/log/app');

$deployment->addVolume('config', [
    'configMap' => ['name' => 'app-config']
])->addVolumeMount('api', 'config', '/etc/config')
  ->addVolumeMount('logger', 'config', '/etc/fluentd');
```

### Database Application with Persistent Storage
```php
// Note: For databases, consider using StatefulSet instead
$deployment = new Deployment();
$deployment->setName('postgres-app')
          ->setNamespace('database')
          ->setReplicas(1) // Typically 1 for single-master databases
          ->setSelectorMatchLabels(['app' => 'postgres'])
          ->addContainer('postgres', 'postgres:13', [5432], [
              'POSTGRES_DB' => 'myapp',
              'POSTGRES_USER' => 'appuser',
              'POSTGRES_PASSWORD_FILE' => '/etc/secrets/postgres-password'
          ]);

// Add persistent volume
$deployment->addVolume('postgres-data', [
    'persistentVolumeClaim' => ['claimName' => 'postgres-pvc']
])->addVolumeMount('postgres', 'postgres-data', '/var/lib/postgresql/data');

// Add secret for password
$deployment->addVolume('postgres-secrets', [
    'secret' => ['secretName' => 'postgres-secret']
])->addVolumeMount('postgres', 'postgres-secrets', '/etc/secrets');
```

## Deployment Monitoring and Health

### Comprehensive Health Checks
```php
$deployment = new Deployment();
$deployment->setName('health-monitored-app')
          ->setNamespace('production')
          ->setReplicas(5)
          ->setSelectorMatchLabels(['app' => 'monitored-app'])
          ->addContainer('app', 'my-app:latest', [8080]);

// Startup probe for slow-starting applications
$deployment->setStartupProbe('app', [
    'httpGet' => ['path' => '/startup', 'port' => 8080],
    'initialDelaySeconds' => 0,
    'periodSeconds' => 10,
    'timeoutSeconds' => 5,
    'failureThreshold' => 30 // Allow 5 minutes for startup
]);

// Readiness probe
$deployment->setReadinessProbe('app', [
    'httpGet' => ['path' => '/ready', 'port' => 8080],
    'initialDelaySeconds' => 5,
    'periodSeconds' => 5,
    'timeoutSeconds' => 3,
    'failureThreshold' => 3
]);

// Liveness probe
$deployment->setLivenessProbe('app', [
    'httpGet' => ['path' => '/health', 'port' => 8080],
    'initialDelaySeconds' => 30,
    'periodSeconds' => 10,
    'timeoutSeconds' => 5,
    'failureThreshold' => 3
]);
```

### Deployment Status Monitoring
```php
class DeploymentMonitor
{
    private ClientInterface $client;
    
    public function monitorRollout(string $name, string $namespace, int $timeout = 600): bool
    {
        $start = time();
        
        while (time() - $start < $timeout) {
            $deployment = Deployment::find($name, $namespace);
            
            $desired = $deployment->getReplicas();
            $updated = $deployment->getUpdatedReplicas();
            $ready = $deployment->getReadyReplicas();
            $available = $deployment->getAvailableReplicas();
            
            echo "Rollout status: {$ready}/{$desired} ready, {$updated} updated, {$available} available\n";
            
            // Check if rollout is complete
            if ($ready === $desired && $updated === $desired && $available === $desired) {
                echo "Rollout completed successfully!\n";
                return true;
            }
            
            // Check for rollout failure
            $conditions = $deployment->getConditions();
            foreach ($conditions as $condition) {
                if ($condition['type'] === 'Progressing' && 
                    $condition['status'] === 'False' &&
                    $condition['reason'] === 'ProgressDeadlineExceeded') {
                    echo "Rollout failed: Progress deadline exceeded\n";
                    return false;
                }
            }
            
            sleep(10);
        }
        
        echo "Rollout timed out\n";
        return false;
    }
    
    public function getRolloutHistory(string $name, string $namespace): array
    {
        $replicaSets = ReplicaSet::all($namespace, [
            'labelSelector' => "app={$name}"
        ]);
        
        $history = [];
        foreach ($replicaSets as $rs) {
            $revision = $rs->getAnnotation('deployment.kubernetes.io/revision');
            if ($revision) {
                $history[] = [
                    'revision' => (int)$revision,
                    'name' => $rs->getName(),
                    'replicas' => $rs->getReplicas(),
                    'ready' => $rs->getReadyReplicas(),
                    'created' => $rs->getCreationTimestamp()
                ];
            }
        }
        
        usort($history, fn($a, $b) => $b['revision'] <=> $a['revision']);
        return $history;
    }
}
```

## Production Deployment Checklist

### Pre-Deployment Validation
```php
class DeploymentValidator
{
    public function validateDeployment(Deployment $deployment): array
    {
        $issues = [];
        
        // Check resource limits
        $containers = $deployment->getContainers();
        foreach ($containers as $container) {
            if (!isset($container['resources']['limits'])) {
                $issues[] = "Container {$container['name']} missing resource limits";
            }
            
            if (!isset($container['resources']['requests'])) {
                $issues[] = "Container {$container['name']} missing resource requests";
            }
        }
        
        // Check health probes
        foreach ($containers as $container) {
            if (!isset($container['readinessProbe'])) {
                $issues[] = "Container {$container['name']} missing readiness probe";
            }
            
            if (!isset($container['livenessProbe'])) {
                $issues[] = "Container {$container['name']} missing liveness probe";
            }
        }
        
        // Check security context
        $securityContext = $deployment->getSecurityContext();
        if (!isset($securityContext['runAsNonRoot']) || !$securityContext['runAsNonRoot']) {
            $issues[] = "Deployment should run as non-root user";
        }
        
        // Check replica count
        if ($deployment->getReplicas() < 2) {
            $issues[] = "Deployment should have at least 2 replicas for high availability";
        }
        
        return $issues;
    }
}
```

This comprehensive guide covers all the essential deployment patterns needed for production Kubernetes applications using the PHP client library.
