# Contributing to Collider

Thank you for your interest in contributing to Collider! This document provides guidelines and information for contributors.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Contribution Workflow](#contribution-workflow)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Documentation](#documentation)
- [Performance Considerations](#performance-considerations)
- [Submitting Changes](#submitting-changes)

## Code of Conduct

By participating in this project, you agree to maintain a respectful and inclusive environment for all contributors.

## Getting Started

### Prerequisites

- PHP 8.3+
- Swoole 5.0+
- PostgreSQL 15
- Redis 7
- Docker & Docker Compose
- Composer 2.x

### Development Setup

1. **Clone the repository**
   ```bash
   git clone git@github.com:BeMySlaveDarlin/collider.git
   cd collider
   ```

2. **Environment setup**
   ```bash
   cp .env.example .env
   ```

3. **Install and start**
   ```bash
   make install
   ```

4. **Verify setup**
   ```bash
   make health
   curl http://localhost/health
   ```

## Contribution Workflow

### 1. Fork and Branch

```bash
git checkout -b feature/your-feature-name
# or
git checkout -b fix/issue-description
```

### 2. Development

- Write your code following our [coding standards](#coding-standards)
- Add tests for new functionality
- Update documentation if needed
- Ensure all tests pass

### 3. Commit Messages

Use conventional commit format:

```
type(scope): subject

body (optional)

footer (optional)
```

Types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes
- `refactor`: Code refactoring
- `perf`: Performance improvements
- `test`: Test additions/changes
- `chore`: Build process or auxiliary tool changes

Examples:
```
feat(api): add batch event creation endpoint
fix(database): resolve connection pool timeout issue
docs(api): update event creation examples
perf(query): optimize user events aggregation query
```

## Coding Standards

### PHP Standards

We follow PSR-12 coding standards with additional rules:

```bash
# Check code style
make cs-check

# Fix code style
make cs-fix
```

### Code Quality

```bash
# Run static analysis
make phpstan-check
```

### Architecture Guidelines

**Domain-Driven Design Structure:**
```
app/
├── Application/     # Infrastructure & application services
├── Domain/          # Business logic and entities
│   └── UserAnalytics/
│       ├── Entity/           # Domain entities (Event, User, EventType)
│       ├── Repository/       # Data access interfaces  
│       ├── UseCase/          # Application use cases
│       └── ValueObject/      # Request/Response objects
└── Endpoint/        # HTTP controllers & console commands
    ├── Web/         # HTTP controllers
    └── Command/     # Console commands
```

**Key Principles:**
- Keep domain logic pure (no framework dependencies)
- Use dependency injection
- Implement proper error handling
- Follow SOLID principles
- Prefer composition over inheritance

### Database Guidelines

- Use migrations for schema changes
- Add proper indexes for performance
- Use appropriate PostgreSQL data types
- Consider query performance for large datasets

## Testing

### Running Tests

```bash
# Run all tests
vendor/bin/co-phpunit

# Docker environment
docker compose exec app vendor/bin/co-phpunit

# Run specific test suite
vendor/bin/co-phpunit test/Cases/
vendor/bin/co-phpunit test/HttpTestCase.php
```

### Test Coverage

- Write unit tests for domain logic
- Add feature tests for API endpoints using `HttpTestCase`
- Aim for 80%+ coverage on new code
- Test edge cases and error conditions
- Use Hyperf's testing framework (`co-phpunit`)

### Performance Testing

```bash
# Seed test data
make seed

# Basic load test
make load-test

# Custom load test
ab -n 1000 -c 10 http://localhost/events?page=1&limit=100
```

## Documentation

### API Documentation

Update `docs/api.md` when adding/modifying endpoints:
- Include request/response examples
- Document all parameters
- Add error response examples

### Code Documentation

- Use PHPDoc for all public methods
- Document complex business logic
- Add inline comments for non-obvious code

### Architecture Decisions

Document significant architectural decisions in `docs/` directory.

## Performance Considerations

Collider is designed for high performance. When contributing:

### Database Performance

- Use appropriate indexes
- Optimize queries for large datasets (10M+ events)
- Consider PostgreSQL-specific optimizations
- Use EXPLAIN ANALYZE for query optimization

### Swoole/Hyperf Best Practices

- Avoid blocking operations in coroutines
- Use connection pooling properly
- Consider memory usage in long-running processes
- Prefer asynchronous operations

### Benchmarking

Test performance impact of changes:

```bash
# Before changes
ab -n 1000 -c 10 http://localhost/events

# After changes
ab -n 1000 -c 10 http://localhost/events

# Compare results
```

## Submitting Changes

### Pull Request Process

1. **Create Pull Request**
   - Use descriptive title
   - Reference related issues
   - Include summary of changes

2. **PR Description Template**
   ```markdown
   ## Summary
   Brief description of changes

   ## Type of Change
   - [ ] Bug fix
   - [ ] New feature
   - [ ] Breaking change
   - [ ] Documentation update

   ## Testing
   - [ ] Tests pass locally
   - [ ] Added tests for new functionality
   - [ ] Tested performance impact

   ## Documentation
   - [ ] Updated API documentation
   - [ ] Updated README if needed
   - [ ] Added inline code documentation
   ```

### Review Process

- All PRs require review
- Address reviewer feedback promptly
- Ensure CI checks pass
- Squash commits if requested

### Quality Gates

Before merging, ensure:
- [ ] All tests pass (`vendor/bin/co-phpunit`)
- [ ] Code style checks pass (`make cs-check`)
- [ ] Static analysis passes (`make phpstan-check`)
- [ ] Performance benchmarks acceptable
- [ ] Documentation updated
- [ ] GitHub Actions CI passes

## Development Tips

### Useful Commands

```bash
# Development workflow
make install        # Complete setup  
make start         # Start services
make app           # Enter container (alias for shell)
make shell         # Enter container
make logs          # View logs
make seed          # Generate test data

# Code quality
make cs-fix        # Fix code style
make cs-check      # Check code style
make phpstan-check # Static analysis
vendor/bin/co-phpunit # Run tests

# Database
make migrate       # Run migrations
make seed          # Seed test data
```

### Debugging

```bash
# View application logs
make logs

# Enter container for debugging
make shell

# Check service status
make status
```

### Common Issues

**Database Connection Issues:**
```bash
# Check database status
docker compose ps database
docker compose logs database

# Test connection
docker compose exec app bin/hyperf.php migrate
```

**Performance Issues:**
```bash
# Monitor resource usage
docker stats

# Check application health
curl http://localhost/health
make health
```

## Getting Help

- Check existing [issues](https://github.com/BeMySlaveDarlin/collider/issues)
- Review [documentation](docs/)
- Ask questions in discussions

## Continuous Integration

The project uses GitHub Actions for automated testing:

### Workflow Jobs

1. **Code Style Check** (`cs-check`)
   - Runs PHP CS Fixer in dry-run mode
   - Ensures code follows PSR-12 standards

2. **Static Analysis** (`phpstan-check`) 
   - Runs PHPStan analysis
   - Catches potential bugs and type issues

3. **End-to-End Tests** (`build-test`)
   - Sets up full Docker environment
   - Runs database migrations
   - Tests all API endpoints
   - Validates complete application flow

### Local CI Testing

Before pushing, run the same checks locally:

```bash
# Code style
make cs-check

# Static analysis  
make phpstan-check

# Full application test
make install
make api-test
```

## Release Process

Maintainers handle releases following semantic versioning:
- `MAJOR.MINOR.PATCH`
- Breaking changes increment MAJOR
- New features increment MINOR
- Bug fixes increment PATCH

Thank you for contributing to Collider!
