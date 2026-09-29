# Feature 1: Apply to a Job — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use tdd-workflow, hexagonal-contexts, docker-only, atomic-commits.

**Goal:** A candidate can submit an application with full name, contact info, position, notes, and CV text. The application is persisted and triggers async AI enrichment.

**Architecture:** Bounded context `Application`. Domain VO validation. Aggregate Root `JobApplication` with factory `submit()`. Command `SubmitApplication` + handler via Ecotone. Repository saves aggregate and publishes `ApplicationSubmitted` domain event.

**Tech Stack:** Symfony 7.4, Ecotone, Doctrine ORM, PostgreSQL, Twig.

**Spec:** `Viterbit.md` — Section 1 (Apply to a Job)

## Global Constraints

- PHP >= 8.5
- Docker-only execution
- Red-Green-Refactor TDD for every class
- Atomic commits with conventional style
- No secrets committed
- Pre-commit hook runs lint + test
- Domain never imports from Infrastructure

## Review Focus

- Empty CV text rejected
- Invalid email format rejected
- Duplicate application by same email for same position
- `appliedAt` auto-set on submission
- Initial status is `received`
- Async enrichment triggered after save

---

### Task 1.1: ApplicationId Value Object

**Files:**
- Create: `src/Application/Domain/Model/ValueObject/ApplicationId.php`
- Test: `tests/Unit/Application/Domain/Model/ValueObject/ApplicationIdTest.php`

**Interfaces:**
- Produces: `ApplicationId` — immutable UUID VO

- [ ] **Step 1: Write failing test** (generate unique, equals same value, fromString validates)
- [ ] **Step 2: Run to verify failure**
- [ ] **Step 3: Implement minimal `ApplicationId`**
- [ ] **Step 4: Run to verify pass**
- [ ] **Step 5: Commit** `feat: add ApplicationId value object`

---

### Task 1.2: Email Value Object

**Files:**
- Create: `src/Application/Domain/Model/ValueObject/Email.php`
- Test: `tests/Unit/Application/Domain/Model/ValueObject/EmailTest.php`

- [ ] **Step 1-5:** (TDD) Valid email accepted, invalid throws `InvalidArgumentException`
- [ ] **Step 5: Commit** `feat: add Email value object with validation`

---

### Task 1.3: FullName Value Object

**Files:**
- Create: `src/Application/Domain/Model/ValueObject/FullName.php`
- Test: `tests/Unit/Application/Domain/Model/ValueObject/FullNameTest.php`

- [ ] **Step 1-5:** (TDD) Non-empty string validated
- [ ] **Step 5: Commit** `feat: add FullName value object`

---

### Task 1.4: Position Value Object

**Files:**
- Create: `src/Application/Domain/Model/ValueObject/Position.php`
- Test: `tests/Unit/Application/Domain/Model/ValueObject/PositionTest.php`

- [ ] **Step 1-5:** (TDD) Non-empty string validated
- [ ] **Step 5: Commit** `feat: add Position value object`

---

### Task 1.5: CVText Value Object

**Files:**
- Create: `src/Application/Domain/Model/ValueObject/CVText.php`
- Test: `tests/Unit/Application/Domain/Model/ValueObject/CVTextTest.php`

- [ ] **Step 1-5:** (TDD) Non-empty text validated
- [ ] **Step 5: Commit** `feat: add CVText value object`

---

### Task 1.6: Status Value Object

**Files:**
- Create: `src/Application/Domain/Model/ValueObject/Status.php`
- Test: `tests/Unit/Application/Domain/Model/ValueObject/StatusTest.php`

- [ ] **Step 1-5:** (TDD) Factory methods: `received()`, `enriching()`, `enriched()`. Value stored internally.
- [ ] **Step 5: Commit** `feat: add Status value object`

---

### Task 1.7: ApplicationSubmitted Domain Event

**Files:**
- Create: `src/Application/Domain/Event/ApplicationSubmitted.php`
- Test: `tests/Unit/Application/Domain/Event/ApplicationSubmittedTest.php`

**Interfaces:**
- Consumes: `ApplicationId`
- Produces: Event with `applicationId`, `occurredOn`

- [ ] **Step 1-5:** (TDD) Event created with ID and timestamp
- [ ] **Step 5: Commit** `feat: add ApplicationSubmitted domain event`

---

### Task 1.8: JobApplication Aggregate Root

**Files:**
- Create: `src/Application/Domain/Model/JobApplication.php`
- Test: `tests/Unit/Application/Domain/Model/JobApplicationTest.php`

**Interfaces:**
- Consumes: All VOs + `ApplicationSubmitted`
- Produces: `JobApplication` with `submit()` factory, `events()` method

- [ ] **Step 1-5:** (TDD) Aggregate with:
  - `submit(...): self` — creates with `received` status, records `ApplicationSubmitted`
  - `events(): array` — returns recorded events
  - `appliedAt(): DateTimeImmutable`
- [ ] **Step 5: Commit** `feat: add JobApplication aggregate root`

---

### Task 1.9: JobApplicationRepository Interface

**Files:**
- Create: `src/Application/Domain/Repository/JobApplicationRepository.php`
- Test: `tests/Unit/Application/Domain/Repository/JobApplicationRepositoryTest.php` (contract test with in-memory impl)

- [ ] **Step 1-5:** (TDD) Interface: `save()`, `findById()`, `findAll()`
- [ ] **Step 5: Commit** `feat: add JobApplicationRepository interface`

---

### Task 1.10: Doctrine Repository Implementation

**Files:**
- Create: `src/Application/Infrastructure/Persistence/DoctrineJobApplicationRepository.php`
- Create: `src/Application/Infrastructure/Persistence/Doctrine/Type/ApplicationIdType.php`
- Test: `tests/Integration/Application/Infrastructure/Persistence/DoctrineJobApplicationRepositoryTest.php`

- [ ] **Step 1-5:** (TDD) Implement interface with Doctrine ORM + DBAL custom type for `ApplicationId`
- [ ] **Step 5: Commit** `feat: add Doctrine JobApplication repository`

---

### Task 1.11: SubmitApplication Command + Handler

**Files:**
- Create: `src/Application/Application/Command/SubmitApplication.php`
- Create: `src/Application/Application/Handler/SubmitApplicationHandler.php`
- Test: `tests/Unit/Application/Application/Handler/SubmitApplicationHandlerTest.php`
- Test: `tests/Integration/Application/Application/Handler/SubmitApplicationHandlerTest.php`

**Interfaces:**
- Consumes: `JobApplicationRepository`, `EventBus`
- Produces: `#[CommandHandler('application.submit')]`

- [ ] **Step 1-5:** (TDD) Handler creates aggregate, saves, publishes event, returns `ApplicationId`
- [ ] **Step 5: Commit** `feat: add SubmitApplication command and handler`

---

### Task 1.12: Apply Page Controller + Twig Template

**Files:**
- Create: `src/Application/Infrastructure/Web/ApplyController.php`
- Create: `templates/application/apply.html.twig`
- Test: `tests/Integration/Application/Infrastructure/Web/ApplyControllerTest.php`

- [ ] **Step 1-5:** (TDD) Form with fields: fullName, email, phone, position, notes, cvText. On submit, dispatches command via Ecotone `CommandBus`. Redirects to list.
- [ ] **Step 5: Commit** `feat: add Apply page controller and template`

---

### Task 1.13: Database Migration

**Files:**
- Create: `migrations/Version...php`

- [ ] **Step 1-5:** Doctrine migration for `job_applications` table with all fields
- [ ] **Step 5: Commit** `chore: add job_applications migration`
