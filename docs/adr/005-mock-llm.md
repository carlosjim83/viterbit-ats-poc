# ADR-005: Mock LLM for CV Enrichment

## Status

Accepted

## Context

The PoC requires asynchronous CV enrichment (summary + relevance score). The requirements document (`Viterbit.md`) explicitly states: **"Mock LLM requests, don't use a real API"**.

## Decision

Implement a **mock LLM Client** that simulates latency and returns deterministic or semi-random responses based on the input.

The `LLMClientInterface` lives in `Domain/` and the mock implementation lives in `Infrastructure/LLM/`.

In a real environment, the implementation would be swapped for an OpenAI/Anthropic client without touching business logic.

## Consequences

- **Positive**: Satisfies the exercise constraint; fast; no API costs.
- **Positive**: Correctly demonstrates decoupling from external infrastructure.
- **Negative**: Does not reflect real LLM behaviour (acceptable for a PoC).

## References

- `Viterbit.md` — Constraints
