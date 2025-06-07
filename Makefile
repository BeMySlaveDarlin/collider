include .env

.PHONY: all build up down restart logs shell composer swoole queue migrate cs cs-fix psalm phpstan help

all: var composer build up

var:
	mkdir -p var/cache
	mkdir -p var/log
	mkdir -p var/runtime
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

restart:
	docker compose restart

logs:
	docker compose logs -f

shell:
	docker compose exec php bash

help:
	@echo "Available commands:"
	@echo "  all         - Build and start containers"
	@echo "  build       - Build Docker images"
	@echo "  up          - Start development environment"
	@echo "  down        - Stop development environment"
	@echo "  restart     - Restart all services"
	@echo "  logs        - Show logs"
	@echo "  shell       - Enter PHP container shell"
	@echo "  composer    - Run composer install"
