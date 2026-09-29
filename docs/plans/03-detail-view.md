# Feature 3: Detail View — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use tdd-workflow, hexagonal-contexts, cqrs-pattern, docker-only, atomic-commits.

**Goal:** A detail page showing all candidate data, original CV text, AI summary, AI score, status, and timestamps.

**Architecture:** CQRS Query `GetApplicationDetail` + `#[QueryHandler]`. Returns `ApplicationDetailDTO` with full data including nullable enrichment fields.

**Tech Stack:** Symfony 7.4, Ecotone, Doctrine ORM, PostgreSQL, Twig.

**Spec:** `Viterbit.md` — Section 2 (Detail Page)

## Global Constraints

- PHP >= 8.5
- Docker-only execution
- Red-Green-Refactor TDD for every class
- Atomic commits with conventional style
- Pre-commit hook runs lint + test

## Review Focus

- CV text displayed exactly as submitted (no truncation)
- AI summary and score visible when enrichment completed
- Status shows current value (received/enriching/enriched)
- Timestamps formatted for readability
- 404 when application ID not found

---

### Task 3.1: ApplicationDetailDTO

**Files:**
- Create: `src/Application/Application/DTO/ApplicationDetailDTO.php`
- Test: `tests/Unit/Application/Application/DTO/ApplicationDetailDTOTest.php`

**Interfaces:**
- Produces: Read-only DTO with all fields for detail view

- [ ] **Step 1-5:** (TDD) DTO with: `id`, `fullName`, `email`, `phone`, `position`, `notes`, `cvText`, `status`, `summary` (nullable), `score` (nullable), `appliedAt`
- [ ] **Step 5: Commit** `feat: add ApplicationDetailDTO`

---

### Task 3.2: GetApplicationDetail Query + Handler

**Files:**
- Create: `src/Application/Application/Query/GetApplicationDetail.php`
- Create: `src/Application/Application/Handler/GetApplicationDetailHandler.php`
- Test: `tests/Unit/Application/Application/Handler/GetApplicationDetailHandlerTest.php`
- Test: `tests/Integration/Application/Application/Handler/GetApplicationDetailHandlerTest.php`

**Interfaces:**
- Consumes: `JobApplicationRepository`, `ApplicationId`
- Produces: `#[QueryHandler('application.detail')]` returning `?ApplicationDetailDTO`

- [ ] **Step 1-5:** (TDD) Handler loads aggregate by ID, maps to `ApplicationDetailDTO`. Returns `null` if not found.
- [ ] **Step 5: Commit** `feat: add GetApplicationDetail query and handler`

---

### Task 3.3: Detail Page Controller + Twig Template

**Files:**
- Create: `src/Application/Infrastructure/Web/ApplicationDetailController.php`
- Create: `templates/application/detail.html.twig`
- Test: `tests/Integration/Application/Infrastructure/Web/ApplicationDetailControllerTest.php`

- [ ] **Step 1-5:** (TDD) Controller queries via Ecotone `QueryBus` with routing `application.detail`. Template shows all fields. 404 if application not found.
- [ ] **Step 5: Commit** `feat: add application detail page`
