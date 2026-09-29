# ADR-002: Ecotone for CQRS and Async Messaging

## Status

Accepted

## Context

The PoC requires CQRS (separate commands from queries), event-driven architecture, and asynchronous processing (AI CV enrichment). We need a solution that integrates well with Symfony and minimizes boilerplate.

## Decision

Use **Ecotone** (`ecotone/symfony-bundle`) as the messaging and CQRS layer.

## Consequences

- **Positive**: Messages (Commands, Events, Queries) as first-class citizens via PHP attributes (`#[CommandHandler]`, `#[EventHandler]`, `#[QueryHandler]`).
- **Positive**: Native async support with configurable channels (in-memory, Symfony Messenger, AMQP, etc.).
- **Positive**: `EcotoneLite` for testing asynchronous flows in-memory without external infrastructure.
- **Negative**: Additional dependency; smaller community than raw Symfony Messenger.

## Alternatives considered

- Symfony Messenger alone: More boilerplate required for CQRS and event sourcing patterns.
- Laravel Queues: Not applicable (we use Symfony).

## References

- https://docs.ecotone.tech/
- Context7: `/ecotoneframework/documentation`
