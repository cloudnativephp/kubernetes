# Contributing to cloudnativephp/kubernetes

Thanks for your interest in contributing to this Kubernetes client for PHP! This document helps you get set up and submit high‑quality contributions.

## Prerequisites
- PHP >= 8.4
- Composer
- macOS/Linux/WSL recommended

Install dependencies:

```
composer install
```

## Common Tasks

- Run tests (Pest):
```
composer test
```

- Test coverage:
```
composer test-coverage
```

- Static analysis (PHPStan):
```
composer analyse
```

- Coding style (PHP-CS-Fixer):
```
composer format
composer format-check
```

## Pull Request Checklist
- Code is typed and passes `composer analyse` with the configured level
- Style checks pass (`composer format-check`)
- Tests added/updated for new or changed behavior (`composer test`)
- Public APIs documented in README where relevant
- PR title and description clearly explain the change

## Adding or Updating Kubernetes Resources
- Resources live under `src/API/<Group>/<Version>/...`
- Follow the patterns used by existing `AbstractResource.php` and concrete resources
- Add unit tests under `tests/Kubernetes/API/<Group>/<Version>/...`
- Keep serialization consistent with existing resources; see `tests/Unit/ResourceSerializationTest.php`

## Commit Messages
Use clear, descriptive commit messages. Conventional Commits are welcome but not required. Example:
- feat(apps/v1): add StatefulSet scale subresource
- fix(core/v1): correct Service selector serialization

## Discussions and Issues
- Use GitHub Issues for bugs and feature requests
- Provide steps to reproduce and expected vs. actual behavior
- For questions, include environment details (PHP version, OS, relevant config)

## Code of Conduct
By participating, you agree to abide by our [Code of Conduct](./CODE_OF_CONDUCT.md).

## License
By contributing, you agree that your contributions will be licensed under the MIT License, consistent with this repository’s license.
