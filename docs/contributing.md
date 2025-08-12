# Contributing Guide

Thank you for your interest in contributing to the Kubernetes PHP client library! This guide will help you get started with development and ensure your contributions align with the project standards.

## Development Setup

### Prerequisites
- PHP 8.4 or higher
- Composer
- Access to a Kubernetes cluster for testing (minikube, kind, or cloud cluster)

### Local Environment Setup
```bash
# Clone the repository
git clone https://github.com/cloudnativephp/kubernetes.git
cd kubernetes

# Install dependencies
composer install

# Run tests
composer test

# Run static analysis
composer analyse

# Format code
composer format
```

### Development Tools
The project uses several tools to maintain code quality:

- **Pest** - Testing framework with expressive syntax
- **PHPStan** - Static analysis at level 8 (strictest)
- **PHP CS Fixer** - Code formatting and PSR-12 compliance
- **Composer** - Dependency management and scripts

## Architecture Guidelines

### Resource Implementation Pattern

When adding new Kubernetes resources, follow this strict pattern:

#### 1. Extend the Correct AbstractResource
```php
// ✅ CORRECT - Extend API group specific AbstractResource
namespace Kubernetes\API\Apps\V1;

class StatefulSet extends AbstractResource
{
    use IsNamespacedResource; // Only for namespaced resources
    
    public function getKind(): string 
    {
        return 'StatefulSet';
    }
    
    // Resource-specific methods...
}

// ❌ INCORRECT - Never extend Resource directly
class StatefulSet extends Resource // DON'T DO THIS
```

#### 2. Apply Traits Correctly
```php
// Namespaced resources MUST use IsNamespacedResource trait
class Pod extends AbstractResource
{
    use IsNamespacedResource; // Required for namespaced resources
}

// Cluster-scoped resources MUST NOT use IsNamespacedResource trait
class Node extends AbstractResource
{
    // No IsNamespacedResource trait for cluster-scoped resources
}
```

#### 3. Resource Method Patterns
```php
class MyResource extends AbstractResource
{
    // Fluent setters return self
    public function setReplicas(int $replicas): self
    {
        $this->spec['replicas'] = $replicas;
        return $this;
    }
    
    // Getters with sensible defaults
    public function getReplicas(): int
    {
        return $this->spec['replicas'] ?? 1;
    }
    
    // Helper methods for complex operations
    public function addContainer(string $name, string $image, ?array $ports = null): self
    {
        $container = ['name' => $name, 'image' => $image];
        
        if ($ports !== null) {
            $container['ports'] = array_map(fn($port) => ['containerPort' => $port], $ports);
        }
        
        $this->spec['template']['spec']['containers'][] = $container;
        return $this;
    }
}
```

### Documentation Standards

#### PHPDoc Requirements
All public methods MUST have complete PHPDoc documentation:

```php
/**
 * Set the number of desired replicas for the deployment.
 *
 * @param int $replicas The desired number of replicas (must be >= 0)
 *
 * @return self
 *
 * @throws InvalidArgumentException If replicas is negative
 */
public function setReplicas(int $replicas): self
{
    if ($replicas < 0) {
        throw new InvalidArgumentException('Replicas must be non-negative');
    }
    
    $this->spec['replicas'] = $replicas;
    return $this;
}
```

#### Docblock Formatting Rules
- **Blank lines required** between different annotation sections
- Include `@param` for all parameters with types and descriptions
- Include `@return` for return types
- Include `@throws` for exceptions that may be thrown
- Add `@see` links to Kubernetes documentation when relevant

## Testing Guidelines

### Test Structure
Tests MUST mirror the source structure with a `Tests` namespace prefix:

```
src/API/Core/V1/Pod.php → tests/Kubernetes/API/Core/V1/PodTest.php
```

### Required Test Coverage
Every resource MUST have tests covering:

1. **Basic Creation**
```php
it('can create a pod', function (): void {
    $pod = new Pod();
    expect($pod->getApiVersion())->toBe('v1');
    expect($pod->getKind())->toBe('Pod');
});
```

2. **Method Chaining**
```php
it('can chain setter methods', function (): void {
    $pod = new Pod();
    $result = $pod->setName('test')
                  ->setNamespace('default');
    expect($result)->toBe($pod);
});
```

3. **Namespace Operations** (for namespaced resources)
```php
it('can set and get namespace', function (): void {
    $pod = new Pod();
    $pod->setNamespace('production');
    expect($pod->getNamespace())->toBe('production');
});
```

4. **Resource-Specific Functionality**
```php
it('can add containers', function (): void {
    $pod = new Pod();
    $pod->addContainer('web', 'nginx:latest', [80, 443]);
    
    $containers = $pod->getContainers();
    expect($containers)->toHaveCount(1);
    expect($containers[0]['name'])->toBe('web');
    expect($containers[0]['image'])->toBe('nginx:latest');
});
```

### Test Naming Conventions
- Use descriptive test names: `it('can perform specific action', function() { ... })`
- Group related tests with `describe()` blocks
- Use `beforeEach()` for common setup

## Code Style Guidelines

### PSR-12 Compliance
The project follows PSR-12 coding standards with additional rules:

```php
<?php

declare(strict_types=1);

namespace Kubernetes\API\Core\V1;

use Kubernetes\Traits\IsNamespacedResource;

class Pod extends AbstractResource
{
    use IsNamespacedResource;
    
    public function getKind(): string
    {
        return 'Pod';
    }
}
```

### Array Alignment Rules
Arrays with `=>` operators MUST be aligned:

```php
$data = [
    'apiVersion' => $this->getApiVersion(),
    'kind'       => $this->getKind(),
    'metadata'   => $this->metadata,
    'spec'       => $this->spec,
];
```

Match statements require manual alignment:

```php
return match ($extension) {
    'yaml', 'yml' => static::fromYaml($content),
    'json'        => static::fromJson($content),
    default       => static::fromJson($content),
};
```

### Type Hints
Use strict type hints for all method parameters and returns:

```php
public function setReplicas(int $replicas): self
public function getLabels(): array
public function findPod(string $name, ?string $namespace = null): ?Pod
```

## Contribution Workflow

### 1. Issue Creation
Before starting work:
- Check existing issues to avoid duplication
- Create an issue describing the feature/bug
- Wait for maintainer feedback on approach

### 2. Branch Naming
Use descriptive branch names:
- `feature/add-statefulset-support`
- `fix/pod-container-validation`
- `docs/improve-deployment-examples`

### 3. Development Process
```bash
# Create feature branch
git checkout -b feature/my-new-feature

# Make changes following guidelines
# Add tests for new functionality
# Update documentation

# Run quality checks
composer test
composer analyse
composer format

# Commit with descriptive messages
git commit -m "Add StatefulSet resource with PVC template support"
```

### 4. Pull Request Requirements
Your PR must:
- [ ] Include comprehensive tests (>95% coverage for new code)
- [ ] Pass all existing tests
- [ ] Pass PHPStan level 8 analysis
- [ ] Follow PSR-12 formatting
- [ ] Include updated documentation
- [ ] Have descriptive commit messages
- [ ] Reference related issues

### 5. Code Review Process
- All PRs require review from a maintainer
- Address feedback promptly
- Keep PRs focused and reasonably sized
- Be responsive to review comments

## Adding New API Groups

When adding support for new Kubernetes API groups:

### 1. Create AbstractResource
```php
// src/API/NewGroup/V1/AbstractResource.php
namespace Kubernetes\API\NewGroup\V1;

use Kubernetes\Resource;

abstract class AbstractResource extends Resource
{
    public function getApiVersion(): string
    {
        return 'newgroup.k8s.io/v1';
    }
    
    abstract public function getKind(): string;
}
```

### 2. Add Resources
```php
// src/API/NewGroup/V1/MyResource.php
namespace Kubernetes\API\NewGroup\V1;

use Kubernetes\Traits\IsNamespacedResource;

class MyResource extends AbstractResource
{
    use IsNamespacedResource; // If namespaced
    
    public function getKind(): string
    {
        return 'MyResource';
    }
    
    // Resource-specific methods...
}
```

### 3. Create Tests
```php
// tests/Kubernetes/API/NewGroup/V1/MyResourceTest.php
namespace Tests\Kubernetes\API\NewGroup\V1;

use Kubernetes\API\NewGroup\V1\MyResource;

it('can create my resource', function (): void {
    $resource = new MyResource();
    expect($resource->getApiVersion())->toBe('newgroup.k8s.io/v1');
    expect($resource->getKind())->toBe('MyResource');
});
```

## Quality Assurance

### Before Submitting
Run the complete quality check suite:

```bash
# Format code
composer format

# Run tests
composer test

# Static analysis
composer analyse

# Check for any remaining issues
composer check-all # If available
```

### PHPStan Configuration
The project uses PHPStan level 8 with specific ignore patterns for legitimate framework limitations:

```yaml
# phpstan.neon
ignoreErrors:
    - identifier: missingType.iterableValue
    - identifier: missingType.generics
    - message: '#Call to an undefined method .+::setNamespace\(\)#'
    - message: '#Unsafe usage of new static\(\)#'
      path: src/Resource.php
```

Only add new ignore patterns if absolutely necessary and document the reason.

## Documentation Contributions

### Adding Examples
When adding new resources, include practical examples in `docs/examples/`:

```php
// Real-world usage example
$statefulSet = new StatefulSet();
$statefulSet->setName('postgres-cluster')
           ->setNamespace('database')
           ->setReplicas(3)
           ->addPvcTemplate('data', 'ssd', '100Gi')
           ->setPodManagementPolicy('OrderedReady');
```

### API Documentation
Update API reference documentation in `docs/api-reference/` with:
- Complete method signatures
- Parameter descriptions
- Return type information
- Usage examples
- Links to Kubernetes documentation

## Release Process

### Version Numbering
The project follows Semantic Versioning (SemVer):
- `MAJOR.MINOR.PATCH`
- Breaking changes increment MAJOR
- New features increment MINOR
- Bug fixes increment PATCH

### Changelog Maintenance
Update `CHANGELOG.md` with:
- New features
- Breaking changes
- Bug fixes
- Deprecations

## Getting Help

### Communication Channels
- **Issues** - Bug reports and feature requests
- **Discussions** - General questions and community support
- **Pull Requests** - Code review and collaboration

### Maintainer Response
- Issues: Within 48 hours
- Pull Requests: Within 72 hours
- Security Issues: Within 24 hours

Thank you for contributing to the Kubernetes PHP client library! Your contributions help make Kubernetes more accessible to the PHP community.
