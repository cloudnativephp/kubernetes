# Getting Started

This guide will help you get up and running with the Kubernetes PHP client library.

## Installation

Install via Composer:

```bash
composer require cloudnativephp/kubernetes
```

## Requirements

- PHP 8.4 or higher
- Kubernetes cluster access
- Valid kubeconfig or service account token

## Basic Setup

### 1. Client Configuration

#### Using kubeconfig
```php
use Kubernetes\Client\Client;

// Load from default kubeconfig location
$client = Client::fromKubeconfig();

// Load from specific kubeconfig file
$client = Client::fromKubeconfig('/path/to/kubeconfig');

// Load specific context
$client = Client::fromKubeconfig('/path/to/kubeconfig', 'my-context');
```

#### Using Service Account Token
```php
use Kubernetes\Client\Client;

$client = new Client(
    'https://kubernetes.default.svc',
    $token,
    '/var/run/secrets/kubernetes.io/serviceaccount/ca.crt'
);
```

#### Using Bearer Token
```php
use Kubernetes\Client\Client;

$client = new Client('https://my-cluster.example.com', $bearerToken);
```

### 2. Set Default Client

Set a default client to avoid passing it to every operation:

```php
use Kubernetes\Resource;

Resource::setDefaultClient($client);

// Now you can use resources without explicitly passing a client
$pods = Pod::all();
```

## First Steps

### Creating a Simple Pod

```php
use Kubernetes\API\Core\V1\Pod;

$pod = new Pod();
$pod->setName('hello-world')
    ->setNamespace('default')
    ->addContainer('app', 'nginx:latest', [80])
    ->addLabel('app', 'hello-world');

// Create the pod
$client->create($pod);

// Or if you've set a default client
$pod->save();
```

### Listing Resources

```php
use Kubernetes\API\Core\V1\Pod;
use Kubernetes\API\Apps\V1\Deployment;

// List all pods in default namespace
$pods = Pod::all('default');

// List all deployments across all namespaces
$deployments = Deployment::all();

// List with label selector
$pods = Pod::all('default', [
    'labelSelector' => 'app=hello-world'
]);
```

### Working with Existing Resources

```php
use Kubernetes\API\Core\V1\Pod;

// Find a specific pod
$pod = Pod::find('my-pod', 'default');

// Update the pod
$pod->addLabel('version', 'v2');
$pod->save();

// Delete the pod
$pod->delete();
```

## Configuration Patterns

### Environment-based Configuration

```php
// config/kubernetes.php
return [
    'default' => env('KUBE_CONTEXT', 'minikube'),
    'clusters' => [
        'minikube' => [
            'server' => 'https://127.0.0.1:8443',
            'token' => env('KUBE_TOKEN'),
        ],
        'production' => [
            'server' => env('KUBE_SERVER'),
            'token' => env('KUBE_TOKEN'),
            'ca_cert' => env('KUBE_CA_CERT_PATH'),
        ],
    ],
];

// Bootstrap
$config = require 'config/kubernetes.php';
$cluster = $config['clusters'][$config['default']];

$client = new Client(
    $cluster['server'],
    $cluster['token'],
    $cluster['ca_cert'] ?? null
);

Resource::setDefaultClient($client);
```

### Service Account Authentication (In-cluster)

```php
// For applications running inside Kubernetes
$serviceAccountPath = '/var/run/secrets/kubernetes.io/serviceaccount';

$client = new Client(
    'https://kubernetes.default.svc',
    file_get_contents($serviceAccountPath . '/token'),
    $serviceAccountPath . '/ca.crt'
);
```

## Error Handling

```php
use Kubernetes\Exceptions\ApiException;
use Kubernetes\Exceptions\ResourceNotFoundException;

try {
    $pod = Pod::find('non-existent', 'default');
} catch (ResourceNotFoundException $e) {
    echo "Pod not found: " . $e->getMessage();
} catch (ApiException $e) {
    echo "API error: " . $e->getMessage();
    echo "HTTP status: " . $e->getCode();
}
```

## Next Steps

- Read the [Architecture Guide](architecture.md) to understand the library structure
- Explore [Usage Examples](examples/README.md) for common patterns
- Check the [API Reference](api-reference/README.md) for detailed documentation
- Review [Best Practices](best-practices.md) for production deployments
