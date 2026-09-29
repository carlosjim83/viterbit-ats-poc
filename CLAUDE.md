# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is an **Application Tracking System (ATS) PoC** for Viterbit. It demonstrates:
- **DDD + Hexagonal Architecture** with bounded contexts
- **CQRS / Event-driven** messaging via Ecotone
- **Async AI Enrichment** (mocked LLM) for CV summaries and relevance scores
- **TDD** as the primary development methodology
- **PHP Symfony** backend with a simple web UI

## Tech Stack

- **Framework**: Symfony 7.4+ (webapp)
- **CQRS / Messaging**: Ecotone (symfony-bundle) — provides Command Bus, Query Bus, Event Bus
- **Persistence**: Doctrine ORM (via Symfony & Ecotone)
- **Async Transport**: Symfony Messenger (via Ecotone integration) or in-memory for local dev
- **Testing**: PHPUnit + EcotoneLite for flow/integration testing
- **Linting**: PHP-CS-Fixer, PHPStan (max level), Deptrac
- **Docs**: ADRs in `docs/adr/`

## Architecture Rules

### Hexagonal Structure with Contexts

We use **bounded contexts** (e.g., `Application`, `Enrichment`). Inside each context:

```
src/
└── Application/           -- one bounded context
    ├── Domain/
    │   ├── Model/         -- aggregates, entities, value objects
    │   ├── Event/         -- domain events
    │   └── Repository/      -- interfaces (contracts), NOT implementations
    ├── Application/
    │   ├── Command/       -- command DTOs
    │   ├── Query/         -- query DTOs
    │   ├── Handler/       -- command/query handlers
    │   └── Service/       -- application services
    └── Infrastructure/
        ├── Persistence/   -- Doctrine implementations of domain repository interfaces
        ├── Messaging/     -- Ecotone handlers, channels, transformers
        └── Web/           -- Symfony controllers, forms, templates
```

**Important:**
- **NO** `ports/` or `adapters/` directories.
- Domain repository **interfaces** live in `Domain/Repository/`.
- Infrastructure implementations live in `Infrastructure/Persistence/`.
- Infrastructure interfaces (e.g., `LLMClientInterface`) live in `Domain/` when consumed by domain/application layers.

### TDD Methodology (Mandatory)

Every class must follow **Red → Green → Refactor**:
1. Write the failing test first.
2. Write the minimum code to make it pass.
3. Refactor while keeping tests green.

**Never implement production code without a failing test first.**

### Commit Rules

- Commits must be **atomic** and **small** (baby steps).
- One logical change per commit.
- Follow conventional commit style where possible (e.g., `feat:`, `test:`, `refactor:`, `chore:`).
- The attribution line is handled automatically; do not add it manually.

## Ecotone Patterns

- Use `#[CommandHandler]` on application service methods to handle commands.
- Use `#[QueryHandler('routingKey')]` for query handlers.
- Use `#[EventHandler]` + `#[Asynchronous('channelName')]` for async event consumers.
- Use `EventBus` (injected) to publish domain events from aggregates/handlers.
- For testing async flows, use `EcotoneLite::bootstrapFlowTesting()` with `SimpleMessageChannelBuilder::createQueueChannel('name')`.

## Development Commands

### Project Setup
```bash
make init          # Install deps, run migrations, build assets
```

### Quality Tools
```bash
make cs-fix        # Run PHP-CS-Fixer
make stan          # Run PHPStan
make deptrac       # Run Deptrac architecture checks
make lint          # Run all linting tools
```

### Testing
```bash
make test          # Run full PHPUnit suite
make test-unit     # Run only unit tests
make test-integration  # Run only integration tests
```

### Symfony
```bash
symfony server:start   # Start local dev server
symfony console ...    # Run Symfony console commands
```

## Linting & Quality Gates

PHP-CS-Fixer, PHPStan, and Deptrac must be configured from minute zero and must pass before any commit.

## ADRs

All architectural decisions are documented as Architecture Decision Records in `docs/adr/`.
See existing ADRs for decisions already made.

## Constraints from Viterbit.md

- CV input is via textarea (plain text) — no file uploads or OCR.
- Mock LLM requests instead of using a real API.
- The UI is simple: Apply page, Applications list, Detail page.
- Tests must cover submission, filtering, and enrichment paths.
