# Installation

## Requirements

- PHP 8.3+
- Swoole 6.0+
- Docker & Docker Compose
- PostgreSQL 15
- Redis 7
- Composer

## Quick Start

### 1. Clone and Setup

```bash
git clone git@github.com:BeMySlaveDarlin/collider.git
cd collider
make all
```

### 2. Environment

Copy environment configuration:

```bash
cp .env.example .env
```

### 3. Database Setup

Start services:

```bash
docker-compose up -d
```

Run migrations:

```bash
docker-compose exec php php bin/app.php migrate
```

### 4. Seed Data

Generate test data:

```bash
docker-compose exec php php bin/app.php events:seed
```

## Manual Installation

### Dependencies

```bash
composer install
```

### Database

Configure PostgreSQL connection in `.env`:

```env
DB_CONNECTION=postgres
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=app_db
DB_USERNAME=app_user
DB_PASSWORD=secret
```

### Redis

Configure Redis connection in `.env`:

```env
REDIS_HOST=localhost
REDIS_PORT=6379
```

### Migrations

Run database migrations:

```bash
php bin/app.php migrate
```

### Start Server

```bash
php bin/http.php start
```

## Docker Configuration

### Services

- `php` - PHP 8.1 with Swoole
- `nginx` - Web server (ports 80/443)
- `database` - PostgreSQL 15 (port 5432)
- `redis` - Redis 7

### Useful Commands

```bash
# Build containers
make build

# Start environment
make up

# Stop environment
make down

# View logs
make logs

# Enter PHP container
make shell

# Seed database
make seed
```

## Performance Tuning

### PostgreSQL

Edit `docker/database/postgresql.conf`:

- `shared_buffers = 256MB`
- `effective_cache_size = 1GB`
- `work_mem = 16MB`

### Swoole

Configure in `.env`:

```env
SWOOLE_WORKER_NUM=4
SWOOLE_TASK_WORKER_NUM=4
SWOOLE_MAX_REQUEST=10000
```
