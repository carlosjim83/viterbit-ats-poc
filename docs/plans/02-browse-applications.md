# Feature 2: Browse Applications — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use tdd-workflow, hexagonal-contexts, cqrs-pattern, docker-only, atomic-commits.

**Goal:** A page listing all applications newest first, with real-time filtering by status and position, and simple search by candidate name or email.

**Architecture:** CQRS Query `ListApplications` + `#[QueryHandler]`. Query bypasses domain and reads directly from repository or optimized projection. Controller renders Twig with results.

**Tech Stack:** Symfony 7.4, Ecotone, Doctrine ORM, PostgreSQL, Twig.

**Spec:** `Viterbit.md` — Section 2 (Browse Applications)

## Global Constraints

- PHP >= 8.5
- Docker-only execution
- Red-Green-Refactor TDD for every class
- Atomic commits with conventional style
- Pre-commit hook runs lint + test

## Review Focus

- Newest-first ordering (`appliedAt` DESC)
- Filtering by status + position simultaneously (AND logic)
- Search by partial name or email (case-insensitive, LIKE)
- Empty list handled gracefully
- Pagination not required for PoC (nice-to-have)

---

### Task 2.1: ApplicationDTO

**Files:**
- Create: `src/Application/Application/DTO/ApplicationDTO.php`
- Test: `tests/Unit/Application/Application/DTO/ApplicationDTOTest.php`

**Interfaces:**
- Produces: Read-only DTO with all display fields

- [ ] **Step 1-5:** (TDD) DTO with: `id`, `fullName`, `email`, `position`, `status`, `appliedAt`, `score` (nullable)
- [ ] **Step 5: Commit** `feat: add ApplicationDTO`

---

### Task 2.2: ListApplications Query + Handler

**Files:**
- Create: `src/Application/Application/Query/ListApplications.php`
- Create: `src/Application/Application/Handler/ListApplicationsHandler.php`
- Test: `tests/Unit/Application/Application/Handler/ListApplicationsHandlerTest.php`
- Test: `tests/Integration/Application/Application/Handler/ListApplicationsHandlerTest.php`

**Interfaces:**
- Consumes: `JobApplicationRepository`
- Produces: `#[QueryHandler('application.list')]` returning `ApplicationDTO[]`

- [ ] **Step 1-5:** (TDD) Handler accepts optional `status`, `position`, `search` params. Returns `ApplicationDTO[]` ordered by `appliedAt` DESC. Search matches `fullName` or `email` case-insensitive.
- [ ] **Step 5: Commit** `feat: add ListApplications query and handler`

---

### Task 2.3: Extend Repository for Filtering

**Files:**
- Modify: `src/Application/Domain/Repository/JobApplicationRepository.php`
- Modify: `src/Application/Infrastructure/Persistence/DoctrineJobApplicationRepository.php`
- Test: `tests/Unit/Application/Domain/Repository/JobApplicationRepositoryTest.php`
- Test: `tests/Integration/Application/Infrastructure/Persistence/DoctrineJobApplicationRepositoryTest.php`

**Interfaces:**
- Consumes: Filter criteria (status, position, search)
- Produces: Filtered results

- [ ] **Step 1-5:** (TDD) Add `findByCriteria(array $criteria): array` to interface and Doctrine impl. Criteria keys: `status`, `position`, `search`. Search uses `LIKE` on `fullName` and `email`.
- [ ] **Step 5: Commit** `feat: extend repository with search and filter`

---

### Task 2.4: Applications List Controller + Twig Template

**Files:**
- Create: `src/Application/Infrastructure/Web/ListApplicationsController.php`
- Create: `templates/application/list.html.twig`
- Test: `tests/Integration/Application/Infrastructure/Web/ListApplicationsControllerTest.php`

- [ ] **Step 1-5:** (TDD) Controller queries via Ecotone `QueryBus` with routing `application.list`. Accepts query params: `status`, `position`, `search`. Template shows table with filters and search box.
- [ ] **Step 5: Commit** `feat: add applications list page with filters and search`

---

### Task 2.5: Seed Data for Manual Testing

**Files:**
- Create: `src/Application/Infrastructure/Persistence/DataFixtures/ApplicationFixtures.php`
- Modify: `config/services.yaml` (if needed)

- [ ] **Step 1-5:** Create 5-10 sample applications with varied statuses, positions, and names for manual browser testing.
- [ ] **Step 5: Commit** `chore: add application fixtures for testing`
