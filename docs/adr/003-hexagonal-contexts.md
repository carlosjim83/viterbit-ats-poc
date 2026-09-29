# ADR-003: Hexagonal with Bounded Contexts (no Ports/Adapters)

## Status

Accepted

## Context

The PoC requires DDD + Hexagonal architecture. The candidate prefers a **bounded contexts** organisational style and avoids "ports & adapters" terminology to simplify the mental model.

## Decision

Use **bounded contexts** as the primary organisational unit. Inside each context, three layers:

- `Domain/` — Aggregates, Entities, Value Objects, Events, **Repository interfaces**
- `Application/` — Commands, Queries, Handlers, Application Services
- `Infrastructure/` — Concrete implementations (Doctrine Repositories, Controllers, Templates, Messaging)

**Infrastructure interfaces** consumed by Domain (e.g., `LLMClientInterface`) live in `Domain/`.

## Consequences

- **Positive**: Clear structure; dependencies always point inward to Domain.
- **Positive**: Easy to navigate for PHP/Symfony developers.
- **Negative**: Requires discipline to avoid leaking infrastructure dependencies into Domain.

## Example structure

```
src/
└── Application/
    ├── Domain/
    │   ├── Model/
    │   ├── Event/
    │   └── Repository/
    ├── Application/
    │   ├── Command/
    │   ├── Query/
    │   └── Handler/
    └── Infrastructure/
        ├── Persistence/
        ├── Web/
        └── Messaging/
```

## References

- "Implementing Domain-Driven Design" — Vaughn Vernon
- "Hexagonal Architecture" — Alistair Cockburn
