include .env

.PHONY: help install start stop restart build rebuild logs shell composer app status test seed migrate gen-ide

help:
	@echo "Available commands:"
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<command>\033[0m\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

install: runtime composer start

runtime:
	mkdir -p runtime/logs
	mkdir -p runtime/caches
	chmod -R 777 runtime

build:
	docker compose build

rebuild: stop build start

start:
	docker compose up -d

stop:
	docker compose down

restart: stop clear-cache start

clear-cache:
	rm -rf runtime/container/*

composer:
	docker compose run --rm composer

app:
	docker compose exec app bash

shell: app

logs:
	docker compose logs -f app

status:
	docker compose ps

migrate:
	docker compose exec app php bin/hyperf.php migrate

seed:
	docker compose exec app php bin/hyperf.php events:seed

watch:
	docker compose exec app php bin/hyperf.php server:watch

info:
	docker compose exec app php bin/hyperf.php

health:
	curl http://localhost/health

clean: stop
	docker compose down -v
	docker system prune -f

reset: clean install

cs-check:
	docker compose exec app php vendor/bin/php-cs-fixer fix --dry-run

cs-fix:
	docker compose exec app php vendor/bin/php-cs-fixer fix

phpstan-check:
	docker compose exec app php vendor/bin/phpstan analyse --memory-limit 1G

api-test:
	@echo "Testing API endpoints..."
	curl "http://localhost/events?page=1&limit=10"

load-test-ab:
	@echo "Running basic load test..."
	./bin/ab_test.sh http://localhost 123 user.registered

load-test-wrk:
	@echo "Running basic load test..."
	./bin/wrk_test.sh http://localhost 123 user.registered
