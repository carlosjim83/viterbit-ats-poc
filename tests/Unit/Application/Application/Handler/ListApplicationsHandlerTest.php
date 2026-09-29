<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Application\Handler;

use App\Application\Application\DTO\ApplicationDTO;
use App\Application\Application\Handler\ListApplicationsHandler;
use App\Application\Application\Query\ListApplications;
use App\Application\Domain\Model\JobApplication;
use App\Application\Domain\Model\ValueObject\CVText;
use App\Application\Domain\Model\ValueObject\Email;
use App\Application\Domain\Model\ValueObject\FullName;
use App\Application\Domain\Model\ValueObject\Notes;
use App\Application\Domain\Model\ValueObject\Phone;
use App\Application\Domain\Model\ValueObject\Position;
use App\Application\Domain\Repository\JobApplicationRepository;
use PHPUnit\Framework\TestCase;

final class ListApplicationsHandlerTest extends TestCase
{
    public function testItReturnsEmptyArrayWhenNoApplications(): void
    {
        $repository = $this->createMock(JobApplicationRepository::class);
        $repository->method('findByCriteria')
            ->with(['status' => 'received'])
            ->willReturn([]);

        $handler = new ListApplicationsHandler($repository);
        $result = $handler->handle(new ListApplications(status: 'received'));

        self::assertSame([], $result);
    }

    public function testItReturnsApplicationDTOs(): void
    {
        $application = JobApplication::submit(
            new FullName('Ada Lovelace'),
            new Email('ada@example.com'),
            new Phone('+1234567890'),
            new Position('Engineer'),
            new Notes(''),
            new CVText('Experienced developer'),
        );

        $repository = $this->createMock(JobApplicationRepository::class);
        $repository->method('findByCriteria')
            ->with([])
            ->willReturn([$application]);

        $handler = new ListApplicationsHandler($repository);
        $result = $handler->handle(new ListApplications());

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
        $repository = $this->createMock(JobApplicationRepository::class);
        $repository->expects(self::once())
            ->method('findByCriteria')
            ->with([
                'status' => 'enriched',
                'position' => 'Manager',
                'search' => 'Ada',
            ])
            ->willReturn([]);

        $handler = new ListApplicationsHandler($repository);
        $result = $handler->handle(new ListApplications(
            status: 'enriched',
            position: 'Manager',
            search: 'Ada',
        ));

        self::assertSame([], $result);
    }

    public function testItIgnoresEmptyCriteria(): void
    {
        $repository = $this->createMock(JobApplicationRepository::class);
        $repository->expects(self::once())
            ->method('findByCriteria')
            ->with([])
            ->willReturn([]);

        $handler = new ListApplicationsHandler($repository);
        $result = $handler->handle(new ListApplications(
            status: '',
            position: '',
            search: '',
        ));

        self::assertSame([], $result);
    }
}
