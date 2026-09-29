<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Messaging;

use App\Application\Domain\Event\EnrichmentRequested;
use App\Application\Domain\LLMClientInterface;
use App\Application\Domain\Repository\JobApplicationRepository;
use Ecotone\Messaging\Attribute\Asynchronous;
use Ecotone\Modelling\Attribute\EventHandler;

final readonly class EnrichmentHandler
{
    public function __construct(
        private JobApplicationRepository $repository,
        private LLMClientInterface $llmClient,
    ) {
    }

    #[Asynchronous('enrichment')]
    #[EventHandler(endpointId: 'enrichment.handle')]
    public function handle(EnrichmentRequested $event): void
    {
        $application = $this->repository->findById($event->applicationId);

        if (null === $application) {
            return;
        }

        $application->requestEnrichment();
        $this->repository->save($application);

        $result = $this->llmClient->enrich($application->cvText->value, $application->position->value);

        $application->completeEnrichment($result['summary'], $result['score']);
        $this->repository->save($application);
    }
}
