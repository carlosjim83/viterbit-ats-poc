<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Application\Handler;

use App\Application\Application\Command\SubmitApplication\SubmitApplication;
use App\Application\Application\Command\SubmitApplication\SubmitApplicationHandler;
use App\Application\Domain\Event\ApplicationSubmitted;
use App\Application\Domain\Event\EnrichmentRequested;
use App\Application\Domain\Model\JobApplication;
use App\Application\Domain\Model\ValueObject\ApplicationId;
use App\Application\Domain\Model\ValueObject\CVText;
use App\Application\Domain\Model\ValueObject\Email;
use App\Application\Domain\Model\ValueObject\FullName;
use App\Application\Domain\Model\ValueObject\Notes;
use App\Application\Domain\Model\ValueObject\Phone;
use App\Application\Domain\Model\ValueObject\Position;
use App\Application\Domain\Repository\JobApplicationRepository;
use App\Shared\Domain\EventBus;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SubmitApplicationHandlerTest extends TestCase
{
    /** @var JobApplicationRepository&MockObject */
    private JobApplicationRepository $repository;

    /** @var EventBus&MockObject */
    private EventBus $eventBus;

    private SubmitApplicationHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(JobApplicationRepository::class);
        $this->eventBus = $this->createMock(EventBus::class);
        $this->handler = new SubmitApplicationHandler($this->repository, $this->eventBus);
    }

    public function testHandleCreatesAndSavesApplication(): void
    {
        $this->repository->method('findByEmail')->willReturn(null);

        $this->repository->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (JobApplication $app): bool {
                return 'Test User' === $app->fullName->value
                    && 'test@example.com' === $app->email->value
                    && '+1234567890' === $app->phone->value
                    && 'Developer' === $app->position->value
                    && 'Some notes' === $app->notes->value
                    && 'Some CV text' === $app->cvText->value
                    && 'received' === $app->status->value;
            }));

        $publishedEvents = [];
        $this->eventBus->expects(self::exactly(2))
            ->method('publish')
            ->willReturnCallback(static function ($event) use (&$publishedEvents): void {
                $publishedEvents[] = $event;
            });

        $command = new SubmitApplication(
            'Test User',
            'test@example.com',
            '+1234567890',
            'Developer',
            'Some notes',
            'Some CV text',
        );

        $id = $this->handler->handle($command);

        self::assertInstanceOf(ApplicationId::class, $id);
        self::assertCount(2, $publishedEvents);
        self::assertInstanceOf(ApplicationSubmitted::class, $publishedEvents[0]);
        self::assertInstanceOf(EnrichmentRequested::class, $publishedEvents[1]);
        self::assertTrue($id->equals($publishedEvents[1]->applicationId));
    }

    public function testHandleRejectsDuplicateEmail(): void
    {
        $existing = JobApplication::submit(
            new FullName('Existing'),
            new Email('duplicate@example.com'),
            new Phone('+1111111111'),
            new Position('Dev'),
            new Notes(''),
            new CVText('Existing CV'),
        );

        $this->repository->method('findByEmail')
            ->with(self::callback(static fn (Email $email): bool => 'duplicate@example.com' === $email->value))
            ->willReturn($existing);

        $this->eventBus->expects(self::never())->method('publish');

        $command = new SubmitApplication(
            'Test User',
            'duplicate@example.com',
            '+1234567890',
            'Developer',
            'Some notes',
            'Some CV text',
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('An application with email duplicate@example.com already exists.');

        $this->handler->handle($command);
    }
}
