# Behat E2E Application Submission & Enrichment Flow Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add Behat E2E tests that verify a user can submit a job application, the async enrichment runs, and the enriched result (score + summary) is visible in both the list and detail views.

**Architecture:** Behat runs inside the PHP Docker container via SymfonyExtension, booting the real Symfony kernel. The test submits a real HTTP POST to `/apply`, then explicitly consumes the async `enrichment` message queue via `ecotone:run`, then asserts against the rendered HTML of `/applications` and `/applications/{id}`. Database is reset per scenario via a custom Behat hook that truncates `job_applications`.

**Tech Stack:** Behat 3.x + friends-of-behat/symfony-extension, Symfony 7.4, Ecotone, Doctrine PostgreSQL, Docker

**Spec:** This PoC requires end-to-end coverage of the submission → enrichment → readback flow. The existing PHPUnit integration tests verify handlers in isolation but do not exercise the full HTTP + async pipeline together.

## Global Constraints

- All commands run inside Docker: `docker compose exec php ...`
- Behat runs against the real Symfony kernel (`APP_ENV=test`)
- Database must be isolated per scenario (truncate `job_applications` before each)
- Async enrichment must be consumed explicitly (no sleep/wait magic); use `ecotone:run enrichment --limit=1`
- No changes to production source code unless required for testability (e.g., adding `data-testid` attributes to templates)
- Follow existing project conventions: `declare(strict_types=1)`, atomic commits, TDD

## Review Focus

1. **Enrichment not processed before assertion** — If the Behat step forgets to consume the message, the application remains `enriching` and the assertions fail. The test must explicitly trigger consumption.
2. **Database pollution across scenarios** — If truncation fails or is skipped, subsequent scenarios see stale data. The hook must run reliably.
3. **Non-deterministic score/summary from MockLLMClient** — The mock LLM returns deterministic scores based on input hash, but if the input changes, the expected score changes. The test should assert on presence/range, not exact score value, unless it pins the exact input.
4. **SymfonyExtension boot failure** — If the extension cannot boot the kernel (missing `.env.behat`, wrong `KERNEL_CLASS`), Behat fails before any scenario runs. Verify extension config carefully.
5. **Messenger transport not configured for test env** — The `enrichment` transport uses Doctrine. In `test` env this must point to `viterbit_test` database (already configured in `.env.test`). No extra config needed.

---

## File Structure

```
# New files
behat.yml                              -- Behat + SymfonyExtension configuration
.behat.env                             -- Environment file for Behat (APP_ENV=test)
features/
├── bootstrap/
│   └── FeatureContext.php             -- Main Behat context with Symfony kernel + HTTP client
├── application_submission.feature     -- Gherkin feature: submit, enrich, view

# Modified files
templates/application/list.html.twig   -- Add data-testid for assertions
templates/application/detail.html.twig -- Add data-testid for assertions
Makefile                               -- Add `behat` target
```

---

## Task 1: Install Behat and Symfony Extension

**Files:**
- Modify: `composer.json` (add require-dev)
- Create: `behat.yml`
- Create: `.behat.env`
- Modify: `Makefile`

**Interfaces:**
- Consumes: Existing Symfony kernel (`App\Kernel`), existing `.env.test`
- Produces: Working `behat` command runnable via `make behat`

- [ ] **Step 1: Install packages**

Run:
```bash
docker compose exec php composer require --dev behat/behat friends-of-behat/symfony-extension
```

Expected: Packages install, `vendor/bin/behat` exists.

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

- [ ] **Step 4: Create `.behat.env`**

Create `.behat.env`:
```
APP_ENV=test
APP_DEBUG=1
DATABASE_URL="postgresql://viterbit:viterbit@db:5432/viterbit_test?serverVersion=16&charset=utf8"
```

- [ ] **Step 5: Add `behat` to Makefile**

Append to `Makefile`:
```makefile
behat:
	docker compose exec -e APP_ENV=test php vendor/bin/behat --format=progress
```

- [ ] **Step 6: Verify Behat boots**

Run:
```bash
make behat -- --dry-run
```

Expected: No scenarios yet, but kernel boots without errors.

- [ ] **Step 7: Commit**

```bash
git add composer.json composer.lock behat.yml .behat.env features/bootstrap/bootstrap.php Makefile
git commit -m "chore: install Behat with SymfonyExtension for E2E tests"
```

---

## Task 2: Create FeatureContext with HTTP Client and Database Reset

**Files:**
- Create: `features/bootstrap/FeatureContext.php`
- Modify: `features/bootstrap/bootstrap.php` (add `bootstrap.php` for SymfonyExtension)

**Interfaces:**
- Consumes: Symfony kernel, Doctrine EntityManager, `job_applications` table name
- Produces: `FeatureContext` with steps: `I am on`, `I submit`, `the enrichment runs`, `I should see`

- [ ] **Step 1: Write the FeatureContext skeleton (RED)**

Create `features/bootstrap/FeatureContext.php`:
```php
<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\BrowserKit\AbstractBrowser;
use FriendsOfBehat\SymfonyExtension\Driver\SymfonyDriver;

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
        $this->client()->request('POST', '/apply', $data);
    }

    /**
     * @When the enrichment process runs
     */
    public function theEnrichmentProcessRuns(): void
    {
        // Consume exactly one message from the enrichment channel
        $application = new \Symfony\Bundle\FrameworkBundle\Console\Application($this->kernel);
        $application->setAutoExit(false);
        $application->run(new \Symfony\Component\Console\Input\ArrayInput([
            'command' => 'ecotone:run',
            'channel' => 'enrichment',
            '--limit' => 1,
            '--no-interaction' => true,
        ]), new \Symfony\Component\Console\Output\NullOutput());
    }

    /**
     * @Then I should see :text
     */
    public function iShouldSee(string $text): void
    {
        $response = $this->client()->getResponse()->getContent();
        if (!str_contains((string) $response, $text)) {
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
git commit -m "test: add Behat FeatureContext with HTTP client and database reset hook"
```

---

## Task 3: Add data-testid Attributes to Templates for Reliable Assertions

**Files:**
- Modify: `templates/application/list.html.twig`
- Modify: `templates/application/detail.html.twig`

**Interfaces:**
- Consumes: Existing Twig templates
- Produces: Stable selectors for Behat assertions

- [ ] **Step 1: Modify list.html.twig to add data-testid**

Locate the application row loop and add:
```twig
<tr data-testid="application-row" data-email="{{ app.email }}">
```

Locate the score display and add:
```twig
<span data-testid="application-score">{{ app.score ?? '—' }}</span>
```

- [ ] **Step 2: Modify detail.html.twig to add data-testid**

Locate summary and add:
```twig
<p data-testid="application-summary">{{ application.summary }}</p>
```

Locate score and add:
```twig
<span data-testid="application-score">{{ application.score }}</span>
```

- [ ] **Step 3: Verify templates still render**

Run existing integration tests:
```bash
make test-integration
```

Expected: All pass.

- [ ] **Step 4: Commit**

```bash
git add templates/application/list.html.twig templates/application/detail.html.twig
git commit -m "chore: add data-testid attributes for E2E test assertions"
```

---

## Task 4: Write the Gherkin Feature and Implement Steps

**Files:**
- Create: `features/application_submission.feature`
- Modify: `features/bootstrap/FeatureContext.php` (add assertion steps)

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
    And I should see "enriched"
    When I am on "/applications/" followed by the application id for "ada@example.com"
    Then I should see "Ada Lovelace"
    And I should see a summary
    And I should see a score between 0 and 100
```

- [ ] **Step 2: Add missing steps to FeatureContext**

Add these methods to `FeatureContext`:
```php
    /**
     * @When I am on :path followed by the application id for :email
     */
    public function iAmOnPathFollowedByApplicationId(string $path, string $email): void
    {
        $conn = $this->em->getConnection();
        $id = $conn->fetchOne('SELECT id FROM job_applications WHERE email = ?', [$email]);
        if (false === $id) {
            throw new \RuntimeException(sprintf('No application found for email %s.', $email));
        }
        $this->client()->request('GET', $path . $id);
    }

    /**
     * @Then I should see a summary
     */
    public function iShouldSeeASummary(): void
    {
        $response = (string) $this->client()->getResponse()->getContent();
        // The mock LLM returns a summary mentioning the position
        if (!str_contains($response, 'data-testid="application-summary"')) {
            throw new \RuntimeException('Summary element not found.');
        }
        // Assert summary is non-empty (not just the placeholder)
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
```

- [ ] **Step 3: Run Behat (RED)**

```bash
make behat
```

Expected: Scenario fails because `I should see "enriched"` asserts against the list view which may not show the raw status string.

- [ ] **Step 4: Adjust assertions to match actual template output**

If the list view uses a status badge instead of raw text, change:
```gherkin
    And I should see "enriched"
```
to:
```gherkin
    And I should see a score between 0 and 100
```

And adjust the scenario order: assert score presence on list, then navigate to detail.

- [ ] **Step 5: Run Behat (GREEN)**

```bash
make behat
```

Expected: Scenario passes.

- [ ] **Step 6: Commit**

```bash
git add features/application_submission.feature features/bootstrap/FeatureContext.php
git commit -m "test: add Behat E2E scenario for submit, enrich, list, detail flow"
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
- ✅ Submit application via HTTP POST — Task 4
- ✅ Async enrichment runs — Task 2 (ecotone:run step)
- ✅ Enriched data visible in list — Task 4
- ✅ Enriched data visible in detail — Task 4
- ✅ Database isolation per scenario — Task 2 (BeforeScenario hook)

**2. Placeholder scan:**
- No "TBD", "TODO", "implement later"
- No "add appropriate error handling"
- No "similar to Task N"
- All steps contain actual code

**3. Type consistency:**
- `FeatureContext` constructor receives `KernelInterface` (SymfonyExtension provides this)
- `AbstractBrowser` is the correct return type for Symfony test client
- `EntityManagerInterface` retrieved via container

**4. Review Focus coverage:**
- ✅ Enrichment not processed before assertion — Explicit `the enrichment process runs` step
- ✅ Database pollution — `clearDatabase` hook
- ✅ Non-deterministic score — Asserts range (0-100), not exact value
- ✅ SymfonyExtension boot — Verified in Task 1 Step 6
- ✅ Messenger transport config — Uses existing `.env.test` DATABASE_URL

---

## Execution Handoff

**Plan complete and saved to `docs/superpowers/plans/2025-01-10-behat-e2e-enrichment-flow.md`.**

Please review the plan. Which execution approach would you prefer?

- **Subagent-driven** - A fresh subagent implements each task and a fresh reviewer checks it before the next one starts. Most thorough; recommended since this touches composer deps, config, templates, and Behat context wiring.
- **Native** - I implement every task myself in this session. Cheaper and faster since the plan carries all design.

**For this plan I recommend Native**, because there are only 5 bite-sized tasks, no complex interface dependencies between them, and the main risk (SymfonyExtension wiring) is easily verified interactively. Does the plan capture what you want?
