# ADR-001: Symfony as PHP Framework

## Status

Accepted

## Context

The candidate is an experienced PHP backend developer with prior Symfony knowledge. The PoC requires a robust, testable web framework with strong support for DDD, CQRS, and hexagonal architecture.

## Decision

Use **Symfony 7.4+** (webapp mode) as the primary framework.

## Consequences

- **Positive**: Mature ecosystem, native integration with Doctrine ORM, Symfony Messenger, Twig, PHPUnit, and Flex autoconfiguration.
- **Positive**: Ecotone provides an official Symfony bundle.
- **Negative**: Steeper learning curve for newcomers; heavier than minimal frameworks.

## Alternatives considered

- Laravel: Less DDD/CQRS-oriented by default.
- No framework (vanilla): Too much overhead for a PoC with a UI.

## References

- https://symfony.com/doc/current/setup.html
