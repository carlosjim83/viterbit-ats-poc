<?php

declare(strict_types=1);

namespace App\Application\Application\Handler;

use App\Application\Application\DTO\ApplicationDetailDTO;
use App\Application\Application\Query\GetApplicationDetail;
use App\Application\Domain\Model\ValueObject\ApplicationId;
use App\Application\Domain\Repository\JobApplicationRepository;
use Ecotone\Modelling\Attribute\QueryHandler;

final readonly class GetApplicationDetailHandler
{
    public function __construct(
        private JobApplicationRepository $repository,
    ) {
    }

    #[QueryHandler('application.detail')]
    public function handle(GetApplicationDetail $query): ?ApplicationDetailDTO
    {
        $application = $this->repository->findById(
            ApplicationId::fromString($query->applicationId)
        );

        if (null === $application) {
            return null;
        }

        return new ApplicationDetailDTO(
            id: (string) $application->id,
            fullName: $application->fullName->value,
            email: $application->email->value,
            phone: $application->phone->value,
            position: $application->position->value,
            notes: $application->notes->value,
            cvText: $application->cvText->value,
            status: $application->status->value,
            appliedAt: $application->appliedAt->format('Y-m-d H:i:s'),
            summary: null,
            score: null,
        );
    }
}
