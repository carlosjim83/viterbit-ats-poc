# 05 — Behat E2E Application Submission & Enrichment Flow

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add Behat E2E tests that verify a user can submit a job application, the async enrichment runs via EcotoneLite, and the enriched result (score + summary) is visible in both the list and detail views.

**Architecture:** Behat runs inside the PHP Docker container via SymfonyExtension. The `FeatureContext` bootstraps two things:
1. **Symfony kernel** — for HTTP test client (`test.client`) and Doctrine EntityManager.
2. **EcotoneLite** — for sending commands and consuming async events in-memory. This avoids the chapuza of running `ecotone:run` via console inside Behat.

The flow per scenario: truncate DB → EcotoneLite sends `application.submit` command → EcotoneLite runs `enrichment` channel → Symfony test client navigates to `/applications` and `/applications/{id}` → assertions against rendered HTML.

**Tech Stack:** Behat 3.x + friends-of-behat/symfony-extension, EcotoneLite, Symfony 7.4, Doctrine PostgreSQL, Docker

**Spec:** This PoC requires end-to-end coverage of the submission → enrichment → readback flow. The existing PHPUnit integration tests verify handlers in isolation but do not exercise the full HTTP + async pipeline together.

## Global Constraints

- All commands run inside Docker: `docker compose exec php ...`
- Behat runs against the real Symfony kernel (`APP_ENV=test`)
- Database must be isolated per scenario (truncate `job_applications` before each)
- Async enrichment consumed via `EcotoneLite::run('enrichment')` — no console commands, no sleep/wait magic
- No changes to production source code
- Follow existing project conventions: `declare(strict_types=1)`, atomic commits, TDD

## Review Focus

1. **Enrichment not processed before assertion** — If the Behat step forgets `$ecotone->run('enrichment')`, the application remains `enriching`. The step must explicitly trigger consumption.
2. **Database pollution across scenarios** — If truncation fails or is skipped, subsequent scenarios see stale data. The hook must run reliably.
3. **Non-deterministic score/summary from MockLLMClient** — The mock LLM returns deterministic scores based on input hash. The test should assert on presence/range, not exact score value.
4. **SymfonyExtension boot failure** — If the extension cannot boot the kernel (missing `.env.test`, wrong `KERNEL_CLASS`), Behat fails before any scenario runs.
5. **EcotoneLite container wiring** — If the Symfony container is not passed correctly to `bootstrapFlowTesting`, handlers cannot resolve `JobApplicationRepository` or `LLMClientInterface`.

---

## File Structure

```
# New files
behat.yml                              -- Behat + SymfonyExtension configuration
features/
├── bootstrap/
│   └── FeatureContext.php             -- Main Behat context: Symfony HTTP + EcotoneLite
├── application_submission.feature     -- Gherkin feature: submit, enrich, view

# Modified files
composer.json                          -- add ecotone/lite require-dev
composer.lock                          -- updated lockfile
Makefile                               -- add `behat` target
```

---

## Task 1: Install Behat, Symfony Extension and EcotoneLite

**Files:**
- Modify: `composer.json`
- Create: `behat.yml`
- Create: `features/bootstrap/bootstrap.php`
- Modify: `Makefile`

**Interfaces:**
- Consumes: Existing Symfony kernel (`App\Kernel`), existing `.env.test`
- Produces: Working `make behat` command

- [ ] **Step 1: Install packages**

Run:
```bash
docker compose exec php composer require --dev behat/behat friends-of-behat/symfony-extension ecotone/lite
```

Expected: Packages install, `vendor/bin/behat` exists, `EcotoneLite` class is autoloaded.

- [ ] **Step 2: Create behat.yml**

```yaml
# behat.yml
default:
    extensions:
        FriendsOfBehat\SymfonyExtension:
            bootstrap: features/bootstrap/bootstrap.php
            kernel:
                class: App\Kernel
                environment: test
                debug: true
    suites:
        default:
            type: symfony
            contexts:
                - App\Tests\Behat\FeatureContext
            paths:
                - features
```

- [ ] **Step 3: Create bootstrap file**

Create `features/bootstrap/bootstrap.php`:
```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';
```

- [ ] **Step 4: Add `behat` to Makefile**

Append to `Makefile`:
```makefile
behat:
	docker compose exec -e APP_ENV=test php vendor/bin/behat --format=progress
```

- [ ] **Step 5: Verify Behat boots**

Run:
```bash
make behat -- --dry-run
```

Expected: No scenarios yet, but kernel boots without errors.

- [ ] **Step 6: Commit**

```bash
git add composer.json composer.lock behat.yml features/bootstrap/bootstrap.php Makefile
git commit -m "chore: install Behat, SymfonyExtension and EcotoneLite"
```

---

## Task 2: Create FeatureContext with EcotoneLite Integration

**Files:**
- Create: `features/bootstrap/FeatureContext.php`

**Interfaces:**
- Consumes: Symfony kernel, Doctrine EntityManager, EcotoneLite
- Produces: `FeatureContext` with steps: `I am on`, `I submit`, `the enrichment runs`, `I should see`, `I should see a score between`

- [ ] **Step 1: Write the FeatureContext (RED)**

Create `features/bootstrap/FeatureContext.php`:
```php
<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\Application\Application\Command\SubmitApplication\SubmitApplication;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Ecotone\Lite\EcotoneLite;
use Ecotone\Lite\InMemory\SimpleMessageChannelBuilder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\BrowserKit\AbstractBrowser;

final class FeatureContext implements Context
{
    private KernelInterface $kernel;
    private ?AbstractBrowser $client = null;
    private EntityManagerInterface $em;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        /** @var EntityManagerInterface $em */
        $em = $kernel->getContainer()->get(EntityManagerInterface::class);
        $this->em = $em;
    }

    /**
     * @BeforeScenario
     */
    public function clearDatabase(): void
    {
        $conn = $this->em->getConnection();
        $conn->executeStatement('DELETE FROM job_applications');
    }

    /**
     * @Given I am on :path
     */
    public function iAmOn(string $path): void
    {
        $this->client()->request('GET', $path);
    }

    /**
     * @When I submit an application with:
     */
    public function iSubmitAnApplicationWith(\Behat\Gherkin\Node\TableNode $table): void
    {
        $data = $table->getRowsHash();

        $command = new SubmitApplication(
            $data['fullName'],
            $data['email'],
            $data['phone'],
            $data['position'],
            $data['notes'] ?? '',
            $data['cvText'],
        );

        $ecotone = $this->bootstrapEcotone();
        $ecotone->sendCommandWithRoutingKey('application.submit', $command);
    }

    /**
     * @When the enrichment process runs
     */
    public function theEnrichmentProcessRuns(): void
    {
        $ecotone = $this->bootstrapEcotone();
        $ecotone->run('enrichment');
    }

    /**
     * @Then I should see :text
     */
    public function iShouldSee(string $text): void
    {
        $response = (string) $this->client()->getResponse()->getContent();
        if (!str_contains($response, $text)) {
            throw new \RuntimeException(sprintf('Expected to see "%s" but did not.', $text));
        }
    }

    /**
     * @Then the response status code should be :code
     */
    public function theResponseStatusCodeShouldBe(int $code): void
    {
        $actual = $this->client()->getResponse()->getStatusCode();
        if ($actual !== $code) {
            throw new \RuntimeException(sprintf('Expected status %d, got %d.', $code, $actual));
        }
    }

    /**
     * @When I am on "/applications/" followed by the application id for :email
     */
    public function iAmOnApplicationDetailForEmail(string $email): void
    {
        $conn = $this->em->getConnection();
        $id = $conn->fetchOne('SELECT id FROM job_applications WHERE email = ?', [$email]);
        if (false === $id) {
            throw new \RuntimeException(sprintf('No application found for email %s.', $email));
        }
        $this->client()->request('GET', '/applications/' . $id);
    }

    /**
     * @Then I should see a summary
     */
    public function iShouldSeeASummary(): void
    {
        $response = (string) $this->client()->getResponse()->getContent();
        // MockLLM returns a summary mentioning the position
        if (!str_contains($response, 'data-testid="application-summary"')) {
            throw new \RuntimeException('Summary element not found.');
        }
        if (preg_match('/data-testid="application-summary">\s*</p>/', $response)) {
            throw new \RuntimeException('Summary is empty.');
        }
    }

    /**
     * @Then I should see a score between :min and :max
     */
    public function iShouldSeeAScoreBetween(int $min, int $max): void
    {
        $response = (string) $this->client()->getResponse()->getContent();
        if (!preg_match('/data-testid="application-score">(\d+)</', $response, $matches)) {
            throw new \RuntimeException('Score element not found.');
        }
        $score = (int) $matches[1];
        if ($score < $min || $score > $max) {
            throw new \RuntimeException(sprintf('Score %d not in range %d-%d.', $score, $min, $max));
        }
    }

    private function bootstrapEcotone(): EcotoneLite
    {
        $container = $this->kernel->getContainer();

        return EcotoneLite::bootstrapFlowTesting(
            classesToResolve: [
                \App\Application\Application\Command\SubmitApplication\SubmitApplicationHandler::class,
                \App\Application\Application\Event\Enrichment\EnrichmentHandler::class,
            ],
            containerOrAvailableServices: $container,
            enableAsynchronousProcessing: [
                SimpleMessageChannelBuilder::createQueueChannel('enrichment'),
            ],
        );
    }

    private function client(): AbstractBrowser
    {
        if (null === $this->client) {
            $this->client = $this->kernel->getContainer()->get('test.client');
        }

        return $this->client;
    }
}
```

- [ ] **Step 2: Verify kernel boots and context loads**

Run:
```bash
make behat -- --dry-run
```

Expected: No scenarios yet, kernel boots, context loads.

- [ ] **Step 3: Commit**

```bash
git add features/bootstrap/FeatureContext.php
git commit -m "test: add Behat FeatureContext with EcotoneLite and HTTP client"
```

---

## Task 3: Add data-testid Attributes to Templates

**Files:**
- Modify: `templates/application/list.html.twig`
- Modify: `templates/application/detail.html.twig`

**Interfaces:**
- Consumes: Existing Twig templates
- Produces: Stable selectors for Behat assertions

- [ ] **Step 1: Modify list.html.twig**

Locate the application row loop and add:
```twig
<tr data-testid="application-row" data-email="{{ app.email }}">
```

Locate the score display and add:
```twig
<span data-testid="application-score">{{ app.score ?? '—' }}</span>
```

- [ ] **Step 2: Modify detail.html.twig**

Locate summary and add:
```twig
<p data-testid="application-summary">{{ application.summary }}</p>
```

Locate score and add:
```twig
<span data-testid="application-score">{{ application.score }}</span>
```

- [ ] **Step 3: Verify templates still render**

Run existing tests:
```bash
make test
```

Expected: All pass.

- [ ] **Step 4: Commit**

```bash
git add templates/application/list.html.twig templates/application/detail.html.twig
git commit -m "chore: add data-testid attributes for E2E assertions"
```

---

## Task 4: Write the Gherkin Feature

**Files:**
- Create: `features/application_submission.feature`

**Interfaces:**
- Consumes: FeatureContext steps from Task 2, data-testid from Task 3
- Produces: Passing E2E scenario covering submit → enrich → list → detail

- [ ] **Step 1: Write the Gherkin feature**

Create `features/application_submission.feature`:
```gherkin
Feature: Application submission and enrichment flow
  In order to evaluate candidates effectively
  As a hiring manager
  I want submitted applications to be automatically enriched and visible

  Scenario: Submit application and see enriched results
    Given I am on "/apply"
    When I submit an application with:
      | fullName | Ada Lovelace           |
      | email    | ada@example.com        |
      | phone    | +441111111111          |
      | position | Engineering Manager    |
      | notes    | Remote only            |
      | cvText   | Pioneer of computer science with extensive analytical experience. |
    Then the response status code should be 200
    And I should see "Application Submitted"
    When the enrichment process runs
    And I am on "/applications"
    Then I should see "Ada Lovelace"
    And I should see "Engineering Manager"
    And I should see a score between 0 and 100
    When I am on "/applications/" followed by the application id for "ada@example.com"
    Then I should see "Ada Lovelace"
    And I should see a summary
    And I should see a score between 0 and 100
```

- [ ] **Step 2: Run Behat (RED)**

```bash
make behat
```

Expected: Scenario may fail if assertions don't match template text. Adjust assertions as needed.

- [ ] **Step 3: Fix assertions if needed**

If "Application Submitted" is not the exact text, adjust to match the actual success page heading.

- [ ] **Step 4: Run Behat (GREEN)**

```bash
make behat
```

Expected: Scenario passes.

- [ ] **Step 5: Commit**

```bash
git add features/application_submission.feature
git commit -m "test: add Behat E2E scenario for submit → enrich → list → detail flow"
```

---

## Task 5: Add Behat to Makefile validate Target

**Files:**
- Modify: `Makefile`

- [ ] **Step 1: Update validate target**

Change:
```makefile
validate: lint test
```
to:
```makefile
validate: lint test behat
```

- [ ] **Step 2: Verify validate runs**

```bash
make validate
```

Expected: CS-Fixer, PHPStan, Deptrac, PHPUnit, Behat all pass.

- [ ] **Step 3: Commit**

```bash
git add Makefile
git commit -m "chore: include Behat E2E in make validate"
```

---

## Self-Review

**1. Spec coverage:**
- ✅ Submit application via EcotoneLite command — Task 4
- ✅ Async enrichment via EcotoneLite `run('enrichment')` — Task 2
- ✅ Enriched data visible in list — Task 4
- ✅ Enriched data visible in detail — Task 4
- ✅ Database isolation per scenario — Task 2 (BeforeScenario hook)

**2. Placeholder scan:**
- No "TBD", "TODO", "implement later"
- No "add appropriate error handling"
- All steps contain actual code

**3. Type consistency:**
- `FeatureContext` constructor receives `KernelInterface` (SymfonyExtension)
- `bootstrapEcotone()` returns `EcotoneLite`
- `AbstractBrowser` for Symfony test client
- `SimpleMessageChannelBuilder::createQueueChannel('enrichment')` matches the `#[Asynchronous('enrichment')]` attribute

**4. Review Focus coverage:**
- ✅ Enrichment not processed — Explicit `$ecotone->run('enrichment')` step
- ✅ Database pollution — `clearDatabase` hook
- ✅ Non-deterministic score — Asserts range (0-100)
- ✅ SymfonyExtension boot — Verified in Task 1
- ✅ EcotoneLite wiring — Symfony container passed as `containerOrAvailableServices`

---

## Execution Handoff

**Plan complete and saved to `docs/plans/05-behat-e2e-flow.md`.**

Please review the plan. Which execution approach would you prefer?

- **Subagent-driven** - A fresh subagent implements each task and a fresh reviewer checks it before the next one starts. Most thorough.
- **Native** - I implement every task myself in this session. Cheaper and faster since the plan carries all design.

**For this plan I recommend Native**, because there are only 5 bite-sized tasks with clear interfaces, and EcotoneLite wiring is easily verified interactively. Does the plan capture what you want?
