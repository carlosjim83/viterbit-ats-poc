# Viterbit ATS PoC — Docker-based Makefile
# No commands should run directly on the host; always use `make <target>`.

.PHONY: init up down build test test-unit test-integration cs-fix stan deptrac lint sh validate

# ---------------------------------------------------------------------------
# Lifecycle
# ---------------------------------------------------------------------------

init: up
	docker compose exec php composer install --no-interaction --prefer-dist
	docker compose exec php php bin/console doctrine:database:create --if-not-exists
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build --no-cache

# ---------------------------------------------------------------------------
# Quality / Linting
# ---------------------------------------------------------------------------

cs-fix:
	docker compose exec php php vendor/bin/php-cs-fixer fix --diff --verbose

stan:
	docker compose exec php php vendor/bin/phpstan analyse --memory-limit=1G

deptrac:
	docker compose exec php php vendor/bin/deptrac analyse

lint: cs-fix stan deptrac

# ---------------------------------------------------------------------------
# Testing
# ---------------------------------------------------------------------------

test:
	docker compose exec php php vendor/bin/phpunit

test-unit:
	docker compose exec php php vendor/bin/phpunit --testsuite unit

test-integration:
	docker compose exec php php vendor/bin/phpunit --testsuite integration

# ---------------------------------------------------------------------------
# Validation (all quality gates)
# ---------------------------------------------------------------------------

validate: lint test

# ---------------------------------------------------------------------------
# Utils
# ---------------------------------------------------------------------------

sh:
	docker compose exec php bash
