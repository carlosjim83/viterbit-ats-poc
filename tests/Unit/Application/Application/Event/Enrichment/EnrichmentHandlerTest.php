<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Application\Event\Enrichment;

use App\Application\Application\Event\Enrichment\EnrichmentHandler;
use App\Application\Domain\Event\EnrichmentRequested;
use App\Application\Domain\LLMClientInterface;
use App\Application\Domain\Model\ValueObject\ApplicationId;
use App\Application\Domain\Repository\JobApplicationRepository;
use App\Tests\Helpers\Mother\JobApplicationMother;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class EnrichmentHandlerTest extends TestCase
{
    /** @var JobApplicationRepository&MockObject */
    private JobApplicationRepository $repository;

    /** @var LLMClientInterface&MockObject */
    private LLMClientInterface $llmClient;

    private EnrichmentHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(JobApplicationRepository::class);
        $this->llmClient = $this->createMock(LLMClientInterface::class);
        $this->handler = new EnrichmentHandler($this->repository, $this->llmClient);
    }

    public function testItEnrichesApplication(): void
    {
        $application = JobApplicationMother::create();

        $this->repository->method('findById')
            ->with($this->equalTo($application->id))
            ->willReturn($application);

        $this->repository->expects($this->exactly(2))
            ->method('save')
            ->with($this->equalTo($application));

        $this->llmClient->method('enrich')
            ->with($application->cvText->value, $application->position->value)
            ->willReturn([
                'summary' => 'Mock summary for testing.',
                'score' => 42,
            ]);

        $this->handler->handle(new EnrichmentRequested($application->id));

        self::assertSame('enriched', $application->status->value);
        self::assertSame('Mock summary for testing.', $application->summary);
        self::assertSame(42, $application->score);
    }

    public function testItDoesNothingWhenApplicationNotFound(): void
    {
        $this->repository->method('findById')
            ->willReturn(null);

        $this->repository->expects($this->never())
            ->method('save');

        $this->llmClient->expects($this->never())
            ->method('enrich');

        $this->handler->handle(new EnrichmentRequested(ApplicationId::generate()));
    }
}
