# ADR-006: Docker and Makefile as the Only Runtime Environment

## Status

Accepted

## Context

The PoC reviewer must not waste time installing PHP, Composer, extensions, or managing local versions. The project must be runnable with a single command (`make init`) regardless of the reviewer's operating system.

## Decision

Use **Docker** (Docker Compose) as the sole execution and development environment. No Composer, PHPUnit, or Symfony commands should run directly on the host; everything goes through containers.

A **Makefile** acts as the single interface: every Make target invokes `docker compose exec` or `docker compose run` with the corresponding commands.

## Consequences

- **Positive**: Total reproducibility; onboarding in seconds; dependency isolation.
- **Positive**: The reviewer only needs Docker and Make installed.
- **Negative**: Docker overhead locally; slight latency on frequent commands.

## Required Makefile targets

| Target | Description |
|--------|-------------|
| `make init` | Start containers, install dependencies, run migrations |
| `make up` | Start services in the background |
| `make down` | Stop and remove containers |
| `make build` | Rebuild images |
| `make test` | Run the full PHPUnit suite |
| `make cs-fix` | Run PHP-CS-Fixer |
| `make stan` | Run PHPStan |
| `make deptrac` | Run Deptrac architecture checks |
| `make lint` | Run cs-fix + stan + deptrac |
| `make sh` | Open a shell in the PHP container |

## References

- https://docs.docker.com/compose/
