<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Persistence;

use App\Application\Domain\Model\JobApplication;
use App\Application\Domain\Model\ValueObject\ApplicationId;
use App\Application\Domain\Repository\JobApplicationRepository;

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

    public function findByCriteria(array $criteria): array
    {
        return $this->findAll();
    }
}
