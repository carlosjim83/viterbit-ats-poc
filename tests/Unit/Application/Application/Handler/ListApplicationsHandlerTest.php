<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Application\Handler;

use App\Application\Application\Query\ListApplications\ApplicationDTO;
use App\Application\Application\Query\ListApplications\ListApplications;
use App\Application\Application\Query\ListApplications\ListApplicationsHandler;
use App\Application\Domain\Repository\JobApplicationRepository;
use App\Tests\Helpers\Mother\JobApplicationMother;
use PHPUnit\Framework\TestCase;

final class ListApplicationsHandlerTest extends TestCase
{
    /** @var JobApplicationRepository&\PHPUnit\Framework\MockObject\MockObject */
    private JobApplicationRepository $repository;

    private ListApplicationsHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(JobApplicationRepository::class);
        $this->handler = new ListApplicationsHandler($this->repository);
    }

    public function testItReturnsEmptyArrayWhenNoApplications(): void
    {
        $this->repository->method('findByCriteria')
            ->with(['status' => 'received'])
            ->willReturn([]);

        $result = $this->handler->handle(new ListApplications(status: 'received'));

        self::assertSame([], $result);
    }

    public function testItReturnsApplicationDTOs(): void
    {
        $application = JobApplicationMother::builder()
            ->withName('Ada Lovelace')
            ->withEmail('ada@example.com')
            ->withPosition('Engineer')
            ->build();

        $this->repository->method('findByCriteria')
            ->with([])
            ->willReturn([$application]);

        $result = $this->handler->handle(new ListApplications());

        self::assertCount(1, $result);
        self::assertInstanceOf(ApplicationDTO::class, $result[0]);
        self::assertSame('Ada Lovelace', $result[0]->fullName);
        self::assertSame('ada@example.com', $result[0]->email);
        self::assertSame('Engineer', $result[0]->position);
        self::assertSame('received', $result[0]->status);
        self::assertNull($result[0]->score);
    }

    public function testItPassesAllCriteriaToRepository(): void
    {
        $this->repository->expects(self::once())
            ->method('findByCriteria')
            ->with([
                'status' => 'enriched',
                'position' => 'Manager',
                'search' => 'Ada',
            ])
            ->willReturn([]);

        $result = $this->handler->handle(new ListApplications(
            status: 'enriched',
            position: 'Manager',
            search: 'Ada',
        ));

        self::assertSame([], $result);
    }

    public function testItPropagatesScoreFromEnrichedApplication(): void
    {
        $application = JobApplicationMother::builder()
            ->withName('Ada Lovelace')
            ->withEmail('ada@example.com')
            ->withPosition('Engineer')
            ->build();
        $application->requestEnrichment();
        $application->completeEnrichment('Great candidate', 95);

        $this->repository->method('findByCriteria')
            ->with([])
            ->willReturn([$application]);

        $result = $this->handler->handle(new ListApplications());

        self::assertCount(1, $result);
        self::assertSame(95, $result[0]->score);
    }

    public function testItIgnoresEmptyCriteria(): void
    {
        $this->repository->expects(self::once())
            ->method('findByCriteria')
            ->with([])
            ->willReturn([]);

        $result = $this->handler->handle(new ListApplications(
            status: '',
            position: '',
            search: '',
        ));

        self::assertSame([], $result);
    }
}
