# Collider

Low-performance event tracking system built with Swoole and Hyperf Framework.

Based on task: https://gist.github.com/BeMySlaveDarlin/102194669142f0d70ce7d0412f2386b9

## Requirements

- Docker & Docker Compose
- cmake
- git

## Tech Stack

- **Runtime**: PHP 8.3 with Swoole 5.0
- **Framework**: Hyperf 3.1
- **Database**: PostgreSQL 15
- **Cache**: Redis 7
- **Web Server**: NGINX

## Quick Start

```bash
git clone git@github.com:BeMySlaveDarlin/collider.git
cd collider
make install
```

## Documentation

- [Installation Guide](docs/installation.md) - Setup and deployment instructions
- [API Documentation](docs/api.md) - REST API endpoints and examples
- [Console Commands](docs/console.md) - CLI tools and commands

## Features

- Event tracking and storage
- User analytics
- Bulk operations
- Real-time statistics
- Coroutine-based processing

## License

This project is licensed under the GPL-3.0 License.
