# Feature 4: AI Enrichment (Async) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use tdd-workflow, hexagonal-contexts, async-messaging, docker-only, atomic-commits.

**Goal:** After application submission, an async task enriches the CV with a summary and relevance score using a mock LLM. The result updates the application.

**Architecture:** `ApplicationSubmitted` domain event triggers async `#[EventHandler]` on `enrichment` channel. Handler calls `LLMClientInterface` (mock), then calls aggregate methods to store result.

**Tech Stack:** Symfony 7.4, Ecotone, Symfony Messenger (or in-memory), Mock LLM.

**Spec:** `Viterbit.md` — Section 4 (AI Enrichment)

## Global Constraints

- PHP >= 8.5
- Docker-only execution
- Red-Green-Refactor TDD for every class
- Atomic commits with conventional style
- Pre-commit hook runs lint + test
- Mock LLM (no real API)
- Async via Ecotone `#[Asynchronous]`

## Review Focus

- Enrichment starts after submission (event-driven)
- Mock LLM returns deterministic-ish summary + score 0-100
- Enrichment updates application status from `enriching` to `enriched`
- Result visible in detail view and list (score column)
- Failed enrichment handled gracefully (status stays `enriching` or `failed`)

---

### Task 4.1: LLMClientInterface

**Files:**
- Create: `src/Application/Domain/LLMClientInterface.php`
- Test: `tests/Unit/Application/Domain/LLMClientInterfaceTest.php` (contract / dummy test)

**Interfaces:**
- Produces: Interface with `enrich(string $cvText, string $position): array` returning `['summary' => string, 'score' => int]`

- [ ] **Step 1-5:** (TDD) Interface defined in Domain. No implementation yet.
- [ ] **Step 5: Commit** `feat: add LLMClientInterface`

---

### Task 4.2: MockLLMClient Implementation

**Files:**
- Create: `src/Application/Infrastructure/LLM/MockLLMClient.php`
- Test: `tests/Unit/Application/Infrastructure/LLM/MockLLMClientTest.php`

**Interfaces:**
- Consumes: `LLMClientInterface`
- Produces: Mock implementation returning deterministic summary + random-ish score

- [ ] **Step 1-5:** (TDD) Mock generates summary based on keywords in CV text + position. Score is `strlen($cvText) % 100` or similar deterministic formula. Returns `['summary' => string, 'score' => int]`.
- [ ] **Step 5: Commit** `feat: add MockLLMClient implementation`

---

### Task 4.3: Aggregate Enrichment Methods

**Files:**
- Modify: `src/Application/Domain/Model/JobApplication.php`
- Test: `tests/Unit/Application/Domain/Model/JobApplicationTest.php`

**Interfaces:**
- Produces: `requestEnrichment()`, `completeEnrichment(string $summary, int $score)`

- [ ] **Step 1-5:** (TDD) Add methods to aggregate:
  - `requestEnrichment(): void` — transitions status to `enriching`
  - `completeEnrichment(string $summary, int $score): void` — stores summary/score, transitions to `enriched`
- [ ] **Step 5: Commit** `feat: add enrichment methods to JobApplication aggregate`

---

### Task 4.4: EnrichmentRequested Domain Event

**Files:**
- Create: `src/Application/Domain/Event/EnrichmentRequested.php`
- Test: `tests/Unit/Application/Domain/Event/EnrichmentRequestedTest.php`

- [ ] **Step 1-5:** (TDD) Event with `applicationId`
- [ ] **Step 5: Commit** `feat: add EnrichmentRequested domain event`

---

### Task 4.5: SubmitApplicationHandler Publishes EnrichmentRequested

**Files:**
- Modify: `src/Application/Application/Handler/SubmitApplicationHandler.php`
- Test: `tests/Unit/Application/Application/Handler/SubmitApplicationHandlerTest.php`

- [ ] **Step 1-5:** (TDD) After saving application, handler also publishes `EnrichmentRequested` event (in addition to `ApplicationSubmitted`). Or: `ApplicationSubmitted` is consumed by enrichment handler directly.
- [ ] **Step 5: Commit** `feat: trigger enrichment request on submission`

---

### Task 4.6: Async Enrichment Event Handler

**Files:**
- Create: `src/Application/Infrastructure/Messaging/EnrichmentHandler.php`
- Test: `tests/Integration/Application/Infrastructure/Messaging/EnrichmentHandlerTest.php`

**Interfaces:**
- Consumes: `ApplicationSubmitted` (or `EnrichmentRequested`), `JobApplicationRepository`, `LLMClientInterface`
- Produces: `#[Asynchronous('enrichment')]` + `#[EventHandler]`

- [ ] **Step 1-5:** (TDD) Handler:
  1. Loads `JobApplication` by ID from event
  2. Calls `requestEnrichment()` on aggregate
  3. Saves (status = enriching)
  4. Calls `LLMClientInterface::enrich()`
  5. Calls `completeEnrichment()` with result
  6. Saves updated aggregate
- [ ] **Step 5: Commit** `feat: add async enrichment event handler`

---

### Task 4.7: Ecotone Async Configuration

**Files:**
- Modify: `config/packages/messenger.yaml` or Ecotone config
- Test: `tests/Integration/EndToEnd/EnrichmentFlowTest.php`

- [ ] **Step 1-5:** Configure `enrichment` channel. For local dev use in-memory or Symfony Messenger transport. Test with `EcotoneLite::bootstrapFlowTesting()` + `SimpleMessageChannelBuilder::createQueueChannel('enrichment')`.
- [ ] **Step 5: Commit** `chore: configure Ecotone enrichment channel`

---

### Task 4.8: End-to-End Enrichment Flow Test

**Files:**
- Create: `tests/Integration/EndToEnd/EnrichmentFlowTest.php`

- [ ] **Step 1-5:** (TDD) Full flow:
  1. Submit application via command bus
  2. Run `enrichment` channel consumer
  3. Query application detail — verify status = `enriched`, summary and score populated
- [ ] **Step 5: Commit** `test: add end-to-end enrichment flow test`
