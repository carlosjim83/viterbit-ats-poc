<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Domain\Repository;

use App\Application\Domain\Model\JobApplication;
use App\Application\Domain\Model\ValueObject\ApplicationId;
use App\Application\Domain\Repository\JobApplicationRepository;
use App\Tests\Helpers\Mother\JobApplicationMother;
use PHPUnit\Framework\TestCase;

final class JobApplicationRepositoryTest extends TestCase
{
    private JobApplicationRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new InMemoryJobApplicationRepository();
    }

    public function testSaveAndFindById(): void
    {
        $application = $this->createApplication();
        $this->repository->save($application);

        $found = $this->repository->findById($application->id);

        self::assertNotNull($found);
        self::assertTrue($application->id->equals($found->id));
    }

    public function testFindByIdReturnsNullForUnknown(): void
    {
        $id = ApplicationId::fromString('550e8400-e29b-41d4-a716-446655440000');

        $found = $this->repository->findById($id);

        self::assertNull($found);
    }

    public function testFindAllReturnsSavedApplications(): void
    {
        $app1 = $this->createApplication();
        $app2 = $this->createApplication();
        $this->repository->save($app1);
        $this->repository->save($app2);

        $all = $this->repository->findAll();

        self::assertCount(2, $all);
    }

    public function testFindByCriteriaFiltersByStatus(): void
    {
        $app1 = $this->createApplication('Alice', 'alice@example.com', 'Engineer');
        $app2 = $this->createApplication('Bob', 'bob@example.com', 'Manager');
        $this->repository->save($app1);
        $this->repository->save($app2);

        $filtered = $this->repository->findByCriteria(['status' => 'received']);

        self::assertCount(2, $filtered);
    }

    public function testFindByCriteriaFiltersByPosition(): void
    {
        $app1 = $this->createApplication('Alice', 'alice@example.com', 'Engineer');
        $app2 = $this->createApplication('Bob', 'bob@example.com', 'Manager');
        $this->repository->save($app1);
        $this->repository->save($app2);

        $filtered = $this->repository->findByCriteria(['position' => 'Engineer']);

        self::assertCount(1, $filtered);
        self::assertSame('Alice', $filtered[0]->fullName->value);
    }

    public function testFindByCriteriaSearchesByName(): void
    {
        $app1 = $this->createApplication('Alice Smith', 'alice@example.com');
        $app2 = $this->createApplication('Bob Jones', 'bob@example.com');
        $this->repository->save($app1);
        $this->repository->save($app2);

        $filtered = $this->repository->findByCriteria(['search' => 'Alice']);

        self::assertCount(1, $filtered);
        self::assertSame('Alice Smith', $filtered[0]->fullName->value);
    }

    public function testFindByCriteriaSearchesByEmail(): void
    {
        $app1 = $this->createApplication('Alice', 'alice@example.com');
        $app2 = $this->createApplication('Bob', 'bob@example.com');
        $this->repository->save($app1);
        $this->repository->save($app2);

        $filtered = $this->repository->findByCriteria(['search' => 'bob@example.com']);

        self::assertCount(1, $filtered);
        self::assertSame('Bob', $filtered[0]->fullName->value);
    }

    public function testFindByCriteriaSearchIsCaseInsensitive(): void
    {
        $app1 = $this->createApplication('Alice', 'alice@example.com');
        $this->repository->save($app1);

        $filtered = $this->repository->findByCriteria(['search' => 'ALICE']);

        self::assertCount(1, $filtered);
    }

    public function testFindByCriteriaWithNoCriteriaReturnsAll(): void
    {
        $app1 = $this->createApplication();
        $app2 = $this->createApplication();
        $this->repository->save($app1);
        $this->repository->save($app2);

        $filtered = $this->repository->findByCriteria([]);

        self::assertCount(2, $filtered);
    }

    public function testFindByCriteriaWithEmptyStringsReturnsAll(): void
    {
        $app1 = $this->createApplication();
        $app2 = $this->createApplication();
        $this->repository->save($app1);
        $this->repository->save($app2);

        $filtered = $this->repository->findByCriteria(['status' => '', 'position' => '', 'search' => '']);

        self::assertCount(2, $filtered);
    }

    private function createApplication(string $name = 'Test User', string $email = 'test@example.com', string $position = 'Developer'): JobApplication
    {
        return JobApplicationMother::builder()
            ->withName($name)
            ->withEmail($email)
            ->withPosition($position)
            ->build();
    }
}

final class InMemoryJobApplicationRepository implements JobApplicationRepository
{
    /** @var array<string, JobApplication> */
    private array $applications = [];

    public function save(JobApplication $application): void
    {
        $this->applications[(string) $application->id] = $application;
    }

    public function findById(ApplicationId $id): ?JobApplication
    {
        return $this->applications[(string) $id] ?? null;
    }

    public function findAll(): array
    {
        $apps = array_values($this->applications);
        usort($apps, static fn (JobApplication $a, JobApplication $b): int => $b->appliedAt <=> $a->appliedAt);

        return $apps;
    }

    public function findByEmail(\App\Application\Domain\Model\ValueObject\Email $email): ?JobApplication
    {
        foreach ($this->applications as $application) {
            if ($application->email->value === $email->value) {
                return $application;
            }
        }

        return null;
    }

    public function findByCriteria(array $criteria): array
    {
        $apps = $this->findAll();

        if ([] === $criteria || ('' === ($criteria['status'] ?? '') && '' === ($criteria['position'] ?? '') && '' === ($criteria['search'] ?? ''))) {
            return $apps;
        }

        $filtered = [];
        foreach ($apps as $app) {
            if (isset($criteria['status']) && '' !== $criteria['status'] && $app->status->value !== $criteria['status']) {
                continue;
            }

            if (isset($criteria['position']) && '' !== $criteria['position'] && $app->position->value !== $criteria['position']) {
                continue;
            }

            if (isset($criteria['search']) && '' !== $criteria['search']) {
                $search = strtolower($criteria['search']);
                $match = str_contains(strtolower($app->fullName->value), $search)
                    || str_contains(strtolower($app->email->value), $search);
                if (!$match) {
                    continue;
                }
            }

            $filtered[] = $app;
        }

        return $filtered;
    }
}
