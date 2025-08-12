# Development Guide

This document provides comprehensive guidelines for developing and contributing to the Kubernetes PHP Client Library.

## 🏗️ Project Architecture

### Directory Structure

```
src/
├── Resource.php                 # Base resource class
├── API/                        # Kubernetes API groups
│   ├── Core/V1/               # Core Kubernetes resources
│   │   ├── AbstractResource.php
│   │   ├── Pod.php
│   │   ├── Service.php
│   │   └── ...
│   ├── Apps/V1/               # Application workloads
│   ├── Batch/V1/              # Batch processing
│   ├── NetworkingK8sIo/V1/   # Networking resources
│   └── RbacAuthorizationK8sIo/V1/ # RBAC resources
├── Client/                    # API client implementations
├── Contracts/                 # Interfaces and contracts
├── Exceptions/                # Custom exceptions
└── Traits/                    # Reusable traits
```

### Namespace Mapping

Kubernetes API groups are mapped to PHP namespaces following this convention:

| Kubernetes API Group | PHP Namespace |
|----------------------|---------------|
| `core/v1` | `Kubernetes\API\Core\V1` |
| `apps/v1` | `Kubernetes\API\Apps\V1` |
| `batch/v1` | `Kubernetes\API\Batch\V1` |
| `networking.k8s.io/v1` | `Kubernetes\API\NetworkingK8sIo\V1` |
| `rbac.authorization.k8s.io/v1` | `Kubernetes\API\RbacAuthorizationK8sIo\V1` |

## 🛠️ Development Setup

### Prerequisites

- PHP 8.4 or higher
- Composer
- Git

### Initial Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/cloudnativephp/kubernetes.git
   cd kubernetes
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Verify installation:**
   ```bash
   composer test
   composer analyse
   composer format
   ```

### Development Tools

The project includes several quality assurance tools:

```bash
# Run the test suite
composer test

# Static analysis (PHPStan Level 8)
composer analyse

# Code formatting (PSR-12 + custom rules)
composer format

# Run all quality checks
composer check-all
```

## 📝 Coding Standards

### PHP Standards

- **PHP Version:** 8.4+ with strict types (`declare(strict_types=1)`)
- **Coding Style:** PSR-12 + custom rules
- **Static Analysis:** PHPStan Level 8 (strictest)
- **Documentation:** Complete PHPDoc coverage

### Code Style Rules

#### Type Declarations

```php
<?php

declare(strict_types=1);

namespace Kubernetes\API\Core\V1;

class Pod extends AbstractAbstractResource
{
    // All methods must have type hints
    public function setName(string $name): self
    {
        $this->metadata['name'] = $name;
        return $this;
    }
    
    public function getName(): ?string
    {
        return $this->metadata['name'] ?? null;
    }
}
```

#### Array Alignment
Arrays and match statements must have aligned `=>` operators:

```php
// Correct array alignment
$data = [
    'apiVersion' => $this->getApiVersion(),
    'kind'       => $this->getKind(),
    'metadata'   => $this->metadata,
    'spec'       => $this->spec,
];

// Correct match statement alignment
return match ($extension) {
    'yaml', 'yml' => static::fromYaml($content),
    'json'        => static::fromJson($content),
    default       => static::fromJson($content),
};
```

#### Documentation Standards
```php
/**
 * Set the container image for the pod.
 *
 * @param string $image The container image name and tag
 *
 * @return self
 *
 * @throws InvalidArgumentException When image format is invalid
 */
public function setImage(string $image): self
{
    // Implementation
}
```

## 🏛️ Architecture Patterns

### AbstractResource Pattern

Each API version must have an `AbstractResource` class:

```php
<?php

declare(strict_types=1);

namespace Kubernetes\API\Core\V1;

use Kubernetes\API\AbstractResource;

abstract class AbstractResource extends AbstractResource
{
    public function getApiVersion(): string
    {
        return 'v1';
    }

    abstract public function getKind(): string;
}
```

### Resource Implementation

All resources must:

1. **Extend AbstractResource** (never `Resource` directly)
2. **Implement only `getKind()`** method
3. **Use appropriate traits** for functionality
4. **Provide fluent interface** with method chaining

```php
<?php

declare(strict_types=1);

namespace Kubernetes\API\Core\V1;

use Kubernetes\Traits\IsNamespacedResource;

class Pod extends AbstractAbstractResource
{
    use IsNamespacedResource; // For namespaced resources only

    public function getKind(): string
    {
        return 'Pod';
    }

    public function setRestartPolicy(string $policy): self
    {
        $this->spec['restartPolicy'] = $policy;
        return $this;
    }

    public function addContainer(array $container): self
    {
        $this->spec['containers'][] = $container;
        return $this;
    }
}
```

### Trait Usage

#### HasMetadata Trait
Used by ALL Kubernetes resources for common metadata operations:

```php
// Provided by HasMetadata
$resource->setName('my-resource');
$resource->addLabel('app', 'my-app');
$resource->addAnnotation('description', 'My resource');
```

#### IsNamespacedResource Trait
Used ONLY by namespaced resources:

```php
// Provided by IsNamespacedResource  
$resource->setNamespace('production');
$namespace = $resource->getNamespace();
```

**Important:** Cluster-scoped resources (Node, PersistentVolume, ClusterRole, etc.) must NOT use this trait.

## 🧪 Testing Guidelines

### Test Structure

Tests mirror the `src/` structure under `tests/` with a `Tests\` prefix:

```
tests/
├── Pest.php                    # Pest configuration
├── TestCase.php                # Base test case
└── Kubernetes/
    └── API/
        └── Core/V1/
            ├── PodTest.php
            ├── ServiceTest.php
            └── ...
```

### Test Patterns

#### Basic Resource Tests
```php
<?php

use Kubernetes\API\Core\V1\Pod;

it('can create a pod', function (): void {
    $pod = new Pod();
    expect($pod->getApiVersion())->toBe('v1');
    expect($pod->getKind())->toBe('Pod');
});

it('extends the correct abstract resource', function (): void {
    $pod = new Pod();
    expect($pod)->toBeInstanceOf(AbstractResource::class);
});
```

#### Method Chaining Tests
```php
it('can chain setter methods', function (): void {
    $pod = new Pod();
    $result = $pod
        ->setName('test-pod')
        ->setNamespace('default')
        ->setRestartPolicy('Always');
    
    expect($result)->toBe($pod);
    expect($pod->getName())->toBe('test-pod');
});
```

#### Namespace Tests (for namespaced resources)
```php
it('can set and get namespace', function (): void {
    $pod = new Pod();
    $pod->setNamespace('production');
    expect($pod->getNamespace())->toBe('production');
});
```

#### Array Conversion Tests
```php
it('can convert to array', function (): void {
    $pod = new Pod();
    $pod->setName('test')->setNamespace('default');
    
    $array = $pod->toArray();
    expect($array)->toHaveKeys(['apiVersion', 'kind', 'metadata']);
    expect($array['apiVersion'])->toBe('v1');
    expect($array['kind'])->toBe('Pod');
});
```

### Running Tests

```bash
# Run all tests
composer test

# Run specific test file
./vendor/bin/pest tests/Kubernetes/API/Core/V1/PodTest.php

# Run tests with coverage
./vendor/bin/pest --coverage

# Run tests in parallel
./vendor/bin/pest --parallel
```

## 🔍 Static Analysis

### PHPStan Configuration

The project uses PHPStan Level 8 (strictest) with custom configuration in `phpstan.neon`:

```yaml
parameters:
    level: 8
    paths:
        - src
        - tests
    ignoreErrors:
        # Framework limitations (legitimate ignores)
        - identifier: missingType.iterableValue
        - identifier: missingType.generics
        - message: '#Call to an undefined method .+::setNamespace\(\)#'
        - message: '#Unable to resolve the template type TValue in call to function expect#'
```

### Common Issues and Solutions

#### Trait Method Recognition
PHPStan may not recognize methods from traits in test contexts:

```php
// Solution: Add explicit null checks
$result = $pod->getTemplate();
expect($result)->not->toBeNull();
if ($result !== null) {
    expect($result['containers'])->toBeArray();
}
```

#### Unsafe `new static()` Usage
Factory methods may trigger warnings:

```php
// In Resource.php - legitimate pattern
public static function fromArray(array $data): static
{
    /** @var static $resource */
    $resource = new static();
    // ... implementation
    return $resource;
}
```

## 🚀 Adding New Resources

### Step-by-Step Guide

1. **Check AbstractResource exists** for the API group
2. **Create resource class** extending AbstractResource
3. **Apply appropriate traits**
4. **Implement resource-specific methods**
5. **Write comprehensive tests**
6. **Validate with quality tools**

### Example: Adding a New Resource

```php
<?php

declare(strict_types=1);

namespace Kubernetes\API\Apps\V1;

use Kubernetes\Traits\IsNamespacedResource;

/**
 * Represents a Kubernetes Deployment resource.
 *
 * @see https://kubernetes.io/docs/concepts/workloads/controllers/deployment/
 */
class Deployment extends AbstractAbstractResource
{
    use IsNamespacedResource;

    public function getKind(): string
    {
        return 'Deployment';
    }

    /**
     * Set the number of desired replicas.
     *
     * @param int $replicas Number of pod replicas
     *
     * @return self
     */
    public function setReplicas(int $replicas): self
    {
        $this->spec['replicas'] = $replicas;
        return $this;
    }

    /**
     * Get the current replica count.
     *
     * @return int
     */
    public function getReplicas(): int
    {
        return $this->spec['replicas'] ?? 1;
    }
}
```

### Corresponding Test

```php
<?php

use Kubernetes\API\Apps\V1\{AbstractAbstractResource, Deployment};

it('can create a deployment', function (): void {
    $deployment = new Deployment();
    expect($deployment->getApiVersion())->toBe('apps/v1');
    expect($deployment->getKind())->toBe('Deployment');
    expect($deployment)->toBeInstanceOf(AbstractAbstractResource::class);
});

it('can set and get replicas', function (): void {
    $deployment = new Deployment();
    $deployment->setReplicas(3);
    expect($deployment->getReplicas())->toBe(3);
});

it('has default replica count', function (): void {
    $deployment = new Deployment();
    expect($deployment->getReplicas())->toBe(1);
});
```

## 🔧 Development Workflow

### Daily Development

1. **Create feature branch:** `git checkout -b feature/add-new-resource`
2. **Implement changes** following architecture patterns
3. **Run quality checks:** `composer test && composer analyse && composer format`
4. **Commit changes** with descriptive messages
5. **Push and create PR**

### Pre-commit Checklist

- [ ] All tests pass (`composer test`)
- [ ] PHPStan analysis passes (`composer analyse`)  
- [ ] Code is properly formatted (`composer format`)
- [ ] New functionality has tests
- [ ] Documentation is updated
- [ ] Architecture compliance verified

### Debugging Tips

#### Test Failures
```bash
# Run specific test with verbose output
./vendor/bin/pest tests/path/to/test.php -v

# Debug with dd() function
it('debugs array structure', function (): void {
    $resource = new Resource();
    dd($resource->toArray()); // Dies and dumps
});
```

#### PHPStan Issues
```bash
# Analyze specific file
./vendor/bin/phpstan analyse src/API/Core/V1/Pod.php

# Generate baseline for existing issues
./vendor/bin/phpstan analyse --generate-baseline
```

## 📚 Learning Resources

### Kubernetes API Reference
- [Official Kubernetes API Docs](https://kubernetes.io/docs/reference/kubernetes-api/)
- [API Conventions](https://github.com/kubernetes/community/blob/master/contributors/devel/sig-architecture/api-conventions.md)

### PHP Development
- [PHP 8.4 Documentation](https://www.php.net/manual/en/)
- [PSR-12 Coding Style](https://www.php-fig.org/psr/psr-12/)
- [PHPStan Documentation](https://phpstan.org/user-guide/getting-started)

### Testing
- [Pest Testing Framework](https://pestphp.com/)
- [Testing Best Practices](https://pestphp.com/docs/testing-techniques)

## 🤝 Contributing Guidelines

### Code Review Process

1. **Architecture Compliance:** Verify AbstractResource usage and trait application
2. **Code Quality:** Check PHPStan compliance and test coverage
3. **Documentation:** Ensure proper PHPDoc formatting
4. **Functionality:** Validate method chaining and helper methods

### Commit Message Format

```
type(scope): description

[optional body]

[optional footer]
```

Examples:
- `feat(core): add Pod resource with container management`
- `fix(apps): correct StatefulSet scaling behavior`
- `docs(readme): update installation instructions`
- `test(batch): add CronJob schedule validation tests`

## 🎯 Current Development Priorities

### Completed (Production Ready)
- ✅ Core/v1 - All essential resources (15 resources)
- ✅ Apps/v1 - All workload resources (4 resources)  
- ✅ Batch/v1 - Job processing (2 resources)
- ✅ RBAC - Security and access control (4 resources)
- ✅ Networking - Network policies and ingress (3 resources)

### Future Enhancements
- HTTP client integration for actual API calls
- Watch API support for real-time updates
- Authentication methods (kubeconfig, service accounts)
- Custom Resource Definition (CRD) support
- Resource validation before API submission
- Advanced error handling and retry logic

---

This development guide provides the foundation for maintaining high code quality and consistent architecture across the Kubernetes PHP Client Library. For questions or clarifications, please open a GitHub Discussion or Issue.
