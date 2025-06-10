# Console Commands

## Available Commands

### Database Seeding

#### events:seed

Generate test data for performance testing.

**Usage:**

```bash
bin/hyperf.php events:seed
```

**Docker:**

```bash
docker-compose exec php bin/hyperf.php events:seed
# OR
make seed
```

**Description:**

- Creates 10,000,000 events
- Generates random users and event types
- Populates metadata with realistic data
- Shows progress during execution

### Database Migrations

#### migrate

Run database migrations.

**Usage:**

```bash
bin/hyperf.php migrate
```

**Docker:**

```bash
docker-compose exec php bin/hyperf.php migrate
```

#### migrate:rollback

Rollback database migrations.

**Usage:**

```bash
bin/hyperf.php migrate:rollback
```

#### migrate:status

Show migration status.

**Usage:**

```bash
bin/hyperf.php migrate:status
```

### Server Commands

#### start

Start Swoole HTTP server.

**Usage:**

```bash
bin/hyperf.php start
```

#### server:watch

Start server with hot-reload for development.

**Usage:**

```bash
bin/hyperf.php server:watch
```

### Development Commands

#### Makefile Shortcuts

```bash
# Complete setup
make install

# Start containers
make start

# Stop containers
make stop

# View logs
make logs

# Enter PHP container
make shell

# Seed database
make seed

# Run migrations
make migrate
```
