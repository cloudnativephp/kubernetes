# Service API Reference

The Service class provides stable network endpoints for accessing Pods, enabling service discovery and load balancing within the cluster.

## Class Definition

```php
namespace Kubernetes\API\Core\V1;

use Kubernetes\Traits\IsNamespacedResource;

class Service extends AbstractResource
{
    use IsNamespacedResource;
    
    public function getKind(): string; // Returns 'Service'
    public function getApiVersion(): string; // Inherited: 'v1'
}
```

## Core Methods

### Service Type Configuration

#### setType()
```php
public function setType(string $type): self
```

Sets the Service type.

**Parameters:**
- `$type` - Service type: 'ClusterIP', 'NodePort', 'LoadBalancer', 'ExternalName'

**Example:**
```php
$service->setType('LoadBalancer'); // Expose externally
$service->setType('ClusterIP');    // Internal cluster access only
```

#### getType()
```php
public function getType(): string
```

Returns the Service type (default: 'ClusterIP').

### Port Configuration

#### addPort()
```php
public function addPort(string $name, int $port, ?int $targetPort = null, string $protocol = 'TCP'): self
```

Adds a port to the Service.

**Parameters:**
- `$name` - Port name (must be unique)
- `$port` - Service port number
- `$targetPort` - Target port on Pods (defaults to service port)
- `$protocol` - Protocol: 'TCP', 'UDP', 'SCTP'

**Example:**
```php
$service->addPort('http', 80, 8080)
        ->addPort('https', 443, 8443)
        ->addPort('metrics', 9090);
```

#### setPorts()
```php
public function setPorts(array $ports): self
```

Sets all Service ports at once.

#### getPorts()
```php
public function getPorts(): array
```

Returns array of port configurations.

### Selector Configuration

#### setSelectorMatchLabels()
```php
public function setSelectorMatchLabels(array $labels): self
```

Sets Pod selector labels for traffic routing.

**Example:**
```php
$service->setSelectorMatchLabels([
    'app' => 'web-server',
    'tier' => 'frontend'
]);
```

#### getSelector()
```php
public function getSelector(): array
```

Returns the current Pod selector.

### Advanced Configuration

#### setClusterIP()
```php
public function setClusterIP(string $clusterIP): self
```

Sets specific cluster IP address.

**Special values:**
- `'None'` - Creates headless Service
- IP address - Assigns specific cluster IP

#### setExternalName()
```php
public function setExternalName(string $externalName): self
```

Sets external DNS name for ExternalName Services.

#### setSessionAffinity()
```php
public function setSessionAffinity(string $affinity): self
```

Sets session affinity policy.

**Parameters:**
- `$affinity` - 'None' or 'ClientIP'

#### setLoadBalancerIP()
```php
public function setLoadBalancerIP(string $ip): self
```

Sets specific load balancer IP (cloud provider dependent).

#### addExternalIP()
```php
public function addExternalIP(string $ip): self
```

Adds external IP address for direct access.

## Helper Methods

### Headless Service Creation
```php
public function makeHeadless(): self
```

Converts Service to headless (sets clusterIP to 'None').

### Common Service Patterns
```php
public function exposeDeployment(string $deploymentName, int $port, ?int $targetPort = null): self
```

Quick configuration for exposing a Deployment.

### Health Check Configuration
```php
public function setHealthCheckNodePort(int $port): self
```

Sets health check node port for LoadBalancer Services.

## Status Methods (Read-Only)

### getLoadBalancerIngress()
```php
public function getLoadBalancerIngress(): array
```

Returns load balancer ingress points (for LoadBalancer Services).

### getClusterIPs()
```php
public function getClusterIPs(): array
```

Returns assigned cluster IP addresses.

## Usage Examples

### Basic ClusterIP Service
```php
$service = new Service();
$service->setName('web-service')
        ->setNamespace('production')
        ->setType('ClusterIP')
        ->setSelectorMatchLabels(['app' => 'web-server'])
        ->addPort('http', 80, 8080);

$client->create($service);
```

### LoadBalancer Service with Multiple Ports
```php
$service = new Service();
$service->setName('api-service')
        ->setNamespace('production')
        ->setType('LoadBalancer')
        ->setSelectorMatchLabels(['app' => 'api-server'])
        ->addPort('http', 80, 8080)
        ->addPort('https', 443, 8443)
        ->addPort('metrics', 9090)
        ->setSessionAffinity('ClientIP');

// Optional: Set specific load balancer IP
$service->setLoadBalancerIP('192.168.1.100');

$client->create($service);
```

### Headless Service for StatefulSet
```php
$service = new Service();
$service->setName('database-headless')
        ->setNamespace('database')
        ->makeHeadless() // Sets clusterIP to None
        ->setSelectorMatchLabels(['app' => 'postgres'])
        ->addPort('postgres', 5432);

// Used by StatefulSet for stable network identities
$client->create($service);
```

### ExternalName Service
```php
$service = new Service();
$service->setName('external-database')
        ->setNamespace('production')
        ->setType('ExternalName')
        ->setExternalName('db.example.com');

// No selector needed for ExternalName services
$client->create($service);
```

### NodePort Service
```php
$service = new Service();
$service->setName('admin-console')
        ->setNamespace('kube-system')
        ->setType('NodePort')
        ->setSelectorMatchLabels(['app' => 'admin-console'])
        ->addPort('http', 80, 8080); // NodePort auto-assigned

$client->create($service);
```

### Service with External IPs
```php
$service = new Service();
$service->setName('legacy-service')
        ->setNamespace('default')
        ->setSelectorMatchLabels(['app' => 'legacy-app'])
        ->addPort('http', 80)
        ->addExternalIP('192.168.1.50')
        ->addExternalIP('192.168.1.51');
```

## Service Discovery Patterns

### Service-to-Service Communication
```php
// Services can be accessed via DNS:
// service-name.namespace.svc.cluster.local
// or simply: service-name (within same namespace)

$webService = new Service();
$webService->setName('web-frontend')
          ->setSelectorMatchLabels(['app' => 'web'])
          ->addPort('http', 80);

$apiService = new Service();
$apiService->setName('api-backend')
          ->setSelectorMatchLabels(['app' => 'api'])
          ->addPort('http', 8080);

// Web frontend can call: http://api-backend:8080/api/v1/users
```

### Cross-Namespace Access
```php
// Accessing service in different namespace
$databaseService = new Service();
$databaseService->setName('postgres')
               ->setNamespace('database')
               ->setSelectorMatchLabels(['app' => 'postgres'])
               ->addPort('postgres', 5432);

// Access from other namespaces: postgres.database.svc.cluster.local:5432
```

## Monitoring and Observability

### Prometheus Integration
```php
$service = new Service();
$service->setName('app-service')
        ->setSelectorMatchLabels(['app' => 'my-app'])
        ->addPort('http', 8080)
        ->addPort('metrics', 9090) // Prometheus metrics port
        ->addAnnotation('prometheus.io/scrape', 'true')
        ->addAnnotation('prometheus.io/port', '9090')
        ->addAnnotation('prometheus.io/path', '/metrics');
```

### Service Mesh Integration
```php
$service = new Service();
$service->setName('microservice')
        ->setSelectorMatchLabels(['app' => 'microservice'])
        ->addPort('http', 8080)
        ->addPort('grpc', 9090)
        ->addAnnotation('sidecar.istio.io/inject', 'true')
        ->addLabel('app', 'microservice')
        ->addLabel('version', 'v1');
```

## Troubleshooting

### Service Endpoint Validation
```php
// Check if Service has endpoints
$service = Service::find('my-service', 'default');
$endpoints = Endpoints::find('my-service', 'default');

if (empty($endpoints->getSubsets())) {
    echo "Service has no endpoints - check Pod labels\n";
    
    // Verify selector matches Pod labels
    $selector = $service->getSelector();
    $pods = Pod::all('default', [
        'labelSelector' => http_build_query($selector, '', ',')
    ]);
    
    echo "Matching Pods: " . count($pods) . "\n";
}
```

### Service Connectivity Testing
```php
// Create debug Pod to test service connectivity
$debugPod = new Pod();
$debugPod->setName('network-debug')
         ->setNamespace('default')
         ->addContainer('debug', 'nicolaka/netshoot')
         ->setRestartPolicy('Never');

$client->create($debugPod);

// Use kubectl exec to test:
// kubectl exec network-debug -- nslookup my-service
// kubectl exec network-debug -- curl my-service:80
```

## See Also

- [Pod](pod.md) - Service targets
- [Deployment](../apps/v1/deployment.md) - Workload management
- [Endpoints](endpoints.md) - Service endpoint details
- [Ingress](../networking/v1/ingress.md) - HTTP/HTTPS routing
- [NetworkPolicy](../networking/v1/network-policy.md) - Traffic control
