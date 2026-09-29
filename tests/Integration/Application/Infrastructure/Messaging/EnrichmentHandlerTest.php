<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Infrastructure\Messaging;

use App\Application\Domain\Event\EnrichmentRequested;
use App\Application\Domain\LLMClientInterface;
use App\Application\Domain\Model\ValueObject\ApplicationId;
use App\Application\Domain\Repository\JobApplicationRepository;
use App\Application\Infrastructure\Messaging\EnrichmentHandler;
use App\Tests\Helpers\Mother\JobApplicationMother;
use PHPUnit\Framework\TestCase;

final class EnrichmentHandlerTest extends TestCase
{
    public function testItEnrichesApplication(): void
    {
        $repository = $this->createMock(JobApplicationRepository::class);
        $llmClient = $this->createMock(LLMClientInterface::class);
        $handler = new EnrichmentHandler($repository, $llmClient);

        $application = JobApplicationMother::create();

        $repository->method('findById')
            ->with($this->equalTo($application->id))
            ->willReturn($application);

        $repository->expects($this->exactly(2))
            ->method('save')
            ->with($this->equalTo($application));

        $llmClient->method('enrich')
            ->with($application->cvText->value, $application->position->value)
            ->willReturn([
                'summary' => 'Mock summary for testing.',
                'score' => 42,
            ]);

        $handler->handle(new EnrichmentRequested($application->id));

        self::assertSame('enriched', $application->status->value);
        self::assertSame('Mock summary for testing.', $application->summary);
        self::assertSame(42, $application->score);
    }

    public function testItDoesNothingWhenApplicationNotFound(): void
    {
        $repository = $this->createMock(JobApplicationRepository::class);
        $llmClient = $this->createMock(LLMClientInterface::class);
        $handler = new EnrichmentHandler($repository, $llmClient);

        $repository->method('findById')
            ->willReturn(null);

        $repository->expects($this->never())
            ->method('save');

        $llmClient->expects($this->never())
            ->method('enrich');

        $handler->handle(new EnrichmentRequested(ApplicationId::generate()));
    }
}
