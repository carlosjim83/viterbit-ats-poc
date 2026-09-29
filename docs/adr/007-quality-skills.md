# ADR-007: Automated Quality and Architecture Skills

## Status

Accepted

## Context

This is a technical test where quality, TDD rigour, and functional requirement compliance are direct evaluation criteria. We cannot allow a refactoring or feature to break architecture or leave code untested.

## Decision

After every significant implementation, **automated skills** (reviewable commands) validate quality using standard tools already configured in the project:

1. **Hexagonal Architecture**: `make deptrac` — verifies layer dependencies via Deptrac rules.
2. **TDD and Tests**: `make test` — PHPUnit suite must pass at 100%; every class in `src/` must have a corresponding test in `tests/`.
3. **Code Quality**: `make lint` — runs PHP-CS-Fixer + PHPStan + Deptrac; all must pass.
4. **Functional Requirements**: `make test` must cover submission, filtering, and enrichment paths; manual code review checks endpoint/handler existence.

No custom validation scripts are used; the existing linting and testing toolchain is the single source of truth.

Each skill is documented as a Claude Code skill file in `.claude/skills/`.

## Consequences

- **Positive**: Confidence that every commit maintains required quality.
- **Positive**: The reviewer can run `make validate` (lint + test) and verify everything at once.
- **Positive**: No redundant scripts to maintain; tools already enforce the rules.
- **Negative**: Additional time in each development cycle (acceptable for a quality PoC).

## References

- `.claude/skills/`
- `Makefile` (`validate` target)
