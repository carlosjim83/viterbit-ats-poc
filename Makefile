# Viterbit ATS PoC — Docker-based Makefile
# No commands should run directly on the host; always use `make <target>`.

.PHONY: init up down build test test-unit test-integration behat cs-fix stan deptrac lint sh validate install-hooks

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

behat:
	docker compose exec -e APP_ENV=test php vendor/bin/behat --format=progress

# ---------------------------------------------------------------------------
# Validation (all quality gates)
# ---------------------------------------------------------------------------

validate: lint test behat

# ---------------------------------------------------------------------------
# Git Hooks
# ---------------------------------------------------------------------------

install-hooks:
	cp tools/git-hooks/pre-commit .git/hooks/pre-commit
	chmod +x .git/hooks/pre-commit
	@echo "Git pre-commit hook installed."

# ---------------------------------------------------------------------------
# Utils
# ---------------------------------------------------------------------------

sh:
	docker compose exec php bash
