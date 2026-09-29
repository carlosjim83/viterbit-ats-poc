# ADR-007: Automated Quality and Architecture Skills

## Status

Accepted

## Context

This is a technical test where quality, TDD rigour, and functional requirement compliance are direct evaluation criteria. We cannot allow a refactoring or feature to break architecture or leave code untested.

## Decision

After every significant implementation, **automated skills** (reviewable scripts/commands) will validate:

1. **Hexagonal Architecture** (`validate-architecture`):
   - Folder structure respected (Domain/Application/Infrastructure per context).
   - No imports from Infrastructure into Domain or Application.
   - Deptrac passes without errors.

2. **TDD and Coverage** (`validate-tdd`):
   - Every class in `src/` has a corresponding test in `tests/`.
   - PHPUnit passes at 100%.
   - No dead code (unused classes).

3. **Code Quality** (`validate-quality`):
   - PHP-CS-Fixer reports no errors.
   - PHPStan at maximum level passes without errors.

4. **Functional Requirements** (`validate-requirements`):
   - Required endpoints/controllers exist (Apply, List, Detail).
   - Handlers exist for key commands/queries.
   - Mock LLM and async flow are implemented.

Each skill is defined as an executable script (`bin/validate-*` or Make target) and documented in `.claude/skills/`.

## Consequences

- **Positive**: Confidence that every commit maintains required quality.
- **Positive**: The reviewer can run `make validate` and verify everything at once.
- **Negative**: Additional time in each development cycle (acceptable for a quality PoC).

## References

- `.claude/skills/`
- `Makefile` (`validate-*` targets)
