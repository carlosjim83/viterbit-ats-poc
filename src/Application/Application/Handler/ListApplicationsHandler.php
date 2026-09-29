<?php

declare(strict_types=1);

namespace App\Application\Application\Handler;

use App\Application\Application\DTO\ApplicationDTO;
use App\Application\Application\Query\ListApplications;
use App\Application\Domain\Repository\JobApplicationRepository;
use Ecotone\Modelling\Attribute\QueryHandler;

final readonly class ListApplicationsHandler
{
    public function __construct(
        private JobApplicationRepository $repository,
    ) {
    }

    /**
     * @return list<ApplicationDTO>
     */
    #[QueryHandler('application.list')]
    public function handle(ListApplications $query): array
    {
        $criteria = [];

        if (null !== $query->status && '' !== $query->status) {
            $criteria['status'] = $query->status;
        }

        if (null !== $query->position && '' !== $query->position) {
            $criteria['position'] = $query->position;
        }

        if (null !== $query->search && '' !== $query->search) {
            $criteria['search'] = $query->search;
        }

        $applications = $this->repository->findByCriteria($criteria);

        $dtos = [];
        foreach ($applications as $app) {
            $dtos[] = new ApplicationDTO(
                id: (string) $app->id,
                fullName: $app->fullName->value,
                email: $app->email->value,
                position: $app->position->value,
                status: $app->status->value,
                appliedAt: $app->appliedAt->format('c'),
                score: null,
            );
        }

        return $dtos;
    }
}
