include .env

.PHONY: all build up down restart logs shell composer help cs-check cs-fix cs-fix-diff psalm psalm-baseline psalm-fix psalm-info

all: var composer build up

var:
	mkdir -p var/cache
	mkdir -p var/log
	mkdir -p var/storage
	chmod -R 777 var

composer:
	docker compose run --rm composer

build:
	docker compose build

rebuild: down all

up:
	docker compose up -d

down:
	docker compose down
	rm -rf var/log/*
	rm -rf var/cache/*

restart:
	docker compose restart

logs:
	docker compose logs -f

shell:
	docker compose exec php bash

seed:
	docker-compose exec php php bin/app.php events:seed

cs-check:
	docker compose exec php php-cs-fixer fix --dry-run --diff

cs-fix:
	docker compose exec php php-cs-fixer fix

cs-fix-diff:
	docker compose exec php php-cs-fixer fix --diff

psalm:
	docker compose exec php psalm --no-cache

psalm-baseline:
	docker compose exec php psalm --set-baseline=psalm-baseline.xml

psalm-clear:
	./vendor/bin/psalm --clear-cache

psalm-config:
	./vendor/bin/psalm --init

help:
	@echo "Available commands:"
	@echo "  all         - Build and start containers"
	@echo "  build       - Build Docker images"
	@echo "  rebuild     - Rebuild Docker images"
	@echo "  up          - Start development environment"
	@echo "  down        - Stop development environment"
	@echo "  restart     - Restart all services"
	@echo "  logs        - Show logs"
	@echo "  shell       - Enter PHP container shell"
	@echo "  var         - Creates var dirs"
	@echo "  seed        - Seeds database with data"
	@echo "  composer    - Run composer install"
	@echo "  cs-check    - Check code style (dry-run)"
	@echo "  cs-fix      - Fix code style"
	@echo "  cs-fix-diff - Fix code style with diff"
