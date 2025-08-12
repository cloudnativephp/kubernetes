# Kubernetes PHP Client Library

[![PHP Version Require](http://poser.pugx.org/cloudnativephp/kubernetes/require/php)](https://packagist.org/packages/cloudnativephp/kubernetes)
[![Latest Stable Version](http://poser.pugx.org/cloudnativephp/kubernetes/v)](https://packagist.org/packages/cloudnativephp/kubernetes)
[![License](http://poser.pugx.org/cloudnativephp/kubernetes/license)](https://packagist.org/packages/cloudnativephp/kubernetes)

A modern, type-safe PHP client library for the Kubernetes API. Built with PHP 8.4+ and designed to provide an intuitive, object-oriented interface for managing Kubernetes resources - similar to Laravel's Eloquent ORM but specifically tailored for Kubernetes.

## 🌟 Features

- **🚀 Modern PHP 8.4+** - Leverages the latest PHP features including strict types
- **🔒 Type-safe** - Complete type hints and PHPStan Level 8 compliance  
- **🏗️ Resource-oriented** - Each Kubernetes resource is a dedicated PHP class
- **🎯 Comprehensive API Coverage** - Supports all major Kubernetes API groups
- **⚡ Fluent Interface** - Chainable methods for elegant resource configuration
- **🧪 Battle-tested** - 308+ tests with comprehensive coverage
- **📝 Fully Documented** - Complete PHPDoc coverage with examples
- **🔧 Developer Experience** - Includes formatting, linting, and static analysis tools

## 📋 Requirements

- **PHP 8.4** or higher
- **Composer** for dependency management
- **Kubernetes cluster** (for actual API operations)

## 📦 Installation

Install via Composer:

```bash
composer require cloudnativephp/kubernetes
```

## 🚀 Quick Start

### Basic Resource Creation

```php
<?php

use Kubernetes\API\Core\V1\Pod;
use Kubernetes\API\Core\V1\Service;

// Create a Pod
$pod = new Pod();
$pod->setName('my-app')
    ->setNamespace('default')
    ->addLabel('app', 'my-app')
    ->addContainer([
        'name' => 'app',
        'image' => 'nginx:latest',
        'ports' => [['containerPort' => 80]]
    ])
    ->setRestartPolicy('Always');

// Create a Service
$service = new Service();
$service->setName('my-app-service')
        ->setNamespace('default')
        ->setType('ClusterIP')
        ->setSelectorMatchLabels(['app' => 'my-app'])
        ->addPort(80, 80, 'TCP');
```

### Working with Deployments

```php
use Kubernetes\API\Apps\V1\Deployment;

$deployment = new Deployment();
$deployment->setName('web-app')
           ->setNamespace('production')
           ->setReplicas(3)
           ->setSelectorMatchLabels(['app' => 'web'])
           ->addContainer('web', 'nginx:1.21', [80])
           ->setRollingUpdateStrategy(1, 1);

// Convert to array for API submission
$deploymentManifest = $deployment->toArray();
```

### Configuration Management

```php
use Kubernetes\API\Core\V1\ConfigMap;
use Kubernetes\API\Core\V1\Secret;

// Create ConfigMap
$configMap = new ConfigMap();
$configMap->setName('app-config')
          ->setNamespace('default')
          ->setData([
              'database.host' => 'postgres.example.com',
              'app.debug' => 'false'
          ]);

// Create Secret
$secret = new Secret();
$secret->setName('app-secrets')
       ->setNamespace('default')
       ->setType('Opaque')
       ->setData([
           'db-password' => base64_encode('super-secret'),
           'api-key' => base64_encode('api-key-value')
       ]);
```

### Persistent Storage

```php
use Kubernetes\API\Apps\V1\StatefulSet;

$statefulSet = new StatefulSet();
$statefulSet->setName('database')
            ->setNamespace('data')
            ->setReplicas(3)
            ->setServiceName('database-headless')
            ->setSelectorMatchLabels(['app' => 'postgres'])
            ->addPvcTemplate('data', 'ssd-storage', '100Gi', ['ReadWriteOnce'])
            ->setPodManagementPolicy('OrderedReady')
            ->setRollingUpdateStrategy(1);
```

### Batch Jobs & Cron Jobs

```php
use Kubernetes\API\Batch\V1\Job;
use Kubernetes\API\Batch\V1\CronJob;

// One-time Job
$job = new Job();
$job->setName('data-migration')
    ->setNamespace('default')
    ->setCompletions(1)
    ->setParallelism(1)
    ->createPodTemplate('migrator:latest', ['migrate'], ['--all']);

// Scheduled CronJob
$cronJob = new CronJob();
$cronJob->setName('backup-job')
        ->setNamespace('backups')
        ->setDailySchedule(2, 0) // 2:00 AM daily
        ->forbidConcurrency()
        ->setHistoryLimits(7, 2)
        ->createSimpleJobTemplate('backup-tool:latest', ['backup.sh'], ['--full']);
```

## 📚 API Reference

### Supported Kubernetes API Groups

| API Group | Version | Resources | Status |
|-----------|---------|-----------|--------|
| **core** | v1 | Pod, Service, Secret, ConfigMap, PV, PVC, Namespace, Node, Event, Endpoints, LimitRange, ResourceQuota, ServiceAccount, ReplicationController | ✅ Complete |
| **apps** | v1 | Deployment, StatefulSet, DaemonSet, ReplicaSet | ✅ Complete |
| **batch** | v1 | Job, CronJob | ✅ Complete |
| **rbac.authorization.k8s.io** | v1 | Role, RoleBinding, ClusterRole, ClusterRoleBinding | ✅ Complete |
| **networking.k8s.io** | v1 | NetworkPolicy, Ingress, IngressClass | ✅ Complete |

### Resource Categories

#### **Core Workloads (core/v1)**
- `Pod` - Basic compute units
- `Service` - Service discovery and load balancing  
- `ReplicationController` - Legacy pod replication

#### **Advanced Workloads (apps/v1)**
- `Deployment` - Stateless application deployments
- `StatefulSet` - Stateful applications with persistent storage
- `DaemonSet` - Node-level system services
- `ReplicaSet` - Pod replication management

#### **Configuration & Storage (core/v1)**
- `ConfigMap` - Non-sensitive configuration data
- `Secret` - Sensitive information storage
- `PersistentVolume` / `PersistentVolumeClaim` - Storage management
- `LimitRange` / `ResourceQuota` - Resource constraints

#### **Batch Processing (batch/v1)**
- `Job` - Run-to-completion workloads
- `CronJob` - Time-based scheduled jobs

#### **Security & Access (rbac.authorization.k8s.io/v1)**
- `Role` / `ClusterRole` - Permission definitions
- `RoleBinding` / `ClusterRoleBinding` - Permission assignments

#### **Networking (networking.k8s.io/v1)**
- `NetworkPolicy` - Network traffic rules
- `Ingress` / `IngressClass` - HTTP/HTTPS routing

## 🏗️ Advanced Usage

### Method Chaining

All setter methods return `self` for fluent interface support:

```php
$deployment = new Deployment();
$result = $deployment
    ->setName('api-server')
    ->setNamespace('production')
    ->setReplicas(5)
    ->setSelectorMatchLabels(['app' => 'api'])
    ->addContainer('api', 'api-server:v1.2.3', [8080])
    ->setResourceLimits(['memory' => '512Mi', 'cpu' => '500m'])
    ->setResourceRequests(['memory' => '256Mi', 'cpu' => '250m']);

// $result === $deployment (true)
```

### Helper Methods

Resources include convenience methods for common operations:

```php
// DaemonSet node targeting
$daemonSet = new DaemonSet();
$daemonSet->runOnAllNodes()          // Includes master/control-plane tolerations
          ->setHostNetwork(true)     // Access host networking
          ->setPrivileged(true);     // Run with elevated privileges

// StatefulSet scaling and storage
$statefulSet = new StatefulSet();
$statefulSet->scale(5)                              // Set replica count
            ->addPvcTemplate('data', 'fast-ssd', '50Gi')  // Add storage template
            ->setPodManagementPolicy('Parallel');          // Parallel startup

// Service port management
$service = new Service();
$service->addPort(80, 8080, 'TCP', 'http')    // External:Internal:Protocol:Name
        ->addPort(443, 8443, 'TCP', 'https')
        ->setSessionAffinity('ClientIP');
```

### Resource Conversion

Convert resources to/from arrays for API operations:

```php
// To Array (for API submission)
$podArray = $pod->toArray();

// From Array (from API response)
$pod = Pod::fromArray($apiResponse);

// JSON serialization
$jsonManifest = json_encode($deployment->toArray(), JSON_PRETTY_PRINT);
```

## 🔧 Development Tools

This library includes comprehensive development tools:

```bash
# Run tests
composer test

# Static analysis (PHPStan Level 8)
composer analyse

# Code formatting (PSR-12)
composer format

# All quality checks
composer test && composer analyse && composer format
```

## 📖 Examples

### Complete Application Stack

```php
use Kubernetes\API\Core\V1\{Namespace, Secret, ConfigMap, Service};
use Kubernetes\API\Apps\V1\Deployment;

// Create namespace
$namespace = new Namespace();
$namespace->setName('my-app');

// Application configuration
$configMap = new ConfigMap();
$configMap->setName('app-config')
          ->setNamespace('my-app')
          ->setData([
              'REDIS_HOST' => 'redis.my-app.svc.cluster.local',
              'LOG_LEVEL' => 'info'
          ]);

// Application secrets
$secret = new Secret();
$secret->setName('app-secrets')
       ->setNamespace('my-app')
       ->setStringData([
           'DB_PASSWORD' => 'secure-password',
           'JWT_SECRET' => 'jwt-signing-key'
       ]);

// Application deployment
$deployment = new Deployment();
$deployment->setName('web-app')
           ->setNamespace('my-app')
           ->setReplicas(3)
           ->setSelectorMatchLabels(['app' => 'web'])
           ->addContainer('web', 'my-app:v1.0.0', [8080])
           ->addEnvFromConfigMap('app-config')
           ->addEnvFromSecret('app-secrets');

// Load balancer service
$service = new Service();
$service->setName('web-service')
        ->setNamespace('my-app')
        ->setType('LoadBalancer')
        ->setSelectorMatchLabels(['app' => 'web'])
        ->addPort(80, 8080);
```

### Database with Persistent Storage

```php
use Kubernetes\API\Core\V1\{Secret, Service};
use Kubernetes\API\Apps\V1\StatefulSet;

// Database credentials
$dbSecret = new Secret();
$dbSecret->setName('postgres-credentials')
         ->setNamespace('database')
         ->setStringData([
             'POSTGRES_USER' => 'admin',
             'POSTGRES_PASSWORD' => 'secure-db-password',
             'POSTGRES_DB' => 'application'
         ]);

// Headless service for StatefulSet
$headlessService = new Service();
$headlessService->setName('postgres-headless')
                ->setNamespace('database')
                ->setType('ClusterIP')
                ->setClusterIp('None')  // Headless
                ->setSelectorMatchLabels(['app' => 'postgres'])
                ->addPort(5432, 5432);

// PostgreSQL StatefulSet
$postgres = new StatefulSet();
$postgres->setName('postgres')
         ->setNamespace('database')
         ->setReplicas(1)
         ->setServiceName('postgres-headless')
         ->setSelectorMatchLabels(['app' => 'postgres'])
         ->addContainer('postgres', 'postgres:15', [5432])
         ->addEnvFromSecret('postgres-credentials')
         ->addPvcTemplate('data', 'ssd-storage', '20Gi')
         ->setPodManagementPolicy('OrderedReady');
```

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](.github/CONTRIBUTING.md) for details.

### Development Setup

1. Clone the repository
2. Install dependencies: `composer install`
3. Run tests: `composer test`
4. Run static analysis: `composer analyse`

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🔗 Links

- [Documentation](https://github.com/cloudnativephp/kubernetes/wiki)
- [Kubernetes API Reference](https://kubernetes.io/docs/reference/kubernetes-api/)
- [Issues](https://github.com/cloudnativephp/kubernetes/issues)
- [Discussions](https://github.com/cloudnativephp/kubernetes/discussions)

## 🙏 Acknowledgments

- Inspired by Laravel's Eloquent ORM patterns
- Built for the Kubernetes community
- Follows Kubernetes API conventions and best practices

---

**Ready to modernize your Kubernetes PHP applications?** Start with `composer require cloudnativephp/kubernetes` and experience type-safe Kubernetes resource management!
