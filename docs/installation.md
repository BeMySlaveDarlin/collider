# Installation Guide

## Requirements

- PHP 8.3+
- Swoole 5.0+
- PostgreSQL 15
- Redis 7
- Docker & Docker Compose
- Composer 2.x

### PHP Extensions

- `ext-swoole` (>=5.0)
- `ext-pdo`
- `ext-pdo_pgsql`
- `ext-redis`
- `ext-json`
- `ext-openssl`
- `ext-pcntl`

## Quick Start

### 1. Clone Repository

```bash
git clone git@github.com:BeMySlaveDarlin/collider.git
cd collider
```

### 2. Configuration

Copy the example environment file:

```bash
cp .env.example .env
```

### 3. Automated Setup

Using Make:

```bash
make install
```

This command will:

- Build Docker containers
- Install dependencies
- Run database migrations
- Start all services

If you prefer manual setup:

```bash
# Start Docker containers
docker-compose up -d

# Install PHP dependencies
docker-compose exec php composer install

# Run database migrations
docker-compose exec php bin/hyperf.php migrate

# Seed test data (optional)
docker-compose exec php bin/hyperf.php events:seed
```

## Docker Services

| Service  | Port    | Description               |
|----------|---------|---------------------------|
| php      | 9501    | Hyperf/Swoole application |
| nginx    | 80, 443 | Web server                |
| database | 5432    | PostgreSQL 15             |
| redis    | 6379    | Redis cache               |

## Starting the Application

### Development Mode

```bash
# Using Hyperf's built-in server
docker-compose exec php bin/hyperf.php start

# With hot-reload
docker-compose exec php bin/hyperf.php server:watch
```

### Production Mode

```bash
# Set environment
export APP_ENV=production

# Start with optimizations
docker-compose exec php bin/hyperf.php start
```

## Seeding Test Data

Generate 10 million test events:

```bash
# Using Make
make seed

# Or manually
docker-compose exec php bin/hyperf.php events:seed
```

## Available Make Commands

```bash
make install     # Complete setup
make start       # Start containers
make stop        # Stop containers
make logs        # View logs
make shell       # Enter PHP container
make seed        # Seed database
make migrate     # Run migrations
```

## Next Steps

1. Seed test data for development
2. Review [API Documentation](api.md)
3. Explore [Console Commands](console.md)
