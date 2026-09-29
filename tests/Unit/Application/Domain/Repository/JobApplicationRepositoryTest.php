<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Domain\Repository;

use App\Application\Domain\Model\JobApplication;
use App\Application\Domain\Model\ValueObject\ApplicationId;
use App\Application\Domain\Model\ValueObject\CVText;
use App\Application\Domain\Model\ValueObject\Email;
use App\Application\Domain\Model\ValueObject\FullName;
use App\Application\Domain\Model\ValueObject\Position;
use App\Application\Domain\Repository\JobApplicationRepository;
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

    private function createApplication(): JobApplication
    {
        return JobApplication::submit(
            new FullName('Test User'),
            new Email('test@example.com'),
            new Position('Developer'),
            new CVText('Some experience'),
        );
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
        return array_values($this->applications);
    }
}
