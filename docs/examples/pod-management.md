# Pod Management Examples

Complete examples for creating, managing, and monitoring Pods using the Kubernetes PHP client library.

## Basic Pod Creation

### Simple Pod
```php
use Kubernetes\API\Core\V1\Pod;

$pod = new Pod();
$pod->setName('hello-world')
    ->setNamespace('default')
    ->addContainer('app', 'nginx:latest', [80])
    ->addLabel('app', 'hello-world')
    ->addLabel('version', 'v1.0.0');

// Deploy the pod
$client->create($pod);
```

### Pod with Environment Variables
```php
$pod = new Pod();
$pod->setName('app-with-env')
    ->setNamespace('production')
    ->addContainer('app', 'my-app:latest', [8080], [
        'DATABASE_URL' => 'postgresql://db:5432/myapp',
        'REDIS_URL' => 'redis://redis:6379',
        'LOG_LEVEL' => 'info'
    ])
    ->addLabel('app', 'web-server')
    ->addLabel('tier', 'frontend');
```

### Pod with Resource Limits
```php
$pod = new Pod();
$pod->setName('resource-limited-pod')
    ->setNamespace('default')
    ->addContainer('app', 'cpu-intensive-app:latest')
    ->setContainerResources('app', [
        'requests' => [
            'cpu' => '100m',
            'memory' => '128Mi'
        ],
        'limits' => [
            'cpu' => '500m',
            'memory' => '512Mi'
        ]
    ]);
```

## Advanced Pod Configurations

### Multi-Container Pod
```php
$pod = new Pod();
$pod->setName('multi-container-app')
    ->setNamespace('default')
    ->addContainer('web', 'nginx:latest', [80])
    ->addContainer('api', 'my-api:latest', [8080])
    ->addContainer('worker', 'my-worker:latest')
    ->addLabel('app', 'microservices-pod');

// Configure shared volume
$pod->addVolume('shared-data', ['emptyDir' => []])
    ->addVolumeMount('web', 'shared-data', '/usr/share/nginx/html')
    ->addVolumeMount('api', 'shared-data', '/app/public');
```

### Pod with Init Containers
```php
$pod = new Pod();
$pod->setName('app-with-init')
    ->setNamespace('default')
    ->addInitContainer('setup', 'setup-image:latest', [], [
        'SETUP_MODE' => 'initialize'
    ])
    ->addContainer('app', 'main-app:latest', [8080])
    ->addLabel('app', 'initialized-app');

// Add shared volume for init container data
$pod->addVolume('init-data', ['emptyDir' => []])
    ->addVolumeMount('setup', 'init-data', '/setup-output')
    ->addVolumeMount('app', 'init-data', '/app/data');
```

### Pod with ConfigMap and Secret
```php
// First create ConfigMap and Secret
$configMap = new ConfigMap();
$configMap->setName('app-config')
          ->setNamespace('default')
          ->setData([
              'database.conf' => 'host=localhost\nport=5432',
              'app.properties' => 'debug=false\nlog_level=info'
          ]);

$secret = new Secret();
$secret->setName('app-secrets')
       ->setNamespace('default')
       ->setStringData([
           'db-password' => 'super-secret-password',
           'api-key' => 'your-api-key-here'
       ]);

// Create the resources
$client->create($configMap);
$client->create($secret);

// Now create pod that uses them
$pod = new Pod();
$pod->setName('configured-app')
    ->setNamespace('default')
    ->addContainer('app', 'my-app:latest', [8080])
    ->addConfigMapVolume('config', 'app-config', '/etc/config')
    ->addSecretVolume('secrets', 'app-secrets', '/etc/secrets')
    ->addEnvFromConfigMap('app-config')
    ->addEnvFromSecret('app-secrets');
```

## Pod Lifecycle Management

### Health Checks and Probes
```php
$pod = new Pod();
$pod->setName('health-checked-app')
    ->setNamespace('default')
    ->addContainer('app', 'health-app:latest', [8080]);

// Add readiness probe
$pod->setReadinessProbe('app', [
    'httpGet' => [
        'path' => '/health/ready',
        'port' => 8080
    ],
    'initialDelaySeconds' => 10,
    'periodSeconds' => 5,
    'timeoutSeconds' => 3,
    'failureThreshold' => 3
]);

// Add liveness probe
$pod->setLivenessProbe('app', [
    'httpGet' => [
        'path' => '/health/live',
        'port' => 8080
    ],
    'initialDelaySeconds' => 30,
    'periodSeconds' => 10,
    'timeoutSeconds' => 5,
    'failureThreshold' => 3
]);

// Add startup probe for slow-starting applications
$pod->setStartupProbe('app', [
    'httpGet' => [
        'path' => '/health/startup',
        'port' => 8080
    ],
    'initialDelaySeconds' => 0,
    'periodSeconds' => 10,
    'timeoutSeconds' => 3,
    'failureThreshold' => 30
]);
```

### Pod Security Context
```php
$pod = new Pod();
$pod->setName('secure-app')
    ->setNamespace('default')
    ->addContainer('app', 'secure-app:latest', [8080])
    ->setSecurityContext([
        'runAsNonRoot' => true,
        'runAsUser' => 1000,
        'runAsGroup' => 1000,
        'fsGroup' => 1000,
        'seccompProfile' => [
            'type' => 'RuntimeDefault'
        ]
    ])
    ->setContainerSecurityContext('app', [
        'allowPrivilegeEscalation' => false,
        'readOnlyRootFilesystem' => true,
        'capabilities' => [
            'drop' => ['ALL']
        ]
    ]);
```

## Pod Monitoring and Debugging

### Getting Pod Status
```php
use Kubernetes\API\Core\V1\Pod;

// Find and inspect a pod
$pod = Pod::find('my-app-pod', 'default');

echo "Pod Phase: " . $pod->getPhase() . "\n";
echo "Pod IP: " . $pod->getPodIP() . "\n";
echo "Node: " . $pod->getNodeName() . "\n";

// Check container statuses
$containerStatuses = $pod->getContainerStatuses();
foreach ($containerStatuses as $status) {
    echo "Container: " . $status['name'] . "\n";
    echo "Ready: " . ($status['ready'] ? 'Yes' : 'No') . "\n";
    echo "Restart Count: " . $status['restartCount'] . "\n";
    
    if (isset($status['state']['running'])) {
        echo "Status: Running since " . $status['state']['running']['startedAt'] . "\n";
    } elseif (isset($status['state']['waiting'])) {
        echo "Status: Waiting - " . $status['state']['waiting']['reason'] . "\n";
    }
}
```

### Pod Logs (requires log streaming implementation)
```php
// Get pod logs
$logOptions = [
    'container' => 'app',
    'tailLines' => 100,
    'timestamps' => true
];

$logs = $client->getLogs($pod, $logOptions);
echo $logs;

// Follow logs (streaming)
$client->followLogs($pod, $logOptions, function($line) {
    echo "[" . date('Y-m-d H:i:s') . "] " . $line . "\n";
});
```

## Pod Patterns

### Sidecar Pattern
```php
$pod = new Pod();
$pod->setName('app-with-sidecar')
    ->setNamespace('default')
    ->addContainer('app', 'main-app:latest', [8080])
    ->addContainer('logger', 'fluentd:latest')
    ->addContainer('monitor', 'prometheus-exporter:latest', [9090])
    ->addLabel('pattern', 'sidecar');

// Shared log volume
$pod->addVolume('logs', ['emptyDir' => []])
    ->addVolumeMount('app', 'logs', '/var/log/app')
    ->addVolumeMount('logger', 'logs', '/var/log/app');
```

### Ambassador Pattern
```php
$pod = new Pod();
$pod->setName('app-with-ambassador')
    ->setNamespace('default')
    ->addContainer('app', 'my-app:latest')
    ->addContainer('proxy', 'nginx-proxy:latest', [80])
    ->addLabel('pattern', 'ambassador');

// App connects to localhost, proxy handles external communication
$pod->setContainerEnv('app', [
    'PROXY_URL' => 'http://localhost:80'
]);
```

### Adapter Pattern
```php
$pod = new Pod();
$pod->setName('legacy-app-adapted')
    ->setNamespace('default')
    ->addContainer('legacy-app', 'old-app:latest', [8080])
    ->addContainer('adapter', 'api-adapter:latest', [9090])
    ->addLabel('pattern', 'adapter');

// Adapter translates between legacy app and modern APIs
$pod->addVolume('shared-data', ['emptyDir' => []])
    ->addVolumeMount('legacy-app', 'shared-data', '/data')
    ->addVolumeMount('adapter', 'shared-data', '/data');
```

## Troubleshooting Pods

### Common Issues and Solutions

#### Pod Stuck in Pending
```php
// Check pod events for scheduling issues
$events = $client->getEvents('default', [
    'fieldSelector' => 'involvedObject.name=' . $pod->getName()
]);

foreach ($events as $event) {
    if ($event['type'] === 'Warning') {
        echo "Warning: " . $event['message'] . "\n";
    }
}

// Check node resources
$nodes = Node::all();
foreach ($nodes as $node) {
    $allocatable = $node->getAllocatable();
    echo "Node: " . $node->getName() . "\n";
    echo "CPU: " . $allocatable['cpu'] . "\n";
    echo "Memory: " . $allocatable['memory'] . "\n";
}
```

#### Pod CrashLoopBackOff
```php
// Check container exit codes and restart counts
$pod = Pod::find('problematic-pod', 'default');
$containerStatuses = $pod->getContainerStatuses();

foreach ($containerStatuses as $status) {
    if ($status['restartCount'] > 0) {
        echo "Container " . $status['name'] . " has restarted " . $status['restartCount'] . " times\n";
        
        if (isset($status['lastState']['terminated'])) {
            $terminated = $status['lastState']['terminated'];
            echo "Last exit code: " . $terminated['exitCode'] . "\n";
            echo "Reason: " . $terminated['reason'] . "\n";
        }
    }
}
```

#### Image Pull Errors
```php
// Check for image pull secrets and repository access
$pod = Pod::find('image-pull-error-pod', 'default');
$containerStatuses = $pod->getContainerStatuses();

foreach ($containerStatuses as $status) {
    if (isset($status['state']['waiting']) && 
        str_contains($status['state']['waiting']['reason'], 'ImagePull')) {
        
        echo "Image pull error for container: " . $status['name'] . "\n";
        echo "Message: " . $status['state']['waiting']['message'] . "\n";
        
        // Check if image pull secret is needed
        $containers = $pod->getContainers();
        foreach ($containers as $container) {
            if ($container['name'] === $status['name']) {
                echo "Image: " . $container['image'] . "\n";
            }
        }
    }
}
```

This comprehensive guide covers the most common Pod management scenarios you'll encounter in production Kubernetes environments.
