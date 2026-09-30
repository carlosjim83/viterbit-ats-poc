<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Persistence;

use App\Application\Domain\Model\JobApplication;
use App\Application\Domain\Model\ValueObject\ApplicationId;
use App\Application\Domain\Model\ValueObject\Email;
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
        $apps = array_values($this->applications);
        usort($apps, static fn (JobApplication $a, JobApplication $b): int => $b->appliedAt <=> $a->appliedAt);

        return $apps;
    }

    public function findByEmail(Email $email): ?JobApplication
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
