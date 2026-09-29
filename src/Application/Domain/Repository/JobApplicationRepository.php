<?php

declare(strict_types=1);

namespace App\Application\Domain\Repository;

use App\Application\Domain\Model\JobApplication;
use App\Application\Domain\Model\ValueObject\ApplicationId;

interface JobApplicationRepository
{
    public function save(JobApplication $application): void;

    public function findById(ApplicationId $id): ?JobApplication;

    /** @return list<JobApplication> */
    public function findAll(): array;

    public function findByEmail(\App\Application\Domain\Model\ValueObject\Email $email): ?JobApplication;

    /**
     * @param array{status?: string, position?: string, search?: string} $criteria
     *
     * @return list<JobApplication>
     */
    public function findByCriteria(array $criteria): array;
}
