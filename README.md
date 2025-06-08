# User Analytics API

Low-performance event tracking system built with Swoole and Spiral Framework.

#### Based on task

https://gist.github.com/BeMySlaveDarlin/102194669142f0d70ce7d0412f2386b9

## Requirements

- **PHP 8 with**
  - Swoole
  - Redis
  - Spiral Framework
- **Web Server**
  - NGINX
- **Storages**
  - PgSQL
  - Redis
- **Misc**
  - Composer
  - Docker, docker-compose

## Quick Start

```bash
git clone git@github.com:BeMySlaveDarlin/collider.git
cd collider
make all
```

## Documentation

- [Installation Guide](docs/installation.md) - Setup and deployment instructions
- [API Documentation](docs/api.md) - REST API endpoints and examples
- [Console Commands](docs/console.md) - CLI tools and commands

## Features

- **High Performance**: Built with Swoole coroutines for maximum throughput
- **Scalable Architecture**: Domain-driven design with clean separation of concerns
- **Event Tracking**: Store and analyze millions of user events
- **Aggregated Statistics**: Real-time analytics and reporting
- **Bulk Operations**: Efficient batch processing for large datasets

## License

This project is licensed under the GPL-3.0 License.
