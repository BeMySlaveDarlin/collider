# Console Commands

## Available Commands

### Database Seeding

#### events:seed

Generate test data for performance testing.

**Usage:**

```bash
php bin/app.php events:seed
```

**Docker:**

```bash
docker-compose exec php php bin/app.php events:seed
make seed
```

**Description:**

- Creates 10,000,000 events
- Generates random users and event types
- Populates metadata with realistic data
- Shows progress during execution

**Output Example:**

```
Starting database seeding...
Creating event types...
Creating users...
Creating events batch 1/1000...
Creating events batch 2/1000...
...
Database seeding completed.
```

**Performance:**

- Uses batch insertions for efficiency
- Optimized for high-performance bulk operations
- Memory-efficient processing

### Framework Commands

#### migrate

Run database migrations.

**Usage:**

```bash
php bin/app.php migrate
```

**Docker:**

```bash
docker-compose exec php php bin/app.php migrate
```

#### cycle:schema

Generate ORM schema.

**Usage:**

```bash
php bin/app.php cycle:schema
```

### Server Commands

#### start

Start Swoole HTTP server.

**Usage:**

```bash
php bin/http.php start
```

**Options:**

- Starts on port 8080 by default
- Uses Swoole coroutines for performance
- Supports hot reload in development

### Development Commands

#### Makefile Shortcuts

**Build and start:**

```bash
make all
```

**Start containers:**

```bash
make up
```

**Stop containers:**

```bash
make down
```

**View logs:**

```bash
make logs
```

**Enter PHP container:**

```bash
make shell
```

**Seed database:**

```bash
make seed
```

**Rebuild containers:**

```bash
make rebuild
```

## Command Options

### Global Options

All commands support standard Spiral Framework options:

- `-h, --help` - Display help
- `-q, --quiet` - Suppress output
- `-v, --verbose` - Increase verbosity
- `--env` - Specify environment

### Environment Variables

Commands respect `.env` configuration:

```env
APP_ENV=development
DB_CONNECTION=postgres
DB_HOST=database
DB_PORT=5432
DB_DATABASE=app_db
DB_USERNAME=app_user
DB_PASSWORD=secret
```

## Custom Commands

To create custom commands:

1. Create command class in `src/Endpoint/Console/`
2. Extend `Spiral\Console\Command`
3. Add `#[AsCommand]` attribute
4. Register in `ConsoleBootloader`

**Example:**

```php
#[AsCommand(
    name: 'custom:command',
    description: 'Custom command description'
)]
final class CustomCommand extends Command
{
    protected function perform(): int
    {
        $this->info('Custom command executed');
        return self::SUCCESS;
    }
}
```
