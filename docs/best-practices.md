# Best Practices

Production-ready guidelines for using the Kubernetes PHP client library effectively and securely.

## Security Best Practices

### Authentication and Authorization

#### Service Account Best Practices
```php
// Create dedicated service accounts for applications
$serviceAccount = new ServiceAccount();
$serviceAccount->setName('app-service-account')
              ->setNamespace('production')
              ->addLabel('app', 'my-application');

// Bind minimal required permissions
$role = new Role();
$role->setName('app-role')
    ->setNamespace('production')
    ->addRule(['pods'], ['get', 'list', 'watch'])
    ->addRule(['configmaps'], ['get', 'list']);

$roleBinding = new RoleBinding();
$roleBinding->setName('app-role-binding')
           ->setNamespace('production')
           ->bindToRole('app-role')
           ->addSubject('ServiceAccount', 'app-service-account', 'production');
```

#### Client Security Configuration
```php
// Use environment variables for sensitive data
$client = new Client(
    $_ENV['KUBERNETES_API_SERVER'],
    $_ENV['KUBERNETES_TOKEN'],
    $_ENV['KUBERNETES_CA_CERT_PATH']
);

// Never hardcode credentials
// ❌ BAD
$client = new Client('https://k8s.example.com', 'hardcoded-token');

// ✅ GOOD
$client = new Client(
    getenv('KUBERNETES_API_SERVER'),
    getenv('KUBERNETES_TOKEN')
);
```

### Resource Security

#### Pod Security Standards
```php
$deployment = new Deployment();
$deployment->setName('secure-app')
          ->setNamespace('production')
          ->addContainer('app', 'my-app:latest')
          ->setSecurityContext([
              'runAsNonRoot' => true,
              'runAsUser' => 1000,
              'runAsGroup' => 1000,
              'fsGroup' => 1000,
              'seccompProfile' => ['type' => 'RuntimeDefault']
          ])
          ->setContainerSecurityContext('app', [
              'allowPrivilegeEscalation' => false,
              'readOnlyRootFilesystem' => true,
              'capabilities' => ['drop' => ['ALL']]
          ]);
```

#### Secret Management
```php
// Use Kubernetes secrets for sensitive data
$secret = new Secret();
$secret->setName('app-secrets')
       ->setNamespace('production')
       ->setType('Opaque')
       ->setStringData([
           'database-password' => $securePassword,
           'api-key' => $apiKey
       ]);

// Reference secrets in deployments
$deployment->addEnvFromSecret('app-secrets');

// Never store secrets in ConfigMaps
// ❌ BAD
$configMap->setData(['password' => 'secret123']);

// ✅ GOOD - use secrets instead
$secret->setStringData(['password' => 'secret123']);
```

## Performance Best Practices

### Resource Optimization

#### Resource Limits and Requests
```php
// Always set resource requests and limits
$deployment = new Deployment();
$deployment->addContainer('app', 'my-app:latest')
          ->setContainerResources('app', [
              'requests' => [
                  'cpu' => '100m',      // 0.1 CPU cores
                  'memory' => '128Mi'   // 128 MiB
              ],
              'limits' => [
                  'cpu' => '500m',      // 0.5 CPU cores
                  'memory' => '512Mi'   // 512 MiB
              ]
          ]);

// Use appropriate resource ratios
// Memory limit should be 2-4x the request
// CPU limit should be 2-5x the request (or unlimited for burstable workloads)
```

#### Efficient List Operations
```php
// Use label selectors to reduce data transfer
$pods = Pod::all('production', [
    'labelSelector' => 'app=web-server,tier=frontend'
]);

// Use field selectors for specific queries
$runningPods = Pod::all('production', [
    'fieldSelector' => 'status.phase=Running'
]);

// Limit results for large clusters
$recentPods = Pod::all('production', [
    'limit' => 100,
    'labelSelector' => 'app=my-app'
]);
```

### Client Connection Management

#### Connection Pooling and Reuse
```php
class KubernetesClientManager
{
    private static ?ClientInterface $instance = null;
    
    public static function getInstance(): ClientInterface
    {
        if (self::$instance === null) {
            self::$instance = new Client(
                $_ENV['KUBERNETES_API_SERVER'],
                $_ENV['KUBERNETES_TOKEN']
            );
            
            // Set as default to avoid passing client everywhere
            Resource::setDefaultClient(self::$instance);
        }
        
        return self::$instance;
    }
}

// Use singleton pattern for client instances
$client = KubernetesClientManager::getInstance();
```

#### Batch Operations
```php
class BatchResourceManager
{
    private ClientInterface $client;
    private array $pendingCreates = [];
    private array $pendingUpdates = [];
    
    public function queueCreate(ResourceInterface $resource): void
    {
        $this->pendingCreates[] = $resource;
    }
    
    public function queueUpdate(ResourceInterface $resource): void
    {
        $this->pendingUpdates[] = $resource;
    }
    
    public function flush(): array
    {
        $results = [];
        
        // Process creates
        foreach ($this->pendingCreates as $resource) {
            try {
                $results[] = $this->client->create($resource);
            } catch (ApiException $e) {
                // Log error but continue with other resources
                error_log("Failed to create {$resource->getKind()}: " . $e->getMessage());
            }
        }
        
        // Process updates
        foreach ($this->pendingUpdates as $resource) {
            try {
                $results[] = $this->client->update($resource);
            } catch (ApiException $e) {
                error_log("Failed to update {$resource->getKind()}: " . $e->getMessage());
            }
        }
        
        $this->pendingCreates = [];
        $this->pendingUpdates = [];
        
        return $results;
    }
}
```

## Reliability Best Practices

### Error Handling and Retry Logic

#### Robust Error Handling
```php
class RobustKubernetesClient
{
    private ClientInterface $client;
    private LoggerInterface $logger;
    
    public function createWithRetry(
        ResourceInterface $resource, 
        int $maxRetries = 3,
        int $baseDelay = 1
    ): ResourceInterface {
        $lastException = null;
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                return $this->client->create($resource);
            } catch (ApiException $e) {
                $lastException = $e;
                
                // Don't retry client errors (4xx)
                if ($e->getCode() >= 400 && $e->getCode() < 500) {
                    throw $e;
                }
                
                if ($attempt < $maxRetries) {
                    $delay = $baseDelay * pow(2, $attempt - 1); // Exponential backoff
                    $this->logger->warning("Kubernetes API call failed, retrying in {$delay}s", [
                        'attempt' => $attempt,
                        'error' => $e->getMessage()
                    ]);
                    sleep($delay);
                }
            }
        }
        
        throw $lastException;
    }
}
```

#### Circuit Breaker Pattern
```php
class KubernetesCircuitBreaker
{
    private int $failureCount = 0;
    private int $failureThreshold = 5;
    private int $recoveryTimeout = 60;
    private ?int $lastFailureTime = null;
    private string $state = 'closed'; // closed, open, half-open
    
    public function call(callable $operation)
    {
        if ($this->state === 'open') {
            if (time() - $this->lastFailureTime > $this->recoveryTimeout) {
                $this->state = 'half-open';
                $this->failureCount = 0;
            } else {
                throw new RuntimeException('Circuit breaker is open');
            }
        }
        
        try {
            $result = $operation();
            
            if ($this->state === 'half-open') {
                $this->state = 'closed';
                $this->failureCount = 0;
            }
            
            return $result;
        } catch (Exception $e) {
            $this->failureCount++;
            $this->lastFailureTime = time();
            
            if ($this->failureCount >= $this->failureThreshold) {
                $this->state = 'open';
            }
            
            throw $e;
        }
    }
}
```

### Health Checks and Monitoring

#### Application Health Checks
```php
// Comprehensive health check configuration
$deployment = new Deployment();
$deployment->setName('robust-app')
          ->addContainer('app', 'my-app:latest', [8080]);

// Startup probe - for slow-starting applications
$deployment->setStartupProbe('app', [
    'httpGet' => [
        'path' => '/health/startup',
        'port' => 8080,
        'httpHeaders' => [['name' => 'Custom-Check', 'value' => 'startup']]
    ],
    'initialDelaySeconds' => 10,
    'periodSeconds' => 10,
    'timeoutSeconds' => 5,
    'failureThreshold' => 30, // Allow 5 minutes for startup
    'successThreshold' => 1
]);

// Readiness probe - traffic routing
$deployment->setReadinessProbe('app', [
    'httpGet' => [
        'path' => '/health/ready',
        'port' => 8080
    ],
    'initialDelaySeconds' => 5,
    'periodSeconds' => 5,
    'timeoutSeconds' => 3,
    'failureThreshold' => 3,
    'successThreshold' => 1
]);

// Liveness probe - restart unhealthy containers
$deployment->setLivenessProbe('app', [
    'httpGet' => [
        'path' => '/health/live',
        'port' => 8080
    ],
    'initialDelaySeconds' => 30,
    'periodSeconds' => 10,
    'timeoutSeconds' => 5,
    'failureThreshold' => 3,
    'successThreshold' => 1
]);
```

## Scalability Best Practices

### Horizontal Scaling

#### Auto-scaling Configuration
```php
use Kubernetes\API\Autoscaling\V2\HorizontalPodAutoscaler;

$deployment = new Deployment();
$deployment->setName('scalable-web-app')
          ->setReplicas(3) // Minimum replicas
          ->addContainer('web', 'my-web-app:latest')
          ->setContainerResources('web', [
              'requests' => ['cpu' => '100m', 'memory' => '128Mi'],
              'limits' => ['cpu' => '1', 'memory' => '512Mi']
          ]);

$hpa = new HorizontalPodAutoscaler();
$hpa->setName('web-app-hpa')
   ->setTargetRef('Deployment', 'scalable-web-app')
   ->setMinReplicas(3)
   ->setMaxReplicas(50)
   ->addCpuMetric(70)     // Scale when CPU > 70%
   ->addMemoryMetric(80)  // Scale when memory > 80%
   ->addCustomMetric('requests_per_second', 'Pods', '100'); // Custom metric
```

#### Rolling Update Strategy
```php
// Safe rolling update configuration
$deployment->setRollingUpdateStrategy(
    maxUnavailable: 1,    // Only 1 pod unavailable at a time
    maxSurge: 2           // Allow 2 extra pods during update
);

// For critical applications, use more conservative settings
$deployment->setRollingUpdateStrategy(
    maxUnavailable: 0,    // No downtime updates
    maxSurge: 1           // Slower but safer updates
);
```

## Development Best Practices

### Code Organization

#### Resource Factory Pattern
```php
class KubernetesResourceFactory
{
    public static function createStandardWebApp(
        string $name,
        string $namespace,
        string $image,
        array $env = [],
        int $replicas = 3
    ): array {
        $deployment = new Deployment();
        $deployment->setName($name)
                  ->setNamespace($namespace)
                  ->setReplicas($replicas)
                  ->setSelectorMatchLabels(['app' => $name])
                  ->addContainer('app', $image, [8080], $env)
                  ->setContainerResources('app', [
                      'requests' => ['cpu' => '100m', 'memory' => '128Mi'],
                      'limits' => ['cpu' => '500m', 'memory' => '512Mi']
                  ])
                  ->setRollingUpdateStrategy(1, 1)
                  ->addStandardLabels($name);

        $service = new Service();
        $service->setName($name . '-service')
               ->setNamespace($namespace)
               ->setType('ClusterIP')
               ->setSelectorMatchLabels(['app' => $name])
               ->addPort('http', 80, 8080)
               ->addStandardLabels($name);

        return [$deployment, $service];
    }
    
    private static function addStandardLabels(string $appName): array
    {
        return [
            'app' => $appName,
            'version' => 'v1.0.0',
            'component' => 'web',
            'part-of' => 'application-stack',
            'managed-by' => 'kubernetes-php-client'
        ];
    }
}
```

#### Configuration Management
```php
class KubernetesConfig
{
    public static function fromEnvironment(): array
    {
        return [
            'server' => self::getRequiredEnv('KUBERNETES_API_SERVER'),
            'token' => self::getRequiredEnv('KUBERNETES_TOKEN'),
            'ca_cert' => getenv('KUBERNETES_CA_CERT_PATH') ?: null,
            'namespace' => getenv('KUBERNETES_NAMESPACE') ?: 'default',
            'timeout' => (int)(getenv('KUBERNETES_TIMEOUT') ?: 30),
        ];
    }
    
    private static function getRequiredEnv(string $key): string
    {
        $value = getenv($key);
        if ($value === false) {
            throw new InvalidArgumentException("Required environment variable {$key} not set");
        }
        return $value;
    }
}
```

### Testing Best Practices

#### Unit Testing Resources
```php
// Use Pest for testing
it('can create a deployment with proper configuration', function (): void {
    $deployment = new Deployment();
    $deployment->setName('test-app')
              ->setNamespace('test')
              ->setReplicas(3)
              ->addContainer('app', 'nginx:latest', [80]);
    
    expect($deployment->getName())->toBe('test-app');
    expect($deployment->getNamespace())->toBe('test');
    expect($deployment->getReplicas())->toBe(3);
    
    $containers = $deployment->getContainers();
    expect($containers)->toHaveCount(1);
    expect($containers[0]['name'])->toBe('app');
    expect($containers[0]['image'])->toBe('nginx:latest');
});

// Test method chaining
it('supports method chaining', function (): void {
    $pod = new Pod();
    $result = $pod->setName('test')
                  ->setNamespace('default')
                  ->addLabel('app', 'test');
    
    expect($result)->toBe($pod); // Should return self
});
```

#### Integration Testing
```php
class KubernetesIntegrationTest extends TestCase
{
    private ClientInterface $client;
    private string $testNamespace = 'test-namespace';
    
    protected function setUp(): void
    {
        $this->client = new Client(
            $_ENV['TEST_KUBERNETES_API_SERVER'],
            $_ENV['TEST_KUBERNETES_TOKEN']
        );
        
        // Create test namespace
        $namespace = new KubernetesNamespace();
        $namespace->setName($this->testNamespace);
        $this->client->create($namespace);
    }
    
    protected function tearDown(): void
    {
        // Clean up test resources
        $namespace = KubernetesNamespace::find($this->testNamespace);
        $this->client->delete($namespace);
    }
    
    public function testPodLifecycle(): void
    {
        $pod = new Pod();
        $pod->setName('test-pod')
            ->setNamespace($this->testNamespace)
            ->addContainer('nginx', 'nginx:latest');
        
        // Create
        $created = $this->client->create($pod);
        $this->assertEquals('test-pod', $created->getName());
        
        // Read
        $found = Pod::find('test-pod', $this->testNamespace);
        $this->assertEquals('test-pod', $found->getName());
        
        // Update
        $found->addLabel('updated', 'true');
        $updated = $this->client->update($found);
        $this->assertEquals('true', $updated->getLabel('updated'));
        
        // Delete
        $deleted = $this->client->delete($updated);
        $this->assertTrue($deleted);
    }
}
```

This comprehensive best practices guide ensures your Kubernetes PHP applications are secure, performant, reliable, and maintainable in production environments.
