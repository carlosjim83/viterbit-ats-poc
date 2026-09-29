# ADR-004: PHP-CS-Fixer, PHPStan, and Deptrac from Minute Zero

## Status

Accepted

## Context

Code quality is an explicit evaluation criterion for the PoC. The codebase must be clean, strictly typed, and must respect architectural rules.

## Decision

Configure from project inception:

- **PHP-CS-Fixer**: Consistent code style (PSR-12 + Symfony rules).
- **PHPStan**: Static analysis at maximum level (`level: max` / `level: 10`).
- **Deptrac**: Dependency verification between layers (Domain → Application → Infrastructure).

All commits must pass the three tools before being pushed.

## Consequences

- **Positive**: Early prevention of technical debt; immediate feedback in CI/local.
- **Positive**: Deptrac guarantees hexagonal architecture boundaries are not violated.
- **Negative**: Initial configuration overhead; may require tweaks in early iterations.

## References

- https://github.com/PHP-CS-Fixer/PHP-CS-Fixer
- https://phpstan.org/
- https://github.com/qossmic/deptrac
