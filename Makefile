include .env

.PHONY: all build up down restart logs shell composer help

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
	rm -rf var/cache/*
	rm -rf var/runtime/*

restart:
	docker compose restart

logs:
	docker compose logs -f

shell:
	docker compose exec php bash

seed:
	docker-compose exec php php bin/app.php events:seed

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
