# Deployment API Reference

The Deployment class provides declarative updates for Pods and ReplicaSets, enabling rolling updates, scaling, and rollback capabilities.

## Class Definition

```php
namespace Kubernetes\API\Apps\V1;

use Kubernetes\Traits\IsNamespacedResource;

class Deployment extends AbstractResource
{
    use IsNamespacedResource;
    
    public function getKind(): string; // Returns 'Deployment'
    public function getApiVersion(): string; // Inherited: 'apps/v1'
}
```

## Core Methods

### Replica Management

#### setReplicas()
```php
public function setReplicas(int $replicas): self
```

Sets the desired number of Pod replicas.

#### getReplicas()
```php
public function getReplicas(): int
```

Returns the desired replica count (default: 1).

#### scale()
```php
public function scale(int $replicas): self
```

Alias for `setReplicas()` - scales the deployment.

#### scaleUp()
```php
public function scaleUp(int $amount = 1): self
```

Increases replica count by specified amount.

#### scaleDown()
```php
public function scaleDown(int $amount = 1): self
```

Decreases replica count by specified amount (minimum 0).

### Selector Management

#### setSelectorMatchLabels()
```php
public function setSelectorMatchLabels(array $labels): self
```

Sets the label selector for Pod management.

**Example:**
```php
$deployment->setSelectorMatchLabels(['app' => 'web-server', 'tier' => 'frontend']);
```

### Pod Template Management

#### getTemplate()
```php
public function getTemplate(): array
```

Returns the Pod template specification.

#### setTemplate()
```php
public function setTemplate(array $template): self
```

Sets the complete Pod template.

#### addContainer()
```php
public function addContainer(
    string $name, 
    string $image, 
    ?array $ports = null, 
    ?array $env = null
): self
```

Adds a container to the Pod template.

#### setContainerImage()
```php
public function setContainerImage(string $containerName, string $image): self
```

Updates container image in the Pod template.

#### setContainerResources()
```php
public function setContainerResources(string $containerName, array $resources): self
```

Sets resource requests and limits for a container.

### Update Strategy

#### setRollingUpdateStrategy()
```php
public function setRollingUpdateStrategy(?int $maxUnavailable = null, ?int $maxSurge = null): self
```

Configures rolling update parameters.

**Parameters:**
- `$maxUnavailable` - Maximum number of Pods that can be unavailable during update
- `$maxSurge` - Maximum number of Pods that can be created above desired replica count

**Example:**
```php
// Conservative strategy: only 1 Pod unavailable, allow 2 extra Pods
$deployment->setRollingUpdateStrategy(1, 2);

// Zero-downtime strategy: no Pods unavailable, allow 1 extra Pod
$deployment->setRollingUpdateStrategy(0, 1);
```

#### getUpdateStrategy()
```php
public function getUpdateStrategy(): array
```

Returns the current update strategy configuration.

### Health Checks

#### setReadinessProbe()
```php
public function setReadinessProbe(string $containerName, array $probe): self
```

Sets readiness probe for a container in the Pod template.

#### setLivenessProbe()
```php
public function setLivenessProbe(string $containerName, array $probe): self
```

Sets liveness probe for a container in the Pod template.

#### setStartupProbe()
```php
public function setStartupProbe(string $containerName, array $probe): self
```

Sets startup probe for slow-starting containers.

## Status Methods (Read-Only)

### getReplicas() vs Status Methods
```php
public function getReplicas(): int          // Desired replicas (spec)
public function getReadyReplicas(): int     // Currently ready replicas (status)
public function getAvailableReplicas(): int // Currently available replicas (status)
public function getUpdatedReplicas(): int   // Replicas with latest template (status)
```

### getConditions()
```php
public function getConditions(): array
```

Returns deployment conditions (Available, Progressing, ReplicaFailure).

**Example response:**
```php
[
    [
        'type' => 'Available',
        'status' => 'True',
        'lastTransitionTime' => '2023-08-12T10:30:00Z',
        'reason' => 'MinimumReplicasAvailable',
        'message' => 'Deployment has minimum availability.'
    ],
    [
        'type' => 'Progressing',
        'status' => 'True',
        'lastTransitionTime' => '2023-08-12T10:35:00Z',
        'reason' => 'NewReplicaSetAvailable',
        'message' => 'ReplicaSet "web-app-7d4f8c9b6" has successfully progressed.'
    ]
]
```

## Advanced Features

### Deployment Annotations

#### addRevisionHistoryAnnotation()
```php
public function addRevisionHistoryAnnotation(string $changeDescription): self
```

Adds annotation for tracking deployment changes.

#### setRevisionHistoryLimit()
```php
public function setRevisionHistoryLimit(int $limit): self
```

Sets number of old ReplicaSets to retain for rollback.

### Rollback Operations

#### getRevision()
```php
public function getRevision(): ?int
```

Returns current deployment revision number.

#### rollback()
```php
public function rollback(?int $revision = null): self
```

Initiates rollback to previous or specified revision.

## Usage Examples

### Basic Web Application Deployment
```php
$deployment = new Deployment();
$deployment->setName('web-app')
          ->setNamespace('production')
          ->setReplicas(3)
          ->setSelectorMatchLabels(['app' => 'web-app'])
          ->addContainer('web', 'nginx:1.21', [80])
          ->setContainerResources('web', [
              'requests' => ['cpu' => '100m', 'memory' => '128Mi'],
              'limits' => ['cpu' => '500m', 'memory' => '512Mi']
          ])
          ->setRollingUpdateStrategy(1, 1);

// Deploy
$client->create($deployment);
```

### API Server with Health Checks
```php
$deployment = new Deployment();
$deployment->setName('api-server')
          ->setNamespace('production')
          ->setReplicas(5)
          ->setSelectorMatchLabels(['app' => 'api-server'])
          ->addContainer('api', 'my-api:v2.0.0', [8080], [
              'DATABASE_URL' => 'postgresql://db:5432/myapp',
              'LOG_LEVEL' => 'info'
          ])
          ->setReadinessProbe('api', [
              'httpGet' => ['path' => '/health/ready', 'port' => 8080],
              'initialDelaySeconds' => 10,
              'periodSeconds' => 5,
              'failureThreshold' => 3
          ])
          ->setLivenessProbe('api', [
              'httpGet' => ['path' => '/health/live', 'port' => 8080],
              'initialDelaySeconds' => 30,
              'periodSeconds' => 10,
              'failureThreshold' => 3
          ]);
```

### Multi-Container Application
```php
$deployment = new Deployment();
$deployment->setName('microservices-app')
          ->setNamespace('production')
          ->setReplicas(3)
          ->setSelectorMatchLabels(['app' => 'microservices-app'])
          ->addContainer('api', 'my-api:v2.0.0', [8080])
          ->addContainer('worker', 'my-worker:v2.0.0')
          ->addContainer('sidecar', 'logging-agent:latest');

// Add shared volume
$template = $deployment->getTemplate();
$template['spec']['volumes'] = [
    ['name' => 'shared-logs', 'emptyDir' => []]
];
$deployment->setTemplate($template);
```

### Scaling Operations
```php
// Find existing deployment
$deployment = Deployment::find('web-app', 'production');

// Scale up for high traffic
$deployment->scaleUp(5); // Add 5 more replicas
$client->update($deployment);

// Scale to specific number
$deployment->scale(10);
$client->update($deployment);

// Scale down during low traffic
$deployment->scaleDown(3); // Remove 3 replicas
$client->update($deployment);
```

### Rolling Update with Image Change
```php
$deployment = Deployment::find('api-server', 'production');

// Update container image
$deployment->setContainerImage('api', 'my-api:v2.1.0');

// Add change annotation for tracking
$deployment->addRevisionHistoryAnnotation('Updated to v2.1.0 with bug fixes');

// Perform rolling update
$client->update($deployment);

// Monitor rollout status
while (!$deployment->isRolloutComplete()) {
    sleep(10);
    $deployment = Deployment::find('api-server', 'production');
    echo "Rollout status: {$deployment->getReadyReplicas()}/{$deployment->getReplicas()} ready\n";
}
```

### Blue-Green Deployment Pattern
```php
class BlueGreenDeployer
{
    public function deployNewVersion(string $appName, string $namespace, string $newImage): void
    {
        $currentDeployment = Deployment::find($appName, $namespace);
        
        // Create new deployment with "green" label
        $greenDeployment = clone $currentDeployment;
        $greenDeployment->setName($appName . '-green')
                       ->addLabel('color', 'green')
                       ->setSelectorMatchLabels([
                           'app' => $appName,
                           'color' => 'green'
                       ])
                       ->setContainerImage('app', $newImage);
        
        // Deploy green version
        $this->client->create($greenDeployment);
        
        // Wait for green deployment to be ready
        $this->waitForDeploymentReady($greenDeployment);
        
        // Switch service to green
        $service = Service::find($appName . '-service', $namespace);
        $service->setSelectorMatchLabels(['app' => $appName, 'color' => 'green']);
        $this->client->update($service);
        
        // Clean up blue deployment
        $this->client->delete($currentDeployment);
    }
}
```

## See Also

- [ReplicaSet](replica-set.md) - Lower-level Pod replication
- [StatefulSet](stateful-set.md) - For stateful applications
- [Pod](../core/v1/pod.md) - Basic compute unit
- [Service](../core/v1/service.md) - For exposing Deployments
- [HorizontalPodAutoscaler](../autoscaling/v2/horizontal-pod-autoscaler.md) - For automatic scaling
